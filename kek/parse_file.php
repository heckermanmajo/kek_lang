<?php


/**
 * @param array<Token> $tokens
 * @return array
 * @throws SyntaxError
 */
function parse_file(array $tokens): array {
  $statements = [];
  $index = -1;

  while ($index < count($tokens)) {

    $index++;
    $token = $tokens[$index];

    if ($token->type === TokenType::NEW_LINE) {
      $index++;
      continue;
    }

    if ($token->type === TokenType::COMMENT || $token->type === TokenType::MULTILINE_COMMENT) {
      $index++;
      continue;
    }

    if ($token->type === TokenType::IDENTIFIER) {
      $statements[] = parse_toplevel_chunk($tokens, $index);
      continue;
    }

    throw new SyntaxError(
      "Unexpected token [{$token->type->name}] at file toplevel: $token "
      . " only definitions are allowed at the top level"
    );

  }

  return $statements;

}
