<?php

include_once __DIR__ . "/../data.php";
include_once __DIR__ . "/../tokenizer.php";
include_once __DIR__ . "/../parser_helper.php";
include_once __DIR__ . "/parse_identifier.php";
include_once __DIR__ . "/parse_call_argument_list.php";
include_once __DIR__ . "/parse_dot_access_expression.php";
include_once __DIR__ . "/parse_value_literal.php";

/**
 * @param array<Token> $tokens
 * @param int $index
 * @return AstNode
 */
function parse_const_definition(array &$tokens, int &$index, bool $can_be_type = false): ConstDefinitionNode {

  $node = new ConstDefinitionNode(tokens: [], children: []);

  $token = $tokens[$index];

  $identifier_node = parse_identifier($tokens, $index);
  $node->children[] = $identifier_node;

  $token = $tokens[$index];
  if ($token->type !== TokenType::DOUBLE_COLON) {
    throw new SyntaxError("Expected double colon, got: $token");
  }

  $index++;


  ## literal
  if ($tokens[$index]->type === TokenType::STRING_LITERAL_DOUBLE || $tokens[$index]->type === TokenType::STRING_LITERAL_SINGLE || $tokens[$index]->type === TokenType::NUMBER_LITERAL || $tokens[$index]->type === TokenType::BOOL_LITERAL) {
    $value_literal_node = parse_value_literal($tokens, $index);
    $node->children[] = $value_literal_node;
    return $node;
  }

  ## it could be a type expression  -> if this is a field of a trait
  $is_type = ctype_upper($tokens[$index]->value[0]);
  if ($is_type) {
    $type_expression_node = parse_type_expression($tokens, $index);
    $node->children[] = $type_expression_node;
    return $node;
  }

  ## if can have a function
  if($tokens[$index]->type === TokenType::KEYWORD && $tokens[$index]->value === "fn") {
    # todo: parse a function definition block
    # function is a problem
    # function needs function argument list
    # needs return type
    # needs scope
    # a block is a call list followed by -> or {
    return $node;
  }


  ## if can define a struct
  if($tokens[$index]->type === TokenType::KEYWORD && $tokens[$index]->value === "struct") {
    # todo: parse a struct
    return $node;
  }

  ## if can define a trait
  if($tokens[$index]->type === TokenType::KEYWORD && $tokens[$index]->value === "trait") {
    # todo: parse a trait
    return $node;
  }

  ## if can define a enum
  if($tokens[$index]->type === TokenType::KEYWORD && $tokens[$index]->value === "enum") {
    # todo: parse a enum
    return $node;
  }

  ## it can be a expression -> this need to be checked in the name resolver
  ## if the assignment is valid

  $expression_node = parse_expression($tokens, $index);
  $node->children[] = $expression_node;


  return $node;
}



if (count(debug_backtrace()) == 0){

  $expression = "my_name :: 124";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_const_definition($tokens, $index);
  $node->print_as_tree();


  $expression = "my_name :: foo()";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_const_definition($tokens, $index);
  $node->print_as_tree();


  $expression = "my_name :: foo(bar(), lol)";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_const_definition($tokens, $index);
  $node->print_as_tree();


  echo "\n---------------------------------\n";
  echo "All EXPRESSION tests passed☑️\n";
  echo "---------------------------------\n";

}



