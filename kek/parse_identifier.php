<?php

include_once "data.php";
include_once "tokenizer.php";
include_once "parser_helper.php";

/**
 * @param array<Token> $tokens
 * @param int $index
 * @return AstNode
 */
function parse_identifier(array &$tokens, int &$index, bool $can_be_type = false): AstNode {
  $token = $tokens[$index];
  $node = new IdentifierNode(type:AstNodeType::IDENTIFIER, tokens:[$token], children:[]);
  $node->is_all_uppercase = ctype_upper($token->value[0]);
  $node->starts_with_uppercase = ctype_upper($token->value[0]);
  # check that value is not a reserved keyword
  if (is_keyword($token->value, ignore_types: $can_be_type)) {
    throw new SyntaxError("Identifier cannot be a reserved keyword: $token");
  }
  $index++;
  return $node;
}

if (count(debug_backtrace()) == 0){
  $type_expression = "moin";
  $tokens = tokenize($type_expression, verbose: true);

  $index = 0;
  $node = parse_identifier($tokens, $index);
  var_dump($node);

  try{
    $index = 0;
    $type_expression = "fn";
    $tokens = tokenize($type_expression, verbose: true);
    $node = parse_identifier($tokens, $index);
    var_dump($node);
  } catch (SyntaxError $e) {
    echo $e->getMessage() ."\n";
    echo "LOL";
  }

}



