<?php

include "data.php";


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

  # todo: bug, it cannot handle a # between two ##
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

                                 { ... } # Anonymous code block
                      () { ... } # Captured code block without return
                     () -> float { ... } # Captured code block 
     fn(i: int) -> float  { ... } # Anonymous function
f :: fn(i: int) -> float  { ... } # Named local function
f :: fn(i: int) -> float  { ... } # Named global function
f :: fn(self: MyType, i: int) -> float [capture] { ... } # Method

we can use a struct later on ...
MyStruct :: struct {
  a: Int = 10
  b: String = "Hello"
}
##
#?info -> will replace the small comment with an explantion comment and an example

#?help -> will be replaced with al list of all help comments

# Ocaml lifetime modifiers and locality, life time

name :: "John"

Human :: schema {
  age: Int = 10
  name: String = "John"
}

main :: () {

  capture :: (a: Int, b: Int) -> Int {
    return a + b
  }
  
  my_changeable_human := Human{
    age: 20,
    name: name
  }
  
  my_const_human :: Human{
    age: 20,
    name: "John"
  }
  
}

##  hello_world :: () -> String {
  local_var := "Hello, World!"
  
  a :: [] # [1]
  a[] = 1
  a = [1, 2, 3]  erorr
  return local_var
}
##

#"Hello, World!"
#'
#This is a multiline string
#keke
#keke
#'
#"
#  ANOTHER STRING
#  Multiline
#"
CODE;

$tokens = tokenize_first($code, get_signs());
$tokens = collapse_strings($tokens);
$tokens = collapse_comments($tokens);

foreach ($tokens as $token) {
  echo $token . "\n";
}

exit();

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

class CompilerState {
  public function __construct(
    public array $pre_processor_functions
  ) {
  }
}

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

enum AstNodeType{

}

class AstNode {
  public function __construct(
    public AstNodeType $type,
    /** @var array<Token> */
    public array $tokens,
    /** @var array<AstNode> */
    public array $children,
    public ?AstNode $generated_by = null
  ) {
  }
}

class SyntaxError extends Exception { }

function parse_toplevel_chunk(array &$tokens, int &$index): AstNode|SyntaxError {

  $identifier = $tokens[$index];
  $double_colon = $tokens[$index + 1];
  if ($double_colon->type !== TokenType::DOUBLE_COLON) {
    throw new SyntaxError("Expected '::' after top level identifier, got $double_colon");
  }

  $index += 2;
  $const_expression = parse_const_expression($tokens, $index);

}

// can be a function, a schema, or a field definition
function parse_const_expression(){}

parse_file($tokens);


# apply (ast-processor) preprocessor templates and optional compilation


# type check per file
# some names are not known -> put them into a list
# read all other files
# check that at the end the list is empty


# js/php (c) code generation via phptmplates


