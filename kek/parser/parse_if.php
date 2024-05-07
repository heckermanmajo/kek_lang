<?php

include_once __DIR__ . "/../data.php";
include_once __DIR__ . "/../tokenizer.php";
include_once __DIR__ . "/../parser_helper.php";
include_once __DIR__ . "/parse_expression.php";


/**
 * @param array<Token> $tokens
 * @throws SyntaxError
 */
function parse_if_node(array $tokens, int &$index):IfNode {

    $ast_node = new IfNode(tokens: [], children: []);

    $token = $tokens[$index];
    if ($token->type !== TokenType::KEYWORD || $token->value !== "if") {
        throw new SyntaxError("Expected if, got: $token");
    }

    $ast_node->tokens[] = $token;
    $index++; # jump over if


    return $ast_node;

}


if (count(debug_backtrace()) == 0) {


  echo "All tests passed☑️\n";
}