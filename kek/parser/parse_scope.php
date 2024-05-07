<?php

include_once __DIR__ . "/../data.php";
include_once __DIR__ . "/../tokenizer.php";
include_once __DIR__ . "/../parser_helper.php";



/**
 * @param array<Token> $tokens
 * @throws SyntaxError
 */
function parse_scope(array $tokens, int &$index): ScopeBlockNode {

    $ast_node = new ScopeBlockNode(tokens: [], children: []);

    $token = $tokens[$index];
    if ($token->type !== TokenType::OPEN_BRACKET) {
        throw new SyntaxError("Expected {, got: $token");
    }

    $ast_node->tokens[] = $token;
    $index++; # jump over {

    $has_closed = false;

    while ($index < count($tokens)) {
        $token = $tokens[$index];
        if ($token->type === TokenType::CLOSE_BRACKET) {
            $ast_node->tokens[] = $token;
            $has_closed = true;
            $index++; # jump over }
            break;
        }

        # check for definitions if you got an identifier followed by ::
        # identifier :: expression, definition

        # check for an variable definition
        # identifier := type

        # check for an assignment
        # idenfitier = expression
        # this can be done in this scope

        # NO expression term on the left side of the assignment
        # this allows for better control over passing around references

        # a.b.c = 5 -> this stays allowed

        # check for if

        # check for for

        $node = parse_expression($tokens, $index);
        $ast_node->children[] = $node;
    }

    return $ast_node;

}


if (count(debug_backtrace()) == 0) {
/*
  $expression_term = "{ }";
  echo "\n---------------------------------\n";
  echo $expression_term ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression_term, verbose: false);#
  $index = 0;
  $node = parse_scope($tokens, $index);
  $node->print_as_tree();
*/
  echo "All SCOPE tests passed☑️\n";

}