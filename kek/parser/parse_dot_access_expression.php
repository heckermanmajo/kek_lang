<?php

include_once __DIR__ . "/../data.php";
include_once __DIR__ . "/../tokenizer.php";
include_once __DIR__ . "/../parser_helper.php";
include_once __DIR__ . "/parse_identifier.php";

# self.db_connection.save, lol.lol
function parse_dot_access_expression(array $tokens, int &$index): DotAccessExpressionNode {
  # left to right

  $dot_access_ast_node = new DotAccessExpressionNode( tokens: [], children: [
  ]);

  # a.b.c.d.e.f.g.h.i.j.k.l.m.n.o.p.q.r.s.t.u.v.w.x.y.z
  while(true){
    # we look for an identifier followed by a dot
    # until we find something that is not an identifier followed by a dot
    $identifier_ast_node = parse_identifier($tokens, $index);
    $dot_access_ast_node->children[] = $identifier_ast_node;
    $next_one_is_dot = count($tokens) > $index && $tokens[$index]->type === TokenType::DOT;
    if(!$next_one_is_dot) {
      # if so we break
      break;
    }else{
      # if so we skip the dot
      $index++;
    }
  }

  return $dot_access_ast_node;

}


if (count(debug_backtrace()) == 0) {

  $type_expression = "a.b.c";
  $tokens = tokenize($type_expression, verbose: false);

  $index = 0;
  $node = parse_dot_access_expression($tokens, $index);

  $node->print_as_tree();

  echo "All tests passed☑️\n";
}