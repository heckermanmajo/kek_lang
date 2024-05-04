<?php


function analyze_non_sign_word(string $word, int $line, int $column): Token {
  if (is_numeric($word)) {
    return new Token(TokenType::NUMBER_LITERAL, $word, $line, $column);
  }
  return new Token(TokenType::IDENTIFIER, $word, $line, $column);
}

/**
 * @param string $raw_code
 * @param array $signs
 * @return array<Token>
 */
function tokenize_first(string $raw_code, array $signs): array {

  # sort signs by length
  uksort($signs, fn($a, $b) => strlen($b) - strlen($a));

  $tokens = [];
  $line = 1;
  $column = 1;
  $buffer = '';

  # important: signs can have variable length
  # so we need to check for the longer signs first

  while (strlen($raw_code) > 0) {
    # our signs are ordered by length from longest to shortest
    foreach ($signs as $sign => $type) {

      $we_have_a_match = str_starts_with($raw_code, $sign);

      if ($we_have_a_match) {
        # if we have found a sign, and the buffer is not empty
        # the buffer contains a word that is not a sign
        if ($buffer != '') {
          $tokens[] = analyze_non_sign_word($buffer, $line, $column - strlen($buffer));
          $buffer = '';
        }
        $tokens[] = new Token($type, $sign, $line, $column);

        $column += strlen($sign);
        if ($type === TokenType::NEW_LINE) {
          $line++;
          $column = 1;
        }

        # now we remove the sign from the raw code -> we cut it off
        $raw_code = substr($raw_code, strlen($sign));

        # we found a sign, so we can continue with the next character
        # which is the next iteration of the while loop
        continue 2;
      }

    }

    # if we are here, we have not found a sign
    $buffer .= $raw_code[0];
    $column++;
    $raw_code = substr($raw_code, 1);

  }

  # if we are here, we have reached the end of the raw code
  # if the buffer is not empty, we need to analyze it
  if ($buffer != '') {
    $tokens[] = analyze_non_sign_word($buffer, $line, $column - strlen($buffer));
  }

  return $tokens;

}

# we want to treat comments and string each as a single token
# string-delimiter: " or '
# string escape sign: \
# comment-delimiter: #
# multiline comment start and end marked with: ##

/**
 * @param array<Token> $tokens
 * @return array<Token>
 */
function collapse_strings(array $tokens): array {

  $real_tokens = [];
  $in_string = false;
  $string_sign = ''; #  " or '
  $string_buffer = '';

  foreach ($tokens as $token) {
    if ($token->type === TokenType::STRING_LITERAL_DOUBLE || $token->type === TokenType::STRING_LITERAL_SINGLE) {
      if ($in_string && $string_sign === $token->value) {
        $real_tokens[] = new Token($token->type, $string_buffer, $token->line, $token->column);
        $in_string = false;
        $string_buffer = '';
      } else {
        $in_string = true;
        $string_sign = $token->value;
        $string_buffer = '';
      }
    } else if ($in_string) {
      $string_buffer .= $token->value;
    } else {
      $real_tokens[] = $token;
    }
  }

  return $real_tokens;
}

function collapse_comments(array $tokens): array {
  $real_tokens = [];
  $in_comment = false;
  $in_multiline_comment = false;
  $comment_buffer = '';
  $init_line = 0;
  $init_column = 0;

  foreach ($tokens as $token) {
    if ($token->type === TokenType::MULTILINE_COMMENT) {
      if ($in_multiline_comment) {
        // End of multiline comment. We push the entire comment as a single token.
        $real_tokens[] = new Token(TokenType::MULTILINE_COMMENT, $comment_buffer, $init_line, $init_column);
        $comment_buffer = '';
        $in_multiline_comment = false;
      } else {
        // Start of multiline comment
        $in_multiline_comment = true;
        $init_line = $token->line;
        $init_column = $token->column;
      }
    } else if ($token->type === TokenType::COMMENT) {
      if ($in_comment) {
        // This condition shouldn't occur as each comment line is treated as separate token
        throw new Exception("This should not happen");
      } else {
        // Single line comment
        $in_comment = true;
        $comment_buffer = $token->value;
        $init_line = $token->line;
        $init_column = $token->column;
      }
    } else if ($in_comment) {
      if ($token->type === TokenType::NEW_LINE) {
        // End of single line comment. We push the entire line comment as a single token.
        $real_tokens[] = new Token(TokenType::COMMENT, $comment_buffer, $init_line, $init_column);
        $comment_buffer = '';
        $real_tokens[] = $token;
        $in_comment = false;
      } else {
        // Inside of single line comment. Append the token to the comment buffer.
        $comment_buffer .= $token->value;
      }

    } else if ($in_multiline_comment) {
      // Inside of multiline comment. Append the token to the comment buffer.
      $comment_buffer .= $token->value;
    } else {
      // Not inside a comment. Just push the token.
      $real_tokens[] = $token;
    }
  }

  // Leftover multiline comment
  if ($in_multiline_comment) {
    $real_tokens[] = new Token(TokenType::MULTILINE_COMMENT, $comment_buffer, $init_line, $init_column);
  }

  // Leftover single line comment
  if ($in_comment) {
    $real_tokens[] = new Token(TokenType::COMMENT, $comment_buffer, $init_line, $init_column);
  }

  return $real_tokens;
}

$code = <<<CODE
# This is a comment
## This is a multiline comment
KEK
##
"Hello, World!"
'
This is a multiline string
keke
keke
'
"
  ANOTHER STRING
  Multiline
"
CODE;

$tokens = tokenize_first($code, $signs);
$tokens = collapse_strings($tokens);
$tokens = collapse_comments($tokens);

foreach ($tokens as $token) {
  echo $token . "\n";
}
