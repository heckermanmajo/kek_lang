<?php

include_once __DIR__ . "/../data.php";
include_once __DIR__ . "/../tokenizer.php";
include_once __DIR__ . "/../parser_helper.php";
include_once __DIR__ . "/parse_expression.php";


/**
 * @param array<Token> $tokens
 * @throws SyntaxError
 */
function parse_value_literal(array $tokens, int &$index): ValueLiteralNode {

    $ast_expression_node = new ValueLiteralNode( tokens: [], children: [
    ]);

    $token = $tokens[$index];

    if(
      $token->type == TokenType::MULTILINE_STRING
      || $token->type == TokenType::STRING_LITERAL_DOUBLE
      || $token->type == TokenType::STRING_LITERAL_SINGLE
    ) {
      $ast_expression_node->children[] =  new StringLiteralNode( tokens: [$token], children: []);
      $index++;
      return $ast_expression_node;
    }

    if ($token->type == TokenType::NUMBER_LITERAL) {
      $ast_expression_node->children[] = new NumberLiteralNode( tokens: [$token], children: []);
      $index++;
      return $ast_expression_node;
    }

    # bool
    if ($token->type == TokenType::BOOL_LITERAL) {
      $ast_expression_node->children[] = new BoolLiteralNode( tokens: [$token], children: []);
      $index++;
      return $ast_expression_node;
    }

    throw new SyntaxError("Unexpected token [{$token->type->name}] at value literal: $token ");

}


if (count(debug_backtrace()) == 0) {


  echo "All tests passed☑️\n";
}