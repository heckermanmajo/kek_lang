<?php

include_once __DIR__ . "/../data.php";
include_once __DIR__ . "/../tokenizer.php";
include_once __DIR__ . "/../parser_helper.php";



/**
 * @param array<Token> $tokens
 * @throws SyntaxError
 */
function parse_function(array $tokens, int &$index):FunctionNode {



}


if (count(debug_backtrace()) == 0) {

  echo "All tests passed☑️\n";
}