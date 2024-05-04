<?php

$EXPRESSION = [];

$SCHEMA = [];

$LITERAL = [
  [
    OPTIONAL,
    NumberLiteral::class,
    StringLiteral::class,
    BoolLiteral::class,
    ArrayLiteral::class,
    MapLiteral::class
  ]
];

$TYPE_EXPRESSION = [];


$DEFINITION = [
  [OPTIONAL, Keywords::_PUB],
  [OPTIONAL, Keywords::_VAR],
  TokenType::IDENTIFIER,
  TokenType::COLON,
  [OPTIONAL, &$TYPE_EXPRESSION],
  TokenType::EQUALS,
  [ONE_OF, &$EXPRESSION, &$SCHEMA]
];

$RETURN = [
  Keywords::_RETURN,
  &$EXPRESSION
];

$END_OF_SCOPE = [
  &$RETURN,
  Keywords::_BREAK,
  Keywords::_CONTINUE
];

# (x:Int)
$SCOPE_ARG_LIST = [
  TokenType::OPEN_PAREN,
  [OPTIONAL, TokenType::IDENTIFIER],
  TokenType::CLOSE_PAREN
];

# -><typeExpression>
$SCOPE_RETURN_TYPE = [
  TokenType::ARROW,
  &$TYPE_EXPRESSION
];

# x() -> Int { return 10 }
$SCOPE = [
  &$SCOPE_ARG_LIST,
  [OPTIONAL, $SCOPE_RETURN_TYPE],
  TokenType::OPEN_BRACE,
  [MULTIPLE, &$EXPRESSION],
  [&$END_OF_SCOPE],
  TokenType::CLOSE_BRACE
];

# fn x() -> Int { return 10 }
$FUNCTION = [
  Keywords::_FN,
  &$SCOPE
];

$CALL_ARG_LIST = [
  TokenType::OPEN_PAREN,
  [OPTIONAL, &$EXPRESSION],
  TokenType::CLOSE_PAREN
];

$FUNCTION_CALL = [
  TokenType::IDENTIFIER,
  &$CALL_ARG_LIST
];

$FIELD_DEFINITION = [
  TokenType::IDENTIFIER,
  TokenType::COLON,
  &$TYPE_EXPRESSION,
  [OPTIONAL, [TokenType::EQUALS, &$EXPRESSION]]
];


$SCHEMA[] =   Keywords::_SCHEMA;
$SCHEMA[] =   TokenType::CLOSE_BRACKET;
$SCHEMA[] =   TokenType::COLON;
$SCHEMA[] =   TokenType::IDENTIFIER;
$SCHEMA[] =   TokenType::OPEN_BRACE;
$SCHEMA[] =   [MULTIPLE, [Keywords::_USE, TokenType::IDENTIFIER]];
$SCHEMA[] =   [MULTIPLE, &$DEFINITION];
$SCHEMA[] =   TokenType::CLOSE_BRACE;
