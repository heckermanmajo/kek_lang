<?php

include_once __DIR__ . "/../data.php";
include_once __DIR__ . "/../tokenizer.php";
include_once __DIR__ . "/../parser_helper.php";
include_once __DIR__ . "/parse_identifier.php";


/**
 * @throws SyntaxError
 */
function parse_expression(array $tokens, int &$index): ExpressionNode {
  $ast_expression_node = new ExpressionNode(type:AstNodeType::EXPRESSION, tokens:[], children:[

  ]);

  # is this token an identifier?
  if ($tokens[$index]->type === TokenType::IDENTIFIER) {

    # if the token after the identifier is a dot, then this is a member access

    # if it is a (, then this is a function call

    # else it is a simple identifier

    $node = parse_identifier($tokens, $index);
    $ast_expression_node->children[] = $node;


  }



  # check for literals, etc.

  return $ast_expression_node;
}


if (count(debug_backtrace()) == 0) {

  $type_expression = "moin";
  $tokens = tokenize($type_expression, verbose: false);

  $index = 0;
  $node = parse_expression($tokens, $index);



  echo "All tests passed☑️\n";
}