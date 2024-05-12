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

  case SEMICOLON = 'SEMICOLON';

  case DOLLAR = 'DOLLAR';

  case AT_SIGN = 'AT_SIGN';

  case DOUBLE_AT_SIGN = 'DOUBLE_AT_SIGN';

  case UNARY_OPERATOR = 'UNARY_OPERATOR';
  case BINARY_OPERATOR = 'BINARY_OPERATOR';
  case UNARY_BINARY_OPERATOR = 'UNARY_BINARY_OPERATOR';

  case VAR_ASSIGNMENT = 'VAR_ASSIGNMENT';

  case DOUBLE_DOT = 'DOUBLE_DOT';

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
    ".." => TokenType::DOUBLE_DOT,
    ":=" => TokenType::VAR_ASSIGNMENT,
    "." => TokenType::BINARY_OPERATOR,
    ";" => TokenType::SEMICOLON,
    "$" => TokenType::DOLLAR,
    "?" => TokenType::UNARY_OPERATOR,
    " not " => TokenType::UNARY_OPERATOR,
    " and " => TokenType::BINARY_OPERATOR,
    " or " => TokenType::BINARY_OPERATOR,
    "-" => TokenType::UNARY_BINARY_OPERATOR,
    "+" => TokenType::BINARY_OPERATOR,
    "*" => TokenType::BINARY_OPERATOR,
    "/" => TokenType::BINARY_OPERATOR,
    "%" => TokenType::BINARY_OPERATOR,
    "==" => TokenType::BINARY_OPERATOR,
    "!=" => TokenType::BINARY_OPERATOR,
    "**" => TokenType::BINARY_OPERATOR,
    "//" => TokenType::BINARY_OPERATOR,
    "<" => TokenType::BINARY_OPERATOR,
    ">" => TokenType::BINARY_OPERATOR,
    "<=" => TokenType::BINARY_OPERATOR,
    ">=" => TokenType::BINARY_OPERATOR,
    "!" => TokenType::UNARY_OPERATOR,
    "&" => TokenType::BINARY_OPERATOR,
    "|" => TokenType::BINARY_OPERATOR,
  ];
  return $signs;
}
enum StatementType: string {
  case Literal = 'Literal';
}

enum Keywords: string {
  case _PUB = 'pub';
  case _EXTERN = 'extern';
  case _CONST = 'const';
  case _FN = 'fn';
  case _STRUCT = 'struct';
  case _TRAIT = 'trait';
  case _RETURN = 'return';
  case _CONTINUE = 'continue';
  case _BREAK = 'break';
  case _IF = 'if';
  case _ELSE_IF = 'elif';
  case _ELSE = 'else';
  case _FOR = 'for';
  case _ENUM = 'enum';
  case _USE = 'use';
  case _SET = 'set';
  #case _SELF = 'self';
/*  case _LIST = 'List';
  case _MAP = 'Map';
  case _UNION = 'Union';
  case _OPTION = 'Option';
  case _TREE_NODE = 'TreeNode';
  case _QUEUE = 'Queue';*/
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

abstract class AstNode {
  public function __construct(
    /** @var array<Token> */
    public array $tokens,
    /** @var array<AstNode> */
    public array $children,
    public ?AstNode $generated_by = null,
    public ?AstNode $parent = null,
    public ?ExecutionContext $context = null
  ) {
  }

  public function extra_data(): string{
    return "";
  }

  public function print_as_tree(int $indent = 0){
    // display the ast node in nice s format
    $indent_str = str_repeat("   ", $indent);
    $token_value = "";
    if($this->tokens[0] ?? false){
      $token_value = $this->tokens[0]->value;
    }

    echo $indent_str . static::class. " : ". $token_value . $this->extra_data() . "\n";
    foreach($this->children as $child){
      if($child){
        $child->print_as_tree($indent + 1);
      }else{
        echo print_r($child, true);
      }
    }
  }

  # collect all defintions -> all variable names
  # collect rules
  # rules are per module, so the module node is the root of the analysis.
  # the use statements -> need will be parsed
  #abstract public function init_execution_context(): string;

  #abstract public function get_type(): string;

  #abstract public function render_js(): string;

  #abstract public function render_php(): string;

}

class ExecutionContext{

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

class ExpressionNode extends AstNode {
}

class CallArgumentListNode extends AstNode {
}

class DotAccessExpressionNode extends AstNode {
}

class ValueLiteralNode extends AstNode {
}

class StringLiteralNode extends AstNode {}

class NumberLiteralNode extends AstNode {}

class BoolLiteralNode extends AstNode {}



# TYPE STUFF
class TypeExpressionNode extends AstNode {
}

class TypeIdentifierNode extends AstNode {
  public bool $is_local = false;
  public bool $is_const = false;
  public bool $is_optional = false;

  public function extra_data(): string{
   $extra = "";
    if($this->is_local) {
      $extra .= " (local)";
    }
    if($this->is_const) {
      $extra .= " (const)";
    }
    if($this->is_optional) {
      $extra .= " (optional)";
    }
    return $extra;
  }
}

class TypeUnionNode extends AstNode {}

class TypeFunctionNode extends AstNode {}

class TypeReturnNode extends AstNode {}

class TypeArgumentListNode extends AstNode {}

class ReturnTypeNode extends AstNode {}

class ConstDefinitionNode extends AstNode {#

  public bool $extern = false;
  public bool $pub = false;

  public function extra_data(): string{
    $extra = "";
    if($this->extern) {
      $extra .= " (extern)";
    }
    if($this->pub) {
      $extra .= " (pub)";
    }
    return $extra;
  }

}

class IfNode extends AstNode {}

class ExpressionTermNode extends AstNode {}

class ScopeNode extends AstNode {}

class BlockNode extends AstNode {}

class FunctionNode extends AstNode {}

class ForNode extends AstNode {}

class ConstructionLiteralNode extends AstNode {}

class TypeConstructionNode extends AstNode {}

class AssignmentNode extends AstNode {}

class VariableDefinitionNode extends AstNode {}

class ElifNode extends AstNode {}

class ElseNode extends AstNode {}

class ReturnNode extends AstNode {}
class ConditionNode extends AstNode {}

class NameListNode extends AstNode {}

class FieldDefinitionNode extends AstNode {}

class TypeDefinitionNode extends AstNode {
  public string $type;
}

class UseNode extends AstNode {}

class VariableSettingNode extends AstNode {}

class SetNode extends AstNode {}

class UnionTypeNode extends AstNode {}