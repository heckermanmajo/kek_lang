<?php

include_once __DIR__ . "/../data.php";
include_once __DIR__ . "/../tokenizer.php";
include_once __DIR__ . "/../parser_helper.php";
include_once __DIR__ . "/parse_identifier.php";
include_once __DIR__ . "/parse_call_argument_list.php";
include_once __DIR__ . "/parse_dot_access_expression.php";
include_once __DIR__ . "/parse_value_literal.php";


/**
 * @throws SyntaxError
 */
function parse_expression(array $tokens, int &$index): ExpressionNode {
  $ast_expression_node = new ExpressionNode(tokens:[], children:[

  ]);

  # is this token an identifier?
  if ($tokens[$index]->type === TokenType::IDENTIFIER) {

    # if the token after the identifier is a dot, then this is a member access
    if(count($tokens) > $index + 1 && $tokens[$index + 1]->type === TokenType::DOT) {
      $node = parse_dot_access_expression($tokens, $index);
      $ast_expression_node->children[] = $node;

      # does now follow a function call?
      if(count($tokens) > $index && $tokens[$index]->type === TokenType::OPEN_PAREN) {
        $node = parse_call_argument_list($tokens, $index);
        $ast_expression_node->children[] = $node;
      }

      return $ast_expression_node;

    }

    # if the token after the identifier is an open parenthesis, then this is a function call
    if(count($tokens) > $index + 1 && $tokens[$index + 1]->type === TokenType::OPEN_PAREN) {

      # consume the identifier
      $node = parse_identifier($tokens, $index);
      $ast_expression_node->children[] = $node;

      # consume the open parenthesis -> function call argument list
      $node = parse_call_argument_list($tokens, $index);
      $ast_expression_node->children[] = $node;

      return $ast_expression_node;
    }

    # else it is a simple identifier

    $node = parse_identifier($tokens, $index);
    $ast_expression_node->children[] = $node;

    return $ast_expression_node;

  }

  # check for literals, etc.
  $node = parse_value_literal($tokens, $index);
  $ast_expression_node->children[] = $node;

  return $ast_expression_node;
}


if (count(debug_backtrace()) == 0) {



  $expression = "moin";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);
  $index = 0;
  $node = parse_expression($tokens, $index);




  $expression = "a.b.moin(kek,lol)";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);
  $index = 0;
  $node = parse_expression($tokens, $index);
  $node->print_as_tree();




  $expression = "a.b.moin(kek.QWERT,lol.FOO)";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);
  $index = 0;
  $node = parse_expression($tokens, $index);
  $node->print_as_tree();



  $expression = "moin(kek())";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);
  $index = 0;
  $node = parse_expression($tokens, $index);
  $node->print_as_tree();



  #echo "---------------------------------";
  $expression = "a.b.moin(kek.QWERT,lol.FOO.FUCK.YOU(LÖPPT))";
  $expression = "a.b.moin(kek.QWERT())";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_expression($tokens, $index);
  $node->print_as_tree();



  $expression = "a.b.moin(kek.QWERT(),)";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_expression($tokens, $index);
  $node->print_as_tree();



  $expression = "a.b.moin(kek.QWERT(), lol)";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_expression($tokens, $index);
  $node->print_as_tree();


  $expression = "a.b.moin(kek.QWERT(), lol, lol, lol)";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_expression($tokens, $index);
  $node->print_as_tree();


  $expression = "a.b.moin(kek.QWERT(), lol.foo, lol, lol)";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_expression($tokens, $index);
  $node->print_as_tree();


  $expression = "a.b.moin(kek.QWERT(), lol(), lol, lol)";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_expression($tokens, $index);
  $node->print_as_tree();


  $expression = "a.b.moin(kek.QWERT(), lol, lol, lol())";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_expression($tokens, $index);
  $node->print_as_tree();



  $expression = "a.b.moin(kek.QWERT(), lol, lol, lol.foo(lol))";
  $expression = "a.b.moin(lol.foo(lol))";
  $expression = "a.b.moin(foo(lol))";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_expression($tokens, $index);
  $node->print_as_tree();

  $expression = "a.b.moin(kek.QWERT(), lol, lol, lol.foo(lol))";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_expression($tokens, $index);
  $node->print_as_tree();

  $expression = "a.b.moin(foo(lol(bar.kek(123))))";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_expression($tokens, $index);
  $node->print_as_tree();

  $expression = "a.b.moin(foo(lol(bar.kek('123'))))";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_expression($tokens, $index);
  $node->print_as_tree();


  $expression = "a.b.moin(foo(lol(bar.kek(true, false,))))";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_expression($tokens, $index);
  $node->print_as_tree();

  $expression = "\"WURST\"";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_expression($tokens, $index);
  $node->print_as_tree();


  $expression = " a + b ( ) ";
  echo "\n---------------------------------\n";
  echo $expression ."\n";
  echo "---------------------------------\n";
  $tokens = tokenize($expression, verbose: false);#
  $index = 0;
  $node = parse_expression_term($tokens, $index);
  $node->print_as_tree();



  echo "\n---------------------------------\n";
  echo "All EXPRESSION tests passed☑️\n";
  echo "---------------------------------\n";
}