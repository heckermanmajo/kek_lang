<?php
include_once __DIR__ . "/../data.php";
include_once __DIR__ . "/../tokenizer.php";
include_once __DIR__ . "/../parser_helper.php";
include_once __DIR__ . "/parse_identifier.php";
include_once __DIR__ . "/parse_call_argument_list.php";
include_once __DIR__ . "/parse_value_literal.php";

function get_precedence_level(Token $t): int {
  return match ($t->value) {
    "+" => 1,
    "-" => 1,
    "*" => 2,
    "/" => 2,
    "//" => 2,
    "**" => 2,
    "%" => 2,
    "==" => 3,
    "!=" => 3,
    "<" => 3,
    ">" => 3,
    "<=" => 3,
    ">=" => 3,
    " and " => 4,
    " or " => 5,
    " not " => 6,
    "." => 7,
    default => throw new SyntaxError("Unknown operator: $t")
  };
}


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

    # this handles minus as a prefix operator
    $token_is_unary_OR_binary_operator = $token->type === TokenType::UNARY_BINARY_OPERATOR;
    if ($token_is_unary_OR_binary_operator) {
      $last_term = $unsorted_terms[count($unsorted_terms) - 1];
      if ($last_term instanceof Token || count($unsorted_terms) === 0) {
        $token->type = TokenType::UNARY_OPERATOR;
        $unsorted_terms[] = parse_unary_term($tokens, $index);
      } else {
        # just add - as a normal binary operator
        $token->type = TokenType::BINARY_OPERATOR;
        $unsorted_terms[] = $token;
        $index++;
      }
      continue;
    }

    # unary operators
    if ($token->type === TokenType::UNARY_OPERATOR) {
      $unsorted_terms[] = parse_unary_term($tokens, $index);
      continue;
    }

    # binary operators
    if ($token->type === TokenType::BINARY_OPERATOR) {
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

      # check for literals, etc.
      $node = parse_value_literal($tokens, $index);
      $ast_expression_node->children[] = $node;

      $unsorted_terms[] = $ast_expression_node;
    })($tokens, $index);

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


  # term reduction here, based on precedence level

  $highest_precedence_level = 7;
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
    throw new SyntaxError("Expected only one term left, got: " . print_r($unsorted_terms, true));
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


if (count(debug_backtrace()) == 0) {

  $expression_term = "a  + v";
  echo "\n---------------------------------\n";
  echo $expression_term . "\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression_term, verbose: false);#
  $index = 0;
  $node = parse_expression_term($tokens, $index);
  $node->print_as_tree();


  $expression_term = "a(b()) + v";
  echo "\n---------------------------------\n";
  echo $expression_term . "\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression_term, verbose: false);#
  $index = 0;
  $node = parse_expression_term($tokens, $index);
  $node->print_as_tree();


  $expression_term = "a + v * c";
  echo "\n---------------------------------\n";
  echo $expression_term . "\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression_term, verbose: false);#
  $index = 0;
  $node = parse_expression_term($tokens, $index);
  $node->print_as_tree();

  $expression_term = "(a + v) * c";
  echo "\n---------------------------------\n";
  echo $expression_term . "\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression_term, verbose: false);#
  $index = 0;
  $node = parse_expression_term($tokens, $index);
  $node->print_as_tree();

  $expression_term = "(a + (v ** q)) * c";
  echo "\n---------------------------------\n";
  echo $expression_term . "\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression_term, verbose: false);#
  $index = 0;
  $node = parse_expression_term($tokens, $index);
  $node->print_as_tree();


  $expression_term = "(a + (v ** q)) * (c + d) + a";
  echo "\n---------------------------------\n";
  echo $expression_term . "\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression_term, verbose: false);#
  $index = 0;
  $node = parse_expression_term($tokens, $index);
  $node->print_as_tree();

  $expression_term = "(a + (v ** q)) * (c + d)";
  echo "\n---------------------------------\n";
  echo $expression_term . "\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression_term, verbose: false);#
  $index = 0;
  $node = parse_expression_term($tokens, $index);
  $node->print_as_tree();

  $expression_term = "A and B or C";
  echo "\n---------------------------------\n";
  echo $expression_term . "\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression_term, verbose: false);#
  $index = 0;
  $node = parse_expression_term($tokens, $index);
  $node->print_as_tree();


  $expression_term = "A and B or C and (a + b)";
  echo "\n---------------------------------\n";
  echo $expression_term . "\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression_term, verbose: false);#
  $index = 0;
  $node = parse_expression_term($tokens, $index);
  $node->print_as_tree();


  $expression_term = "a and check(123 + 124)";
  echo "\n---------------------------------\n";
  echo $expression_term . "\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression_term, verbose: false);#
  $index = 0;
  $node = parse_expression_term($tokens, $index);
  $node->print_as_tree();

  $expression = "moin";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);
  $index = 0;
  $node = parse_expression_term($tokens, $index);




  $expression = "a.b.moin(kek,lol)";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);
  $index = 0;
  $node = parse_expression_term($tokens, $index);
  $node->print_as_tree();




  $expression = "a.b.moin(kek.QWERT,lol.FOO)";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);
  $index = 0;
  $node = parse_expression_term($tokens, $index);
  $node->print_as_tree();



  $expression = "moin(kek())";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);
  $index = 0;
  $node = parse_expression_term($tokens, $index);
  $node->print_as_tree();



  #echo "---------------------------------";
  $expression = "a.b.moin(kek.QWERT,lol.FOO.FUCK.YOU(LÖPPT))";
  $expression = "a.b.moin(kek.QWERT())";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_expression_term($tokens, $index);
  $node->print_as_tree();



  $expression = "a.b.moin(kek.QWERT(),)";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_expression_term($tokens, $index);
  $node->print_as_tree();



  $expression = "a.b.moin(kek.QWERT(), lol)";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_expression_term($tokens, $index);
  $node->print_as_tree();


  $expression = "a.b.moin(kek.QWERT(), lol, lol, lol)";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_expression_term($tokens, $index);
  $node->print_as_tree();


  $expression = "a.b.moin(kek.QWERT(), lol.foo, lol, lol)";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_expression_term($tokens, $index);
  $node->print_as_tree();


  $expression = "a.b.moin(kek.QWERT(), lol(), lol, lol)";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_expression_term($tokens, $index);
  $node->print_as_tree();


  $expression = "a.b.moin(kek.QWERT(), lol, lol, lol())";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_expression_term($tokens, $index);
  $node->print_as_tree();



  $expression = "a.b.moin(kek.QWERT(), lol, lol, lol.foo(lol))";
  $expression = "a.b.moin(lol.foo(lol))";
  $expression = "a.b.moin(foo(lol))";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_expression_term($tokens, $index);
  $node->print_as_tree();

  $expression = "a.b.moin(kek.QWERT(), lol, lol, lol.foo(lol))";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_expression_term($tokens, $index);
  $node->print_as_tree();

  $expression = "a.b.moin(foo(lol(bar.kek(123))))";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_expression_term($tokens, $index);
  $node->print_as_tree();

  $expression = "a.b.moin(foo(lol(bar.kek('123'))))";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_expression_term($tokens, $index);
  $node->print_as_tree();


  $expression = "a.b.moin(foo(lol(bar.kek(true, false,))))";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_expression_term($tokens, $index);
  $node->print_as_tree();

  $expression = "\"WURST\"";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_expression_term($tokens, $index);
  $node->print_as_tree();


  $expression = " a + b ( ) ";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_expression_term($tokens, $index);
  $node->print_as_tree();

  echo "All EXPRESSION_TERM tests passed☑️\n";
}