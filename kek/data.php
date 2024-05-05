<?php


enum TokenType: string {

  case WHITE_SPACE = 'WHITE_SPACE';

  case TAB = 'TAB';

  case NEW_LINE = 'NEW_LINE';

  case NUMBER_LITERAL = 'NUMBER_LITERAL';

  case STRING_LITERAL_SINGLE = 'STRING_LITERAL_SINGLE';

  case STRING_LITERAL_DOUBLE = 'STRING_LITERAL_DOUBLE';

  case COMMENT = 'COMMENT';

  case MULTILINE_COMMENT = 'MULTILINE_COMMENT';

  case MULTILINE_STRING = 'MULTILINE_STRING';

  case STRING_ESCAPE_DOUBLE = 'STRING_ESCAPE_DOUBLE';

  case STRING_ESCAPE_SINGLE = 'STRING_ESCAPE_SINGLE';

  case IDENTIFIER = 'IDENTIFIER';

  case KEYWORD = 'KEYWORD';

  case COLON = 'COLON';

  case EQUALS = 'EQUALS';

  case COMMA = 'COMMA';

  case OPERATOR = 'OPERATOR';

  case OPEN_PAREN = 'OPEN_PAREN';

  case CLOSE_PAREN = 'CLOSE_PAREN';

  case OPEN_BRACE = 'OPEN_BRACE';

  case CLOSE_BRACE = 'CLOSE_BRACE';

  case OPEN_BRACKET = 'OPEN_BRACKET';

  case CLOSE_BRACKET = 'CLOSE_BRACKET';

  case ARROW = '->';

  case DOUBLE_COLON = 'DOUBLE_COLON';

  case DOT = 'DOT';

  case SEMICOLON = 'SEMICOLON';

}

function get_signs() : array {
  $signs = [
    " " => TokenType::WHITE_SPACE,
    "\t" => TokenType::TAB,
    "\n" => TokenType::NEW_LINE,
    '##' => TokenType::MULTILINE_COMMENT,
    '#' => TokenType::COMMENT,
    '"' => TokenType::STRING_LITERAL_DOUBLE,
    "'" => TokenType::STRING_LITERAL_SINGLE,
    "\\\"" => TokenType::STRING_ESCAPE_DOUBLE,
    "\\'" => TokenType::STRING_ESCAPE_SINGLE,
  ];
  return $signs;
}
enum StatementType: string {
  case Literal = 'Literal';
}

enum Keywords: string {
  case _PUB = 'pub';
  case _VAR = 'var';
  case _CONST = 'const';
  case _FN = 'fn';
  case _INTERFACE = 'interface';
  case _SCHEMA = 'schema';
  case _USE = 'use';
  case _RETURN = 'return';
  case _CONTINUE = 'continue';
  case _BREAK = 'break';
  case _IF = 'if';
  case _ELSE_IF = 'elif';
  case _ELSE = 'else';
  case _FOR = 'for';
  case _MATCH = 'match';
  case _SUB = 'sub';
  case _OBJECT = 'object';
  case _ENUM = 'enum';
  case _CATCH = 'catch';
  case _RECOVER = 'recover';
  case _IMPLEMENTS = 'implements';
  case _SELF = 'self';
  case _MODULE = 'module';
}


const ONE_OF = "one_of";
const MULTIPLE = "multiple";
const OPTIONAL = "optional";

class Token {

  public function __construct(
    public TokenType $type,
    public string    $value,
    public int       $line,
    public int       $column
  ) {
  }

  public function __toString(): string {
    if ($this->type === TokenType::NEW_LINE) {
      return "(NEW_LINE:$this->line-$this->column)";
    }
    $_type = $this->type->value;
    if ($this->type === TokenType::STRING_LITERAL_DOUBLE) {
      $value = "\"" . str_replace("\n", "\\n", $this->value) . "\"";
    } else if ($this->type === TokenType::STRING_LITERAL_SINGLE) {
      $value = '\'' . str_replace("\n", "\\n", $this->value) . '\'';
    } else if ($this->type === TokenType::COMMENT) {
      $value = $this->value;
    } else if ($this->type === TokenType::MULTILINE_COMMENT) {
      $value = str_replace("\n", "\\n", $this->value);
    } else {
      $value = $this->value;
    }
    return "($_type|$value:$this->line-$this->column)";
  }

}


abstract class Statement {
  public int $start_index;
  public int $end_index;
  public StatementType $type; # enum
  /** @var array<Statement> */
  public array $statements;
  public int $line;
  public int $column;
  public string $value = "";

  /**
   * @param array<Token> $tokens
   * @param int $index
   * @return static|null
   */
  static function parse(array $tokens, int $index): ?static {

    # todo
    # implement the parsing of the syntax here
    return null;

  }

  /**
   * Each statement has a type signature, wich is a string
   * that can be used to compare the type of two statements
   * for compatibility.
   */
  abstract public function get_type_signature(): string;

  /**
   * Based on the type of statement, the type checker, checks that my
   * type is compatible with the type of my sub-statements.
   */
  abstract public function type_check();

  abstract public function to_js();

  public function to_php() { }

}