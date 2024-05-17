<?php
/**
 * Keklang refactoring.
 *
 * -> Create PHP code by an AST
 *    So we reach te ideal form of the AST,
 *    then we create such an AST from the code.
 *
 * -> Put the tokenizer in
 *    (fix the single line comment bug)
 *
 * -> Copy paste the parser over, based on
 *    the old parser, apply the new syntax simplifications
 *
 * -> Enrich the AST, until we reach the format
 *    we have already tested to be working to
 *    generate the PHP code.
 *
 *
 * We can delete names after they are created instead of using real
 * scopes.
 *
 */

$KEYWORDS = [
  "enum",
  "struct",
  "fn",
  "ret",

  "import",
  "as",
  "pub",
  "extern",

  "if",
  "elif",
  "else",
  "for",

  "it",
  "_",

  "and",
  "or",
  "not",

];

$SIGNS = [

  "?",
  "??",
  "|",

  "(",
  ")",
  "[",
  "]",
  "{",
  "}",

  ",",

  ".",
  ":",

  "::",
  ":=",
  "=",

  "+",
  "-",
  "*",
  "/",
  "%",
  "**",

  "->",
  "..",
  ";"

];

$OPERATORS = [

  "??" => ["type" => "binary", "precedence" => 1],

  "*" => ["type" => "binary", "precedence" => 2],
  "/" => ["type" => "binary", "precedence" => 2],
  "%" => ["type" => "binary", "precedence" => 2],
  "+" => ["type" => "binary", "precedence" => 3],
  "-" => ["type" => "binary", "precedence" => 3],
  "**" => ["type" => "binary", "precedence" => 9],

  "==" => ["type" => "binary", "precedence" => 4],
  "!=" => ["type" => "binary", "precedence" => 4],
  "<" => ["type" => "binary", "precedence" => 4],
  "<=" => ["type" => "binary", "precedence" => 4],
  ">" => ["type" => "binary", "precedence" => 4],
  ">=" => ["type" => "binary", "precedence" => 4],
  "not" => ["type" => "unary", "precedence" => 6],
  "and" => ["type" => "binary", "precedence" => 7],
  "or" => ["type" => "binary", "precedence" => 8],

];

class KekType{}

enum NodeType{
  case ImportNode;

  case TypeExpression;

  case FunctionDef;

  case TypeDef;

  case CondNode; #  if, elif, else, for

  case Block; # Block full of conds,  statements, expressions and blocks
  # structs and enums do NOT contain blocks
  # blocks can return, but not all blocks have a return type

  case Expression; # every expression is a function call
  # every expression has a return type, it can be none if the expression
  # is a function/method call that does not return anything
  # interpreting the expression: done by calling the functions after looking up the fuctions
  # a simple identifier will be converted into a function call lookup_in_current_scope(identifier)

  case ValueStatement; # variable/const creation, assignment

}

class AstNode{
  public KekType $type;
  public NodeType $node_type;
  public int $line;
  public int $column;
  public array $children = [

  ];
  public bool $is_const;
  public bool $is_pub;
  public bool $is_extern;
}


function parse_type_expression(){}
function parse_type_def(){}
function parse_block(){}
function parse_conditional(){}
function parse_expression_term(){}
function parse_function_definition(){}
function parse_import(){}
function parse_file(){}