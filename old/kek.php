<?php



$keywords = [

  "var",
  "const",

  "fn",
  "schema",
  "interface",
  "sub",
  "object",

  "use",

  "return",

  "self",

  "if",
  "then",
  "else",
  "elif",

  "and",
  "or",
  "not",

  "catch",
  "recover",
  "error",

  "nil",

];

$signs = [
  ":",
  " ",
  "=",
  ";",
  ">",
  "<",
  "+",
  "-",
  "*",
  "/",
  "%",
  "!",  # this function throws an error
  "?",  # this function returns an optional value
  "|", # this function can return multiple types
  "#", # single line comment
  "@", # decorator -> metadata
];

$double_signs = [
  ":=",
  "::",
  "->",
  "==",
  "!=",
  ">=",
  "<=",
  "##",
  "[[",
  "]]",
];

$triple_signs = [
  # no ones yet
];
class Token
{
  public string $value;
  public int $line;
  public int $column;

  public function __construct(string $value, int $line, int $column) {
    $this->value = $value;
    $this->line = $line;
    $this->column = $column;
  }
}

function split_code(string $code, array $signs, array $double_signs, array $triple_signs): array {
  $tokens = [];
  $as_chars = str_split($code);
  $current_token = "";
  $line = 1;
  $column = 1;

  for($i=0; $i<count($as_chars); ++$i){

    $char = $as_chars[$i];

    // Checking for triple sign.
    if($i+2 < count($as_chars) && in_array($char.$as_chars[$i+1].$as_chars[$i+2], $triple_signs)) {
      if($current_token !== "") {
        $tokens[] = new Token($current_token, $line, $column-strlen($current_token));
        $current_token = "";
      }
      $tokens[] = new Token($char.$as_chars[$i+1].$as_chars[$i+2], $line, $column);
      $column += 3;
      $i += 2;
      continue;
    }

    // Checking for double sign.
    if($i+1 < count($as_chars) && in_array($char.$as_chars[$i+1], $double_signs)) {
      if($current_token !== "") {
        $tokens[] = new Token($current_token, $line, $column-strlen($current_token));
        $current_token = "";
      }
      $tokens[] = new Token($char.$as_chars[$i+1], $line, $column);
      $column += 2;
      $i++;
      continue;
    }

    // Checking for single sign.
    if(in_array($char, $signs)) {
      if($current_token !== "") {
        $tokens[] = new Token($current_token, $line, $column-strlen($current_token));
        $current_token = "";
      }
      $tokens[] = new Token($char, $line, $column);
      $column++;
      continue;
    }

    // Checking for line break.
    if($char === "\n") {
      if($current_token !== "") {
        $tokens[] = new Token($current_token, $line, $column-strlen($current_token));
        $current_token = "";
      }
      $line++;
      $column = 1;
      continue;
    }

    $current_token .= $char;
    $column++;

  }

  if($current_token !== "") {
    $tokens[] = new Token($current_token, $line, $column-strlen($current_token));
  }

  return $tokens;
}

$code = '$x = 64 + 36 / (43-23); # testing';

$tokens = split_code($code, $signs, $double_signs, $triple_signs);

// Testing: total tokens count
assert(count($tokens) === 17, "Test failed: The code should be split to 17 tokens.");

// Testing: the first token
assert($tokens[0]->value === '$x' && $tokens[0]->line === 1 && $tokens[0]->column === 1, "Test failed: The first token should be \$x");

// Testing: the second token
assert($tokens[1]->value === ' ' && $tokens[1]->line === 1 && $tokens[1]->column === 3, "Test failed: The second token should be a space.");

// Testing: the last token
assert($tokens[count($tokens)-1]->value === ' testing' && $tokens[count($tokens)-1]->line === 1 && $tokens[count($tokens)-1]->column === 31, "Test failed: The last token should be ' testing'");

//test case with double signs
$code = 'if (x >= 10 && x <= 20}';
$tokens2 = split_code($code, $signs, $double_signs, $triple_signs);
assert(count($tokens2) === 13, "Test failed : The code should split to 13 tokens.");
assert($tokens2[3]->value === '>=' && $tokens2[3]->line === 1 && $tokens2[3]->column === 8, "Test failed : The token should be '>='");
assert($tokens2[7]->value === '<=' && $tokens2[7]->line === 1 && $tokens2[7]->column === 17, "Test failed : The token should be '<='");

echo "Tokenizer: All one line tests are passed!\n";

//Testing multiline code.
$multilineCode = "
\$x = 64 + 36;
if (\$x > 50) {
    \$x = \$x / 2;
    return \$x;
}";

$multilineTokens = split_code($multilineCode, $signs, $double_signs, $triple_signs);

//Testing: total tokens count.
assert(count($multilineTokens) === 21, "Test failed: Multiline code should be split into 21 tokens.");

//Testing: the first token.
assert($multilineTokens[0]->value === '\$x' && $multilineTokens[0]->line === 2 && $multilineTokens[0]->column === 1, "Test failed: First token of multiline code should be '\$x'.");

//Testing: a token from the second line.
assert($multilineTokens[5]->value === '50' && $multilineTokens[5]->line === 3 && $multilineTokens[5]->column === 7, "Test failed: The token '50' should be on the 3rd line, 7th column.");

//Testing: a token from the third line.
assert($multilineTokens[9]->value === '\$x ' && $multilineTokens[9]->line === 4 && $multilineTokens[9]->column === 5, "Test failed: The token '\$x ' should be on the 4th line, 5th column.");

echo "Tokenizer: All multiline tests passed!\n";

interface Parser{
  static function parse(array $tokens, int $index);
}


function parse_expression(array $tokens, int $index){}


class NumberLiteral implements Parser{
  public static function parse(array $tokens, int $index){
    # ...
  }
}


class StringLiteral implements Parser{
  public static function parse(array $tokens, int $index){
    # ...
  }
}

class MapLiteral implements Parser{
  public static function parse(array $tokens, int $index){
    # ...
  }
}

class MapValue implements Parser{
  public static function parse(array $tokens, int $index){
    # ...
  }
}


class NameLiteral implements Parser{
  public static function parse(array $tokens, int $index){
    # ...
  }
}

class Variable implements Parser{
  public static function parse(array $tokens, int $index){
    # ...
  }
}

class FunctionCall implements Parser{
  public static function parse(array $tokens, int $index){
    # ...
  }
}

function parse_schema() {}

function parse_function(){}


function parse_block(){}

function parse_try_block(){}

function parse_catch_block(){}

function parse_recover_block(){}

function parse_method_call(){}

function parse_argument_definition_list(){}

function parse_argument_list(){}

function parse_named_argument(){}

function parse_schema_field_definition(){}


function parse_type_expression(){}
function parse_type_name(){}











