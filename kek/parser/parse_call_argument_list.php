<?php


/**
 * @param array<Token> $tokens
 * @throws SyntaxError
 */
function parse_call_argument_list(array $tokens, int &$index): CallArgumentListNode {

  $ast_expression_node = new CallArgumentListNode(tokens: [], children: []);

  $first_token = $tokens[$index];
  $is_open_parenthesis = $first_token->type === TokenType::OPEN_PAREN;

  if (!$is_open_parenthesis) {
    throw new SyntaxError("Expected open parenthesis for functioncall argument list, got: $first_token");
  }

  $index++;

  $next_token = $tokens[$index];
  $is_close_parenthesis = $next_token->type === TokenType::CLOSE_PAREN;
  if (!$is_close_parenthesis) {

    while (true) {

      if ($index >= count($tokens)) {
        throw new SyntaxError("Expected closing parenthesis for functioncall argument list, got EOF");
      }

      $next_token_is_close = $tokens[$index]->type == TokenType::CLOSE_PAREN;

      if ($next_token_is_close ) {

        $index++; // we jump over the closing parenthesis
        break; // end of argument list

      }

      # can be a.b.e  = "kek" if this is a map, f.e.
      $expression = parse_expression_term($tokens, $index);
      $token = $tokens[$index];
      if ($token->type == TokenType::EQUALS){
          # we have a named parameter
          # now we parse an assignment
          $index++; # jump over the equals sign
          $expression_term_2 = parse_expression_term($tokens, $index);
          $assignment_node = new AssignmentNode(
            tokens: [$token],
            children: [
              $expression,
              $expression_term_2
            ]
          );
          $ast_expression_node->children[] = $assignment_node;
          continue; # the while loop, next list element ...

      }

      $ast_expression_node->children[] = $expression;


    } # end while



  }
  else {
    $index++; // we jump over the closing parenthesis
  }

  # check for literals, etc.

  return $ast_expression_node;

}
