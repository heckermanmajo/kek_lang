<?php


enum TokenType: string {
  case TYPE_KEYWORD = 'TYPE_KEYWORD';

  case BOOL_LITERAL = 'BOOL_LITERAL';

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
  case _STRUCT = 'struct';
  case _TRAIT = 'trait';
  case _USE = 'use';
  case _RETURN = 'return';
  case _CONTINUE = 'continue';
  case _BREAK = 'break';
  case _IF = 'if';
  case _ELSE_IF = 'elif';
  case _ELSE = 'else';
  case _FOR = 'for';
  case _ENUM = 'enum';
  case _IMPLEMENTS = 'implements';
  case _SELF = 'self';
  case _MODULE = 'module';
  case _RULESET = 'ruleset';
  case _ERROR = 'error';
  case _LIST = 'List';
  case _MAP = 'Map';
  case _UNION = 'Union';
  case _OPTION = 'Option';
  case _TREE_NODE = 'TreeNode';
  case _QUEUE = 'Queue';
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


enum AstNodeType{

  # Int, List(Int), UserDefinedType, List(UserDefinedType)
  # Int|nil   Int|String|List(Int)
  # DBResult|Error
  # *A
  # !*A
  # const List(Int)
  # fn(self)->List(Int)
  case TYPE_EXPRESSION;

  # string, number, boolean, nil
  case CONST_EXPRESSION;

  # function all, identifier
  case EXPRESSION;

  # use (String).<identifier-modulename>::exported_const
  case USE_MODULE;

  # ruleset(CONSTEXPRESSION)
  case RULESET;

  # for, if
  case CONTROL_FLOW;

  # break <number> -> can only breaks out of for
  # IMPORTANT: can only break loops in the same scope{}
  case BREAK_STATEMENT;

  # same as break, but continues the loop
  case CONTINUE_STATEMENT;

  # () -> Int {}, {}, (){}
  case SCOPE;

  # fn <scope>
  case FUNCTION_DEFINITION;

  # (a: Int = 1, b: Int = 323)
  case ARGUMENT_LIST_DEFINITION;

  # (self)   (self: <TypeExpression>)
  case METHOD_ARGUMENT_LIST_DEFINITION;

  # -> Int
  case RETURN_TYPE;

  # return <expression>
  case SCOPE_RETURN;

  # fn (self){}
  case METHOD_DEFINITION;

  # <identifier> :: <type_expression> = <const_expression>
  case CONST_MODULE_LEVEL_DEFINITION;

  # <identifier> :: <type_expression> = <expression>
  case CONST_FUNCTION_LEVEL_DEFINITION;

  # pub CONST_MODULE_LEVEL_DEFINITION
  case EXPORT;

  # struct{ use Trait \n implements Interface \n a: Int = 10 \n b: String = "Hello" }
  case STRUCT_DEFINITION;

  # trait { a: Int = 10 \n b: String = "Hello" }
  case TRAIT_DEFINITION;

  # interface { tostring: fn(self)->String }
  case INTERFACE_DEFINITION;

  # implements <interface_name>
  case IMPLEMENTS_INTERFACE;

  # use <trait_name>
  case USE_TRAIT;

  case IDENTIFIER;
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

class CompilerState {
  public function __construct(
    public array $pre_processor_functions
  ) {
  }
}

class TypeExpression extends AstNode {
}

class IdentifierNode extends AstNode {
  public bool $is_all_uppercase;
  public bool $starts_with_uppercase;
  public ?TypeExpression $type_expression;
}

function is_keyword(string $string, bool $ignore_types): bool{
  foreach (Keywords::cases() as $keyword) {
    if ($string === $keyword->value) {
      if($ignore_types){
        if(in_array($string, ["List", "Map", "Error", "Union", "Option", "Queue", "TreeNode"])){
          continue;
        }
      }
      return true;
    }
  }
  return false;
}