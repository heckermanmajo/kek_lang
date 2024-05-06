<?php

# helper functions for parsing
/**
 * This functions allows to look forward in the token stream.
 *
 * @throws SyntaxError
 */
function look_forward(
  array $tokens,
  int $num,
  bool $throw_on_end = true
): ?TokenType {
  if (count($tokens) <= $num) {
    if ($throw_on_end) {
      throw new SyntaxError("Unexpected end of file");
    }
    return null;
  }
  return $tokens[$num]->type;
}

function look_backward(
  array $tokens,
  int $num,
  bool $throw_on_end = true
): ?TokenType {
  if ($num < 0) {
    if ($throw_on_end) {
      throw new SyntaxError("Unexpected start of file");
    }
    return null;
  }
  return $tokens[$num]->type;
}

/**
 * This function allows to look forward in the token stream
 * and expect a certain token type.
 *
 * @param array $tokens
 * @param int $num
 * @param TokenType $tokenType
 * @param string $error_message
 * @return void
 */
function expect_forward(
  array $tokens,
  int $num,
  TokenType $tokenType,
  string $error_message
) {
  $next_token = look_forward($tokens, $num);
  if ($next_token !== $tokenType) {
    throw new SyntaxError($error_message);
  }
}

/**
 * Check if the next token is a non-semantic token
 * like a comment or a newline.
 *
 * @param array $tokens
 * @param int $index
 * @return bool
 */
function next_is_non_semantic_token(
  array $tokens,
  int $index
): bool {
  return $tokens[$index]->type === TokenType::COMMENT
    || $tokens[$index]->type === TokenType::MULTILINE_COMMENT
    || $tokens[$index]->type === TokenType::NEW_LINE
    || $tokens[$index]->type === TokenType::WHITE_SPACE
    || $tokens[$index]->type === TokenType::TAB;
}

/**
 * Skip all non-semantic tokens by incrementing the index
 * until a semantic token is found.
 *
 * @param array $tokens
 * @param int $index
 * @return void
 */
function skip_non_semantic_tokens(array $tokens, int &$index): void {
  while (next_is_non_semantic_token($tokens, $index)) {
    $index++;
  }
}

