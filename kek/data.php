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

$OPERATORS = [
    "+",
    "-",
    "*",
    "/",
    "%",
    "==",
    "!=",
    "**",
];

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

}


/**
 * Special function will not be translated into
 * normal function calls, but will trigger
 * special behavior in the transpiler.
 */
$SPECIAL_FUNCTIONS = [
  "import"
];

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

  public string $full_scope_name = "";

  public function __construct(
    /** @var array<Token> */
    public array $tokens,
    /** @var array<AstNode> */
    public array $children,
    public ?AstNode $generated_by = null,
    public ?AstNode $parent = null,
    public ?ScopeNode $parent_scope = null
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

  function init_nodes_recursively(?AstNode $parent = null): void {

    $this->parent = $parent;
    # this allows to check, f.e. if a tyoe expression is followed by a function call, etc.
    $this->init_extra_data();

    # register my name into the parent scope

    foreach($this->children as $child){
      $child->init_nodes_recursively($this);
    }

  }

  function init_extra_data(){}

  /**
   * Go up as lond we have not hitted a scope
   * @return void
   */
  function set_parent_scopes_recursively(): void {
    if($this->parent){
      $some_parent = $this->parent;
      while($some_parent){
        if($some_parent instanceof ScopeNode){
          $this->parent_scope = $some_parent;
          break;
        }
        $some_parent = $some_parent->parent;
      }
    }
    $this->set_my_scope_name();

    foreach($this->children as $child){
      $child->set_parent_scopes_recursively();
    }
  }
  function set_my_scope_name(){

  }

  function get_scope_name(): string {
    // look into the parent scope to get the scope name of me
  }

  function generate_scope_id (): string {
    return "".uniqid();
  }

  /**
   * Evaluate the nodes to php code.
   *
   * @param TranspilerState $state
   * @return string
   */
  function evaluate(TranspilerState $state): string {

  }

}

class SyntaxError extends Exception { }

/**
 * Contains the state of the transpiler.
 *
 * -> all names, scopes etc. are registered here
 *    and can be accessed by the transpiler.
 */
class TranspilerState {
  public function __construct() {
  }
}

class TypeExpression extends AstNode {

  /**
   * This function returns the type expression
   * as a string, so we can compare types.
   *
   * @return string
   */
  function get_type_as_string(): string {
    return "";
  }

  function init_extra_data(){
    # todo: determine if this is used as a name space
  }

}

class IdentifierNode extends AstNode {
  public bool $is_all_uppercase;
  public bool $starts_with_uppercase;

  /**
   * This function returns the type expression
   * as a string, so we can compare types.
   *
   * @return string
   */
  function get_type_as_string(): string {
    return "";
  }

}

class ExpressionNode extends AstNode {

  /**
   * This function returns the type expression
   * as a string, so we can compare types.
   *
   * @return string
   */
  function get_type_as_string(): string {
    return "";
  }

}

class CallArgumentListNode extends AstNode {
}

class ValueLiteralNode extends AstNode {
}

class StringLiteralNode extends AstNode {}

class NumberLiteralNode extends AstNode {}

class BoolLiteralNode extends AstNode {}

class TypeExpressionNode extends AstNode {}

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

class TypeArgumentListNode extends AstNode {}

class ReturnTypeNode extends AstNode {}

class ConstDefinitionNode extends AstNode {

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

  function generate_php_code(): string {
    ob_start();
    $name = $this->children[0]->tokens[0]->value;
    ?>
    $scope["<?=$name?>"] = <?= $this->children[1]->generate_php_code() ?>
    <?php
    return ob_get_clean();
  }

}

class IfNode extends AstNode {}

class ExpressionTermNode extends AstNode {

  # we have access operators
  # math operator

  # we might want to convert all operators into functions
  # add() multiply() etc.
  # inverse()

}

class ScopeNode extends AstNode {}

class BlockNode extends AstNode {}

class FunctionNode extends AstNode {}

class ForNode extends AstNode {}

class ConstructionLiteralNode extends AstNode {}

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

class SetNode extends AstNode {}

class UnionTypeNode extends AstNode {}