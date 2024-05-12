<?php

/**
 * @param array<Token> $tokens
 * @throws SyntaxError
 */
function parse_scope(array $tokens, int &$index): ScopeNode {

  $ast_node = new ScopeNode(tokens: [], children: []);

  $token = $tokens[$index];
  if ($token->type !== TokenType::OPEN_BRACE) {
    throw new SyntaxError("Expected {, got: $token");
  }

  $ast_node->tokens[] = $token;
  $index++; # jump over {

  $has_closed = false;

  while ($index < count($tokens)) {

    $token = $tokens[$index];
    if ($token->type === TokenType::CLOSE_BRACE) {
      $ast_node->tokens[] = $token;
      $has_closed = true;
      $index++; # jump over }
      break;
    }

    if (
      $token->type === TokenType::OPEN_PAREN # (
      || $token->type === TokenType::OPEN_BRACE
      || $token->type === TokenType::ARROW
    ) {
      # a capturing block can only capture variable names
      # if we got (<name> ...){}
      # if we got (<name> ...)->Tyeexpression{}
      $block_node = parse_block($tokens, $index);
      $ast_node->children[] = $block_node;
      continue;

    }

    # ONLY constants can be extern or pub
    # starts with extern -> this is the type def for a run time lib type
    # simple const definition of sth.
    if (
      $token->type == TokenType::KEYWORD
      && $token->value == "extern"
      || $token->value == "pub"
    ){
      $ast_node->children[] = parse_const($tokens, $index);
      continue;
    }


    if ($token->type === TokenType::IDENTIFIER) {

      $token_ahead = $tokens[$index+1] ?? null;

      #####################################################
      # CONST DEFINITION
      ######################################################

      if ( $token_ahead && $token_ahead->type == TokenType::DOUBLE_COLON) {

        $ast_node->children[] = parse_const($tokens, $index);
        continue;

      } #  end of const definition


      #####################################################
      # FIELD TYPE DEFINITION
      ######################################################
      # check if the field is defined with a single token
      # f.e. my_field: Int
      # since this is a simple field type definition, we can parse it here

      if ($token_ahead && $token_ahead->type === TokenType::COLON) {
        $field_type_definition_node = new FieldDefinitionNode(
          tokens: [],
          children: []
        );

        $field_type_definition_node->children[] = parse_identifier($tokens, $index);

        $index++; # jump over :

        $field_type_definition_node->children[] = parse_type_expression($tokens, $index);

        # todo: here we can parse a default value

        $ast_node->children[] = $field_type_definition_node;
        continue;

      }


      #####################################################
      # VARIABLE DEFINITION
      ######################################################

      if ($token_ahead && $token_ahead->type === TokenType::VAR_ASSIGNMENT) {

        # check for an variable definition
        # identifier :=  returning scope -> parse capture-block, expression
        $identifier = parse_identifier($tokens, $index);
        $index++;  # jump over :=

        $variable_definition_node = new VariableDefinitionNode(
          tokens: [],
          children: [$identifier]
        );

        $token = $tokens[$index];

        # the followup to a variable definition can only be an expression OR a block
        if (
          $token->type === TokenType::OPEN_PAREN # (
          || $token->type === TokenType::OPEN_BRACE  # {
          || $token->type === TokenType::ARROW
        ) {
          $variable_definition_node->children[] = parse_block($tokens, $index);
        } else {
          $variable_definition_node->children[] = parse_expression_term($tokens, $index);
        }

        $ast_node->children[] = $variable_definition_node;
        continue;

      }

      # if we start with an identifier, we can have a function call
      # or a dotted assignment
      # so we parse the expression term
      # if it is followed by a = we have an assignment
      {
        $expression_term = parse_expression_term($tokens, $index);

        $next_token = $tokens[$index] ?? null;
        if ($next_token && $next_token->type === TokenType::EQUALS) {

          $expression_term_2 = parse_expression_term($tokens, $index);

          $assignment_node = new AssignmentNode(
            tokens: [$next_token],
            children: [
              $expression_term,
              $expression_term_2
            ]
          );
          $ast_node->children[] = $assignment_node;
          continue;
        }

        $ast_node->children[] = $expression_term;
        continue;
      }

    }

    # if we got a keyword, we can have an if, for, return
    if ($token->type === TokenType::KEYWORD) {

      if ($token->value === "if") {
        $ast_node->children[] = parse_control_flow($tokens, $index);
        continue;
      }

      if ($token->value === "for") {
        $ast_node->children[] = parse_control_flow($tokens, $index);
        continue;
      }

      if ($token->value === "return") {

        $return_node = new ReturnNode(
          tokens: [],
          children: []
        );

        $index++; # jump over return
        $return_node->children[] = parse_expression_term($tokens, $index);

        $ast_node->children[] = $return_node;
        continue;

      }


      if ($token->value === "set") {
        $ast_node->children[] = parse_set($tokens, $index);
        continue;
      }

    }

    # if we got here, you have not closed the scope
    throw new SyntaxError("Expected }, got: $token");

  }

  return $ast_node;

}
