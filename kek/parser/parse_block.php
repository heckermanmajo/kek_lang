<?php

include_once __DIR__ . "/../data.php";
include_once __DIR__ . "/../tokenizer.php";
include_once __DIR__ . "/../parser_helper.php";

/*

 We expect:

 {

 }

 (<call list>) -> <typeexpression> {

 }

 () -> <typeexpression> {

 }

   (){

 }

 ->Int{

 }



*/

/**
 * @param array<Token> $tokens
 * @throws SyntaxError
 */
function parse_block(array $tokens, int &$index):BlockNode {

  $ast_node = new BlockNode(tokens: [], children: []);

  $token = $tokens[$index];

  if ($token->type === TokenType::OPEN_BRACE) {
    $name_list = parse_name_list($tokens, $index);
    $ast_node->children[] = $name_list;
  }

  $token = $tokens[$index];
  if($token->type === TokenType::ARROW){
    $index++;
    $type_expression = parse_type_expression($tokens, $index);
    $ast_node->children[] = $type_expression;
  }

  $block = parse_scope($tokens, $index);

  $ast_node->children[] = $block;

  return $ast_node;

}

/**
 * @param array<Token> $tokens
 * @param int $index
 * @return array
 */
function parse_name_list(array $tokens, int &$index): NameListNode {

  $name_list_node  = new NameListNode(tokens: [], children: []);
  $token = $tokens[$index];

  if ($token->type !== TokenType::OPEN_PAREN) {
    throw new SyntaxError("Expected (, got: $token");
  }

  $index++; // jump over "("

  while ($token->type === TokenType::IDENTIFIER) {
    $name_list_node->children[] = $token;
    $index++;
    $token = $tokens[$index];
  }

  $token = $tokens[$index];
  if ($token->type !== TokenType::CLOSE_PAREN) {
    throw new SyntaxError("Expected ), got: $token");
  }

  return $name_list_node;

}


if (count(debug_backtrace()) == 0) {


  echo "All tests passed☑️\n";
}