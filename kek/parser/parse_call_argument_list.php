<?php

include_once __DIR__ . "/../data.php";
include_once __DIR__ . "/../tokenizer.php";
include_once __DIR__ . "/../parser_helper.php";
include_once __DIR__ . "/parse_expression.php";


/**
 * @param array<Token> $tokens
 * @throws SyntaxError
 */
function parse_call_argument_list(array $tokens, int &$index): CallArgumentListNode {

  $ast_expression_node = new CallArgumentListNode(tokens: [], children: [
  ]);

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
      $node = parse_expression($tokens, $index);
      $ast_expression_node->children[] = $node;

      if ($index >= count($tokens)) {
        throw new SyntaxError("Expected closing parenthesis for functioncall argument list, got EOF");
      }

      $next_token_is_close = $tokens[$index]->type == TokenType::CLOSE_PAREN;

      if($index+1 >= count($tokens)) {
        $next_next_token_is_close = false;
      }else {
        $next_next_token_is_close = $tokens[$index + 1]->type == TokenType::CLOSE_PAREN;
      }

      if ($next_token_is_close || $next_next_token_is_close) {

        if ($next_next_token_is_close) {

          # only if we have a trailing comma
          # remove the last element -> the trailing comma
          if ($tokens[$index]->type === TokenType::COMMA) {
            #throw new SyntaxError("Expected trailing comma or noting, got: $tokens[$index]");
            $index++; // we jump over the trailing comma
          }

        }
        $index++; // we jump over the closing parenthesis
        break; // end of argument list

      }else{

        if ($tokens[$index]->type !== TokenType::COMMA) {
          throw new SyntaxError("Expected comma or closing parenthesis for functioncall argument list, got: $tokens[$index]");
        }
        $index++; // we jump over the comma
      }

    }

  }
  else {
    $index++; // we jump over the closing parenthesis
  }

  # check for literals, etc.

  return $ast_expression_node;

}


if (count(debug_backtrace()) == 0) {

  $type_expression = "(moin,lol)";
  $tokens = tokenize($type_expression, verbose: false);

  $index = 0;
  $node = parse_call_argument_list($tokens, $index);
  $node->print_as_tree();

  $type_expression = "(
    moin,
    
    lol     ,
  
  wirst,
  )";
  $tokens = tokenize($type_expression, verbose: false);

  $index = 0;
  $node = parse_call_argument_list($tokens, $index);
  $node->print_as_tree();



  echo "All tests passed☑️\n";
}