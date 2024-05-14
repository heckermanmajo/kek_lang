<?php


enum KekNodeType {
  case KekFunctionScope;
  case KekStructScope;
  case KekEnumScope;
  case KekBlock;
  case KekReturnScope;
  case KekCaptureBlock;
  case KekFunction;
  case KekMethod;
  case KekFunctionCall;
  case KekMethodCall;
  case KekFieldAccess;
  case TopLevelScope;
  case KekIfNode;

  case KekElseNode;
  case KekElifNode;
  case KekForNode;
  case KekCondScope;

  case VariableDeclaration;
  case ConstantDeclaration;
}

enum KekTypeType {
  case KekStruct;
  case KekEnum;
  case KekMap;
  case KekList;
  case KekUnion;
  case KekOption;
}

class KekType {
  public function __construct(
    public KekTypeType $type,
    public string      $name,
    # if the type is struct or enum, we only expect one
    # node -> the strict or enum node
    # otherwise, we expect a list of types
    public array       $nodes = [],
  ) {
  }
}

class KekNode {

  public function __construct(
    public bool        $does_return,
    public KekNodeType $node_type,
    public array       $attributes = [],
    public array       $children = []
  ) {
  }
}

/*

a :: 123

 */

$node = new KekNode(
  does_return: false,
  node_type: KekNodeType::ConstantDeclaration,
  attributes: [
    "name" => "a",
    "local" => false
  ],
  children: []
);

$node = [
  "type" => "const_definition",
  "name" => "a",
  "local" => false,
  "constant" => true,
  "return_type" => null,
  "right_value" => [
    "type" => "number_literal",
    "value" => 123,
    "return_type" => "Int"
  ]
];


/*

foo :: fn(a: Int): Bool {
  return a > 123
}

*/

$node = [
  "type" => "function_definition",
  "name" => "foo",
  "arguments" => [
    [
      [
        "type" => "argument_definition",
        "name" => "a",
        "argument_type" => [
          "type" => "type_expression",
          "name" => "Int",
          "as_string" => "Int",
          "type_arguments" => []
        ]
      ],
    ]
  ],
  "return_type" => [
    "type" => "type_expression",
    "name" => "Bool",
    "as_string" => "Bool",
    "type_arguments" => []
  ],
  "body" => [
    [
      "type" => "return_statement",
      "return_type" => [
        "type" => "type_expression",
        "name" => "Bool",
        "as_string" => "Bool",
        "type_arguments" => []
      ],
      "expression" => [
        "type" => "operator",
        "operator" => ">",
        "left" => [
          "type" => "identifier",
          "name" => "a",
          "return_type" => [
            "type" => "type_expression",
            "name" => "Int",
            "as_string" => "Int",
            "type_arguments" => []
          ]
        ],
        "right" => [
          "type" => "number_literal",
          "value" => 123,
          "return_type" => [
            "type" => "type_expression",
            "name" => "Int",
            "as_string" => "Int",
            "type_arguments" => []
          ]
        ],
        "return_type" => [
          "type" => "type_expression",
          "name" => "Bool",
          "as_string" => "Bool",
          "type_arguments" => []
        ]
      ]
    ]
  ]
];


# each file gets a file name prefix
# js und c haben keine name spaces


function generate_main_file(){
  # put namespace around it
}

function generate_import(){
  ## all class names in the imported file are prefixed with an index
  ## import -> sets a name space to pass in all the imported names
  ## this can be a hash
  <hash>__TypeName
  <hash>__functionName
  <hash>__constantName
  ## now add the name space to the scope
  ## so we can update all names that call the imported onces
  ## with the prefix hash
  ## imports are therefore a normal translate file move, just before
  ## the rest of the code and all top level names (and calls) get a prefix
  ## this can be executed on the ast level
  ## parse the file and then append the prefix to all top level names and its uses
  ## then create a sope object for the newly imported ones
}

function generate_field(){}

function generate_struct(string $name, array $fields, array $methods){}

function generate_enum(array $key_value_pairs){

}

function generate_scope(){
  # scope does not need to be enforced
  # since we can just overwrite variable names
  # the usage restrictions are done on the ast level
  # we don't need to do anything with the scopes
}

function generate_if(){}

function generate_for(){}

function generate_variable_definition(){
  # ignore const in the generation
}

function generate_function_statement(){}

function generate_return_statement(string $content){
  return "return $content";
}

function generate_expression(): string {

  # method call
  # function call
  # field access
  # operator -> also a function call

  return "()";
}

function generate_method_call(){}
function generate_function_call(){} #  all operators are functions
function generate_field_access(){}


include_once();



