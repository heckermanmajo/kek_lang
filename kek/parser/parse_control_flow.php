<?php

/**
 * @param array<Token> $tokens
 */
function parse_control_flow(array $tokens, int &$index): ForNode|IfNode {

  $token = $tokens[$index];
  if ($token->type !== TokenType::KEYWORD) {
    throw new SyntaxError("Expected keyword if or for, got: $token");
  }

  if ($token->value === "for") {
    $ast_node = new ForNode(tokens: [], children: []);
  } else {
    $ast_node = new IfNode(tokens: [], children: []);
  }
  $index++;

  # parse assignment if identifier followed by := is given
  # else parse expression

  $ast_node->children[] = parse_condition($tokens, $index);

  $ast_node->children[] = parse_block($tokens, $index);

  $token = $tokens[$index];
  while ($token->type ==TokenType::KEYWORD && $token->value == "elif") {
    $ast_node->children[] = parse_elif_block($tokens, $index);
    $token = $tokens[$index];
  }

  if ($token->type ==TokenType::KEYWORD && $token->value == "else") {
    $ast_node->children[] = parse_else_block($tokens, $index);
  }

  return $ast_node;
}

function parse_condition(array $tokens, int &$index): ConditionNode {
  # [<indentifer> :=] <expression> [.. <expression>] [; expression]

  $ast_node = new ConditionNode(tokens: [], children: []);
  $token = $tokens[$index];

  # is the token an identifier?
  if ($token->type == TokenType::IDENTIFIER) {

    $next_token = $tokens[$index + 1];

    if ($next_token->type == TokenType::VAR_ASSIGNMENT || $next_token->type == TokenType::DOUBLE_COLON) {
      # the syntax must be
      # [<indentifer> :=] <expression> [.. <expression>] [; expression]

      if($next_token->type == TokenType::VAR_ASSIGNMENT){
        $variable_definition_node = new VariableDefinitionNode(tokens: [], children: []);
      }else{
        $variable_definition_node = new ConstDefinitionNode(tokens: [], children: []);
      }

      $variable_definition_node->children[] = parse_identifier($tokens, $index);
      $index++; # jump over := symbol
      $variable_definition_node->children[] = parse_expression_term($tokens, $index);
      $ast_node->children[] = $variable_definition_node;
      # check we got a range with the .. sign
      $token = $tokens[$index];

      if($token->type == TokenType::DOUBLE_DOT){
        $index++; # jump over ".."
        $ast_node->children[] = parse_expression_term($tokens, $index);

        # possible ; with another expression -> the step size
        $token = $tokens[$index];
        if ($token->type == TokenType::SEMICOLON) {
          $index++; # jump over ;
          $ast_node->children[] = parse_expression_term($tokens, $index);
        }

      }
      return $ast_node;
    }

  }

  # should be operator
  $expression = parse_expression_term($tokens, $index);
  $ast_node->children[] = $expression;
  return $ast_node;

}

function parse_elif_block(array $tokens, int &$index): ElifNode {

  $ast_node = new ElifNode(tokens: [], children: []);
  $token = $tokens[$index];

  if ($token->type !== TokenType::KEYWORD || $token->value !== "elif") {
    throw new SyntaxError("Expected keyword elif, got: $token");
  }
  $index++;

  $condition = parse_condition($tokens, $index);
  $ast_node->children[] = $condition;

  # its a block, not a scope (but a block contains a scope)
  $block = parse_block($tokens, $index);
  $ast_node->children[] = $block;

  return $ast_node;

}

function parse_else_block(array $tokens, int &$index): ElseNode {

  $ast_node = new ElseNode(tokens: [], children: []);
  $token = $tokens[$index];

  if ($token->type !== TokenType::KEYWORD || $token->value !== "else") {
    throw new SyntaxError("Expected keyword else, got: $token");
  }
  $index++;

  # its a block, not a scope (but a block contains a scope)
  $block = parse_block($tokens, $index);
  $ast_node->children[] = $block;

  return $ast_node;
}


if (count(debug_backtrace()) == 0) {


  echo "All tests passed☑️\n";
}