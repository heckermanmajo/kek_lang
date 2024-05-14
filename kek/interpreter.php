<?php

#scope is also a block/scope
enum KekScopeType{
  case KekStruct;
  case KekEnum;
  case KekBlock;
  case KekTrait;
  case KekRules;
}

# scope == type
class KekScope{
  public KekScopeType $type;
  public array $functions = [];
  public array $fields = [];
  public array $calls = [];
}

class KekFunction{}


class KekCall{
  # operators are calls
  # add(a, b)
  # [
  #   create_scope, create_capture_scope, create_function_scope, create_type_scope
  # ]

}

/*
 function_scope
 return_scope ->{}
 bock_scope {}
 capture_block_scope (){}
 capture_return_scope ()->{}
 if_scope
 for_scope
 else_scope
 elif_scope
 trait_scope
 enum_scope
 struct_scope

 arg_list


 k :: foo.(12)
 Foo2 :: struct{a:Foo}
 Foo :: struct{
   a:Int

 }
 pub NameSpace2 :: struct{
    window :: 800
    my_func :: fn(){}
    a: Int
 }
 pub NameSpace :: struct{
     KekSpace :: struct{
        window :: 800
        my_func :: fn(){}
        a: Int
     }
    window :: 800
    my_func :: fn(){}
    a: Int
 }

 NameSpace :: import("path_to_file_with_NameSpace").NameSpace.KekSpace

 NameSpace.my_func()

 i := NameSpace.(12)

 a :: bar()

 bar :: fn(){}

 b :: a + 123

 # Type system:
 -> StructType
 -> EnumType
 -> TraitType


  struct (type_arguments, used_traits)


 */



$scope = [
  "create_function_scope",
  ["arg_def_list", ["field_definition"]],
  ["type_definitions", []], # each scope has all its type definitions at the top
  ["function_definitions", []],
  ["imported_names", []],
  ["statements", []]
];

function interpret_scope($scope){}


$statement = [
  ["§return_type", "None oder ?Player"],
  ["<function_name>",
    [] #  argument list
  ], #  built in functions with a § - prefix
];

/*

§get_by_name , "name"

*/

$fn_name = "§if";

class KekField{
  public bool $is_static;
}