<?php

# "special type identifiers": self, fn
# rest is type_identifier
#type_expression_argument_list = ("," type_expression)
#fn(<type expression list>)-><type expression>
#fn(<type expression list>)-><type expression>
#<identifier>(<type expression list>)

/**
 * @param array<Token> $tokens
 * @param int $index
 * @return AstNode
 */
function parse_type_expression(array &$tokens, int &$index): TypeExpressionNode {

  $ast_node = new TypeExpressionNode(tokens: [], children: []);

  # consume local and const
  # can be directly set on the type expression node

  $token = $tokens[$index];

  if($token->type === TokenType::KEYWORD && $token->value === "local"){
    $ast_node->is_local = true;
    $index++;
  }
  if ($token->type === TokenType::KEYWORD && $token->value === "const") {
    $ast_node->is_const = true;
    $index++;
  }

  $is_identifier = $token->type === TokenType::IDENTIFIER;
  $is_self = $token->value === "Self" && $token->type === TokenType::IDENTIFIER;
  $is_fn = $token->value === "Fn" && $token->type === TokenType::IDENTIFIER;

  #echo "lol";
  if (($is_identifier || $is_self) && $token->value != "Fn") {
    $ast_node->children[] = parse_type_identifier($tokens, $index);
    return $ast_node;
  }

  if($is_fn){
    #echo "kekekekek\n";
    $ast_node->children[] = parse_type_function_signature($tokens, $index);
    return $ast_node;
  }

  throw new SyntaxError("Expected type identifier or Self or Fn, got: $token");

}

function parse_type_identifier(array &$tokens, int &$index): AstNode {
  #echo $tokens[$index + 1]->type->name;
  $next_one_is_brace = count($tokens) > $index + 1 && $tokens[$index + 1]->type === TokenType::OPEN_PAREN;
  $token = $tokens[$index];

  $ast_node = new TypeIdentifierNode(tokens: [$token], children: []);
  $index++;

  #echo "flagg";
  if ($next_one_is_brace){
    #echo "parse_type_identifier_list: next one is brace\n";
    $ast_node->children[] = parse_type_identifier_list($tokens, $index);
  }

  return $ast_node;

}

function parse_type_identifier_list(array &$tokens, int &$index): AstNode {

  $ast_node = new TypeArgumentListNode(tokens: [], children: []);
  $token = $tokens[$index];
  $is_open_brace = $token->type === TokenType::OPEN_PAREN;
  if (!$is_open_brace) {
    throw new SyntaxError("Expected open brace for type argument list, got: $token");
  }
  $index++; // consume the open brace

  $empty_list = $tokens[$index]->type === TokenType::CLOSE_PAREN;
  if ($empty_list) {
    $index++; // consume the closing brace
    return $ast_node;
  }

  while (true) {
    $node = parse_type_identifier($tokens, $index);
    $ast_node->children[] = $node;

    if ($index >= count($tokens)) {
      throw new SyntaxError("Expected closing brace for type argument list, got EOF");
    }

    $next_token_is_close = $tokens[$index]->type == TokenType::CLOSE_PAREN;

    if ($next_token_is_close) {
      $index++; // consume the closing brace
      break; // end of argument list
    }

  }

  return $ast_node;

}

function parse_type_function_signature(array &$tokens, int &$index): AstNode {
  # Fn () -> TypeExpression
  $token = $tokens[$index];
  $ast_node = new TypeFunctionNode(tokens: [$token], children: []);
  $next_one_is_open_paren = count($tokens) > $index + 1 && $tokens[$index + 1]->type === TokenType::OPEN_PAREN;

  $index++;

  if (!$next_one_is_open_paren) {
    throw new SyntaxError("Expected open parenthesis for function signature, got: $tokens[$index]");
  }

  $list_node = parse_type_identifier_list($tokens, $index);
  $ast_node->children[] = $list_node;

  $next_is_arrow = count($tokens) > $index && $tokens[$index]->type === TokenType::ARROW;

  if($next_is_arrow){
    $index++;
    $type_expression_node = parse_type_expression($tokens, $index);
    $return_type_node = new ReturnTypeNode(tokens: [], children: [$type_expression_node]);
    $ast_node->children[] = $return_type_node;
  }

  return $ast_node;

}


if (count(debug_backtrace()) == 0) {

  $expression = "List";
  echo "\n---------------------------------\n";
  echo $expression . "\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_type_expression($tokens, $index);
  $node->print_as_tree();


  $expression = "List(String)";
  echo "\n---------------------------------\n";
  echo $expression . "\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_type_expression($tokens, $index);
  $node->print_as_tree();


  $expression = "Fn()->List(String)";
  echo "\n---------------------------------\n";
  echo $expression . "\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_type_expression($tokens, $index);
  $node->print_as_tree();

  $expression = "Fn()";
  echo "\n---------------------------------\n";
  echo $expression . "\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_type_expression($tokens, $index);
  $node->print_as_tree();


  $expression = "Fn(Self,Int)->Self";
  echo "\n---------------------------------\n";
  echo $expression . "\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_type_expression($tokens, $index);
  $node->print_as_tree();


  $expression = "List(String,Map(String,Int))";
  echo "\n---------------------------------\n";
  echo $expression . "\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_type_expression($tokens, $index);
  $node->print_as_tree();


  $expression = "List(String,Map(String,Int))";
  echo "\n---------------------------------\n";
  echo $expression . "\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_type_expression($tokens, $index);
  $node->print_as_tree();

  $expression = "Fn(Map(String,Int),List(String))->List(String)";
  echo "\n---------------------------------\n";
  echo $expression . "\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_type_expression($tokens, $index);
  $node->print_as_tree();


  $expression = "Fn(Map(String,Int),List(Map(String,Union(Nil,Int,Error))))->List(Map(String,Union(Nil,Int,Error)))";
  echo "\n---------------------------------\n";
  echo $expression . "\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_type_expression($tokens, $index);
  $node->print_as_tree();



  $expression = "Fn(Map(String,Int),List(Map(String,Union(Nil,Int,Error))))";
  echo "\n---------------------------------\n";
  echo $expression . "\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_type_expression($tokens, $index);
  $node->print_as_tree();




  echo "\n---------------------------------\n";
  echo "All TYPE-EXPRESSION tests passed☑️\n";
  echo "---------------------------------\n";
}



