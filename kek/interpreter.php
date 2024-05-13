<?php

class KekStatement{
  # for, if, return, set, define :: or :=
  # call
}

class KekExpression{
  # call (translate all operators into calls), identifier, literal
}

#scope is also a block/scope
enum KekTypeType{
  case KekStruct;
  case KekEnum;
  case KekBlock;
  case KekTrait;
  case KekRules;
}

# scope == type
class KekType{
  public KekTypeType $type;
  public array $functions = [];
  public array $fields = [];
}

class KekFunction{}

class KekField{
  public bool $is_static;
}