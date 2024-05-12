<?php

# "special type identifiers": self, fn
# rest is type_identifier
#type_expression_argument_list = ("," type_expression)
#fn(<type expression list>)-><type expression>
#fn(<type expression list>)-><type expression>
#<identifier>(<type expression list>)


/*

Type Int
Construction Literal Player(x=124)
Generic List(Int)
GenericConstruction Literal List(Player)(Player(x=124) Player(x=125))
TypeName()

this is its own function
Function Fn(Int Int)->Int

*/

# todo: check for an ? and | operator
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

  # | operator

  $collected_union_types = [];
  while(true) {
    $token = $tokens[$index];
    if (
      $token->type === TokenType::IDENTIFIER
      || $token->type === TokenType::KEYWORD && $token->value === "local"
      || $token->type === TokenType::KEYWORD && $token->value === "const"
    ) {
      $collected_union_types[] = parse_type_identifier($tokens, $index);
    }

    $token = $tokens[$index];
    if($token->value === "|"){
      $index++;
      continue;
    }


    break;

  }

  if (count($collected_union_types) > 1) {
    $ast_node->children[] = new UnionTypeNode(
      tokens: [],
      children: $collected_union_types
    );
  } else {
    $ast_node->children[] = $collected_union_types[0];
  }

  return $ast_node;

}

function parse_type_identifier(array &$tokens, int &$index): AstNode {
  #echo $tokens[$index + 1]->type->name;
$token = $tokens[$index];
  $ast_node = new TypeIdentifierNode(tokens: [], children: []);

  if($token->value === "local"){
    $index++;
    $ast_node->is_local = true;
  }
  $token = $tokens[$index];

  if($token->value === "const"){
    $index++;
    $ast_node->is_const = true;
  }

  $token = $tokens[$index];
  $next_one_is_brace = count($tokens) > $index + 1 && $tokens[$index + 1]->type === TokenType::OPEN_PAREN;

  $ast_node->tokens[] = $token;
  $index++;

  #echo "flagg";
  if ($next_one_is_brace){
    #echo "parse_type_identifier_list: next one is brace\n";
    $ast_node->children[] = parse_type_identifier_list($tokens, $index);
  }

  $is_option = $tokens[$index]->value == "?";
  if ($is_option) {
    $ast_node->is_optional = true;
    $index++;
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
    $node = parse_type_expression($tokens, $index);
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
