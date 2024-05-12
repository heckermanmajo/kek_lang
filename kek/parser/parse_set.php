<?php


/*

set x = 10
set a.b.c = 5
set <expression> = <expression>

*/

/**
 * @param array<Token> $tokens
 * @param int $index
 * @return SetNode
 */
function parse_set(array $tokens, int &$index): SetNode {

  $node = new SetNode(
    tokens: [],
    children: []
  );

  $token = $tokens[$index];
  if ($token->type === TokenType::KEYWORD && $token->value === "set") {
    $index++;
  } else {
    throw new SyntaxError("Expected keyword 'set', got: $token");
  }

  $expression = parse_expression_term($tokens, $index);

  $token = $tokens[$index];
  if ($token->type === TokenType::EQUALS) {
    $index++;
  } else {
    throw new SyntaxError("Expected equals sign, got: $token");
  }

  $expression_2 = parse_expression_term($tokens, $index);

  $node->children[] = $expression;
  $node->children[] = $expression_2;

  return $node;

}