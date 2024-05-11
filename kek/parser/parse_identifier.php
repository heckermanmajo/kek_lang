<?php

/**
 * @param array<Token> $tokens
 * @param int $index
 * @return AstNode
 */
function parse_identifier(array &$tokens, int &$index, bool $can_be_type = false): AstNode {

  $token = $tokens[$index];
  $node = new IdentifierNode(tokens:[$token], children:[]);
  $node->is_all_uppercase = ctype_upper($token->value[0]);
  $node->starts_with_uppercase = ctype_upper($token->value[0]);

  # check that value is not a reserved keyword
  if (is_keyword($token->value, ignore_types: $can_be_type)) {
    throw new SyntaxError("Identifier cannot be a reserved keyword: $token");
  }

  $index++;
  return $node;
}



if (count(debug_backtrace()) == 0){
  $type_expression = "moin";
  $tokens = tokenize($type_expression, verbose: false);

  $index = 0;
  $node = parse_identifier($tokens, $index);
  #var_dump($node);

  $node->print_as_tree();

  try{
    $index = 0;
    $type_expression = "fn";
    $tokens = tokenize($type_expression, verbose: false);
    $node = parse_identifier($tokens, $index);
    #var_dump($node);
  } catch (SyntaxError $e) {
    #echo "GOT EXPECTED ERROR: \n";
    #echo $e->getMessage() ."\n";
    #echo "LOL";
  } catch (Exception $e) {
    echo "GOT UNEXPECTED ERROR: \n";
    echo $e->getMessage() ."\n";
    throw new Exception("Test failed");
  }

  echo "All tests passed☑️\n";

}



