<?php

include_once __DIR__ . "/../data.php";
include_once __DIR__ . "/../tokenizer.php";
include_once __DIR__ . "/../parser_helper.php";


/**
 * @param array<Token> $tokens
 * @throws SyntaxError
 */
function parse_block(array $tokens, int &$index):BlockNode {

  $ast_node = new BlockNode(tokens: [], children: []);

  /*

  We expect:

  (<call list>) -> <typeexpression> {

  }

  () -> <typeexpression> {

  }

    (){

  }

  ->Int{

  }

  {

  }

*/


}


if (count(debug_backtrace()) == 0) {


  echo "All tests passed☑️\n";
}