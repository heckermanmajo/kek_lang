<?php

function get_precedence_level(Token $t): int {
  return match ($t->value) {
    "+" => 6,
    "-" => 6,
    "*" => 7,
    "/" => 7,
    "//" => 7,
    "**" => 7,
    "%" => 7,
    "==" => 8,
    "!=" => 8,
    "<" => 8,
    ">" => 8,
    "<=" => 8,
    ">=" => 8,
    " and " => 9,
    " or " => 10,
    " not " => 11,
    "|" => 12,
    "." => 13,
    "?" => 13,
    default => throw new SyntaxError("Unknown operator: $t")
  };
}

const MAX_PRECEDENCE_LEVEL = 13;


/**
 * @param array<Token> $tokens
 * @throws SyntaxError
 */
function parse_expression_term(array $tokens, int &$index): ExpressionTermNode|ExpressionNode {

  // if we get an ( we start another expression term
  // if we get an prefix operator we start another expression term
  // then we parse an expression and then we expect an operator
  // or the term is done

  # each operator has aprecedence level
  # once we have all collected
  # we loop over the list of expressions, operators and terms
  # and start wirth the highest precedence level
  # and binding the elements right and left of the operator
  # into a new expression term until we have only one element left
  # and then we return that element

  # for binary_unary operators we need to check after we have collected all
  # expressions, if there is a operator on the left or if it is at the
  # start position - if so we remark it as unary
  # otherwise we mark it as binary

  # we assume the gender of the operator by the position LOL

  $unsorted_terms = [];

  while (true) {

    # we need to check if we are at the end of the tokens
    if ($index >= count($tokens)) {
      break;
    }

    $token = $tokens[$index];

    // break on an close paren, since if you reach one here
    // you are a subexpression and should not continue
    $token_is_close_paren = $token->type === TokenType::CLOSE_PAREN;
    if ($token_is_close_paren) {
      break;
    }


    (function(array $tokens, int &$index) use (&$unsorted_terms) {
      # parse expression that is not operator
      $ast_expression_node = new ExpressionTermNode(tokens: [], children: []);

      if ($tokens[$index]->type === TokenType::IDENTIFIER) {


        # check if the identifier is a type, parse the type
        $identifier_is_type = ctype_upper($tokens[$index]->value[0]) || $tokens[$index]->value === "_";
        if($identifier_is_type){

          $type_expression = parse_type_expression($tokens, $index);
          # TODO: EAT THE . in case of a literal
          # todo: expect a . before the open parenthesis
          $next_token = $tokens[$index];
          $next_next_token = $tokens[$index + 1] ?? null;
          #echo "\nFLAGG\n";
          #echo $next_token;
          #echo $next_next_token;

          # construction literal -> type-expression (call-argument-list)
          # .(  -> signal for construction literal
          # .name() -> is just a function call to a static method
          #            (usage of a strict as a namespace)
          if (
            $next_token->type === TokenType::BINARY_OPERATOR
            && $next_token->value === "."
            && $next_next_token->type === TokenType::OPEN_PAREN
          ) {
            $index++; # jump over the dot
            $node = new ConstructionLiteralNode(tokens: [], children: [$type_expression]);
            $node->children[] = parse_call_argument_list($tokens, $index);
            $expression_node = new ExpressionNode(tokens: [], children: [$node]);
            $unsorted_terms[] = $expression_node;
            echo "\nFLAGG\n";
            return;
          }

          $ast_expression_node->children[] = $type_expression;
          $unsorted_terms[] = $ast_expression_node;

          return;

        }

        # if the token after the identifier is an open parenthesis,
        # then this is a function call
        if (count($tokens) > $index + 1
          && $tokens[$index + 1]->type === TokenType::OPEN_PAREN) {

          # consume the identifier
          $node = parse_identifier($tokens, $index);
          $ast_expression_node->children[] = $node;

          # consume the open parenthesis -> function call argument list
          $node = parse_call_argument_list($tokens, $index);
          $ast_expression_node->children[] = $node;
          $unsorted_terms[] = $ast_expression_node;
          return;
        }

        # else it is a simple identifier
        $node = parse_identifier($tokens, $index);
        $ast_expression_node->children[] = $node;
        $unsorted_terms[] = $ast_expression_node;
        return;

      }

      # in case w
      $token = $tokens[$index];
      if(
        $token->type === TokenType::UNARY_OPERATOR
        || $token->type === TokenType::BINARY_OPERATOR
        || $token->type === TokenType::UNARY_BINARY_OPERATOR
        || $token->type === TokenType::OPEN_PAREN
      ) {
        return;
      }

      # check for literals, etc.
      $node = parse_value_literal($tokens, $index);
      $ast_expression_node->children[] = $node;

      $unsorted_terms[] = $ast_expression_node;
    })($tokens, $index);

    # operators
    if (
      $token->type === TokenType::UNARY_OPERATOR
      || $token->type === TokenType::BINARY_OPERATOR
      || $token->type === TokenType::UNARY_BINARY_OPERATOR
    ) {
      $unsorted_terms[] = $token;
      $index++;
      continue;
    }

    # if we encounter an ( we start a new term
    $token_is_open_paren = $token->type === TokenType::OPEN_PAREN;
    if ($token_is_open_paren) {
      $index++;
      $node = parse_expression_term($tokens, $index);
      $token = $tokens[$index];
      $token_is_close_paren = $token->type === TokenType::CLOSE_PAREN;
      if (!$token_is_close_paren) {
        throw new SyntaxError("Expected closing parenthesis, got: $token");
      }
      $index++;
      $unsorted_terms[] = $node;
      continue;
    }

    # todo: handle expression block

    # now we parse an expression
    #$node = parse_expression_term($tokens, $index);
    #$unsorted_terms[] = $node;

    # we need to check if we are at the end of the tokens
    if ($index >= count($tokens)) {
      break;
    }

    $next_is_operator = $tokens[$index]->type === TokenType::OPERATOR
      || $tokens[$index]->type === TokenType::BINARY_OPERATOR
      || $tokens[$index]->type === TokenType::UNARY_BINARY_OPERATOR
      || $tokens[$index]->type === TokenType::UNARY_OPERATOR;

    if (!$next_is_operator) {
      break;
    }

  }

  # Before we sort the unsorted terms we need to apply the unary operators
  # to the term that follows

  $unsorted_terms_with_applied_unary_operators = [];
  for ($i = 0; $i < count($unsorted_terms); $i++) {

    $unsorted_term = $unsorted_terms[$i];

    if ($unsorted_term instanceof Token){

      if($unsorted_term->type === TokenType::UNARY_OPERATOR){
        $new_term = new ExpressionTermNode(tokens: [$unsorted_term], children: [$unsorted_terms[$i + 1]]);
        $unsorted_terms_with_applied_unary_operators[] = $new_term;
        $i++;
        continue;
      }
      elseif ($unsorted_term->type === TokenType::UNARY_BINARY_OPERATOR){
        $prev_one_is_operator = $i > 0 && $unsorted_terms[$i - 1] instanceof Token;
        if($prev_one_is_operator || $i === 0){
          $new_term = new ExpressionTermNode(tokens: [$unsorted_term], children: [$unsorted_terms[$i + 1]]);
          $unsorted_terms_with_applied_unary_operators[] = $new_term;
          $i++;
          continue;
        }
        else{
          $unsorted_term->type = TokenType::BINARY_OPERATOR;
        }
      }

    }

    $unsorted_terms_with_applied_unary_operators[] = $unsorted_term;

  }

  $unsorted_terms = $unsorted_terms_with_applied_unary_operators;

  # term reduction here, based on precedence level

  $highest_precedence_level = MAX_PRECEDENCE_LEVEL;
  for ($i = $highest_precedence_level; $i >= 1; $i--) {

    $replaced_version_terms = [];
    $preceding_term = null;
    assert(!$unsorted_terms[0] instanceof Token);

    for ($x = 0; $x < count($unsorted_terms); $x++) {
      $term = $unsorted_terms[$x];

      if ($term instanceof Token) {

        assert($term->type === TokenType::BINARY_OPERATOR);

        $precedence_level = get_precedence_level($term);
        if ($precedence_level === $i) {
          $next_term = $unsorted_terms[$x + 1];
          if ($next_term instanceof Token) {
            throw new SyntaxError("Expected expression after binary operator, got: $next_term");
          }
          if ($preceding_term === null) {
            throw new SyntaxError("Expected expression before binary operator, got: $preceding_term");
          }
          if ($preceding_term instanceof Token) {
            throw new SyntaxError("Expected expression before binary operator, got: $preceding_term");
          }
          $node = new ExpressionTermNode(tokens: [$term], children: [$preceding_term, $next_term]);
          $preceding_term = $node;
          $x++;  // so we skip the next term which is now part of the new node
        } else {
          if ($preceding_term !== null) $replaced_version_terms[] = $preceding_term;
          $preceding_term = $term;
        }

      } else {
        if ($preceding_term !== null) $replaced_version_terms[] = $preceding_term;
        $preceding_term = $term;
      }

    }
    if ($preceding_term !== null) $replaced_version_terms[] = $preceding_term;
    $unsorted_terms = $replaced_version_terms;

  }

  if (count($unsorted_terms) > 1) {
    throw new SyntaxError("Expected only one term left, got: ".count($unsorted_terms). " - " . print_r($unsorted_terms, true));
  }

  $node = $unsorted_terms[0];

  return $node;

}


/**
 * This function parses a unary term.
 *
 * - <term>
 * not <term>
 *
 *
 * @param array<Token> $tokens
 * @param int $index
 * @return ExpressionTermNode
 * @throws SyntaxError
 */
function parse_unary_term(array $tokens, int &$index): ExpressionTermNode {
  $ast_node = new ExpressionTermNode(tokens: [], children: []);

  $token = $tokens[$index];

  if ($token->type !== TokenType::UNARY_OPERATOR) {
    throw new SyntaxError("Expected unary operator, got: $token");
  }

  $ast_node->tokens[] = $token;
  $index++; // jump over unary operator

  $node = parse_expression_term($tokens, $index, true);
  $ast_node->children[] = $node;

  return $ast_node;
}
