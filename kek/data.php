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

  case DOLLAR = 'DOLLAR';

  case AT_SIGN = 'AT_SIGN';

  case DOUBLE_AT_SIGN = 'DOUBLE_AT_SIGN';

  case QUESTION_MARK = 'QUESTION_MARK';

  case EXCLAMATION_MARK = 'EXCLAMATION_MARK';

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
    ":" => TokenType::COLON,
    "=" => TokenType::EQUALS,
    "," => TokenType::COMMA,
    "(" => TokenType::OPEN_PAREN,
    ")" => TokenType::CLOSE_PAREN,
    "{" => TokenType::OPEN_BRACE,
    "}" => TokenType::CLOSE_BRACE,
    "[" => TokenType::OPEN_BRACKET,
    "]" => TokenType::CLOSE_BRACKET,
    "->" => TokenType::ARROW,
    "::" => TokenType::DOUBLE_COLON,
    "." => TokenType::DOT,
    ";" => TokenType::SEMICOLON,
    "$" => TokenType::DOLLAR,
    "@" => TokenType::AT_SIGN,
    "@@" => TokenType::DOUBLE_AT_SIGN,
    "?" => TokenType::QUESTION_MARK,
    "!" => TokenType::EXCLAMATION_MARK,
  ];
  return $signs;
}
enum StatementType: string {
  case Literal = 'Literal';
}

enum Keywords: string {
  case _PUB = 'pub';
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
  #case _MATCH = 'match';
  case _SUB = 'sub';
  case _MAP = 'map';
  case _LIST = 'list';
  case _ENUM = 'enum';
  case _CATCH = 'catch';
  case _RECOVER = 'recover';
  case _IMPLEMENTS = 'implements';
  case _SELF = 'self';
}


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