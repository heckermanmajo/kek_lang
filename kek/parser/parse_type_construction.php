<?php

include_once __DIR__ . "/../data.php";
include_once __DIR__ . "/../tokenizer.php";
include_once __DIR__ . "/../parser_helper.php";

/**
 * @param array<Token> $tokens
 * @param int $index
 * @return AstNode
 */
function parse_type_construction(array &$tokens, int &$index, bool $can_be_type = false): TypeConstructionNode {

  $token = $tokens[$index];
  $node = new IdentifierNode(tokens:[$token], children:[]);
  $node->is_all_uppercase = ctype_upper($token->value[0]);
  $node->starts_with_uppercase = ctype_upper($token->value[0]);

  # check that value is not a reserved keyword
  if (is_keyword($token->value, ignore_types: $can_be_type)) {
    throw new SyntaxError("Identifier cannot be a reserved keyword: $token");
  }

  $index++;
  return $node;
}

