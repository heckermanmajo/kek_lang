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

    if ($token->type === TokenType::IDENTIFIER) {

      $token_ahead = $tokens[$index+1] ?? null;

      #####################################################
      # CONST DEFINITION
      ######################################################

      if (
        $token_ahead
        && (
          # starts with extern -> this is the type def for a run time lib type
          ($token_ahead->type == TokenType::KEYWORD
            && $token_ahead->value == "extern")
          ||
          # what is exported on themodule level
          ($token_ahead->type == TokenType::KEYWORD
            && $token_ahead->value == "pub")
          # simple const definition of sth.
          || $token_ahead->type == TokenType::DOUBLE_COLON
        )
      ) {


        $const_definition_node = new ConstDefinitionNode(
          tokens: [],
          children: []
        );

        $token = $tokens[$index];
        if ($token->type == TokenType::KEYWORD && $token->value == "extern") {
          $const_definition_node->extern = true;
          $index++; # jump over extern-keyword
        }

        if ($token->type == TokenType::KEYWORD && $token->value == "pub") {
          $const_definition_node->pub = true;
          $index++; # jump over pub-keyword
        }

        $identifier = parse_identifier($tokens, $index);
        $const_definition_node->children[] = $identifier;
        $index++; # jump over ::

        $next_token = $tokens[$index] ?? null;

        # can be type definition (trait, struct, enum)
        if (
          $next_token
          && $next_token->type === TokenType::KEYWORD
          && (
            $next_token->value === "trait"
            || $next_token->value === "struct"
            || $next_token->value === "enum"
          )
        ) {

          $type_node = new TypeDefinitionNode(
            tokens: [],
            children: []
          );

          if ($next_token->value === "trait") {
            $type_node->type = "trait";
          }
          if ($next_token->value === "struct") {
            $type_node->type = "struct";
          }
          if ($next_token->value === "enum") {
            $type_node->type = "enum";
          }
          $index++;

          # This is the call list after the type definition keyword
          # This allows for generic types struct(T){}
          $next_token = $tokens[$index] ?? null;
          if ($next_token->type === TokenType::OPEN_PAREN) {
            $type_node->children[] = parse_call_argument_list($tokens, $index);
          }

          $body = parse_scope($tokens, $index);
          $type_node->children[] = $body;

          $const_definition_node->children[] = $type_node;
          $ast_node->children[] = $const_definition_node;
          continue;
        }

        # can be function definition (fn)
        if (
          $next_token
          && $next_token->type === TokenType::KEYWORD
          && $next_token->value === "fn"
        ) {

          $function_node = new FunctionNode(
            tokens: [],
            children: []
          );

          $index++;

          $next_token = $tokens[$index] ?? null;
          $is_open_paren = $next_token->type === TokenType::OPEN_PAREN;
          if (!$is_open_paren) {
            throw new SyntaxError("Expected open parenthesis for function definition, got: $next_token");
          }
          $index++;

          while (true) {

            $next_token = $tokens[$index] ?? null;

            if ($next_token->type === TokenType::CLOSE_PAREN) {
              $index++;
              break;
            }

            if ($next_token->type === TokenType::IDENTIFIER) {
              $next_next_token = $tokens[$index + 1] ?? null;
              if ($next_next_token && $next_next_token->type === TokenType::COLON) {
                $field_type_definition_node = new FieldDefinitionNode(
                  tokens: [],
                  children: []
                );

                $field_type_definition_node->children[] = parse_identifier($tokens, $index);

                $index++; # jump over :

                $field_type_definition_node->children[] = parse_type_expression($tokens, $index);

                # todo: here we can parse a default value

                $function_node->children[] = $field_type_definition_node;

              } else {
                throw new SyntaxError("Expected colon for function definition, got: $next_token");
              }
            } else {
              throw new SyntaxError("Expected identifier for function definition, got: $next_token");
            }
          }

          $next_token = $tokens[$index] ?? null;
          if ($next_token->type === TokenType::ARROW) {
            $index++;
            $type_expression = parse_type_expression($tokens, $index);
            $function_node->children[] = $type_expression;
          }

          $block = parse_scope($tokens, $index);
          $function_node->children[] = $block;

          $const_definition_node->children[] = $function_node;
          $ast_node->children[] = $const_definition_node;

          continue;
        }

        # can be expression OR Block
        if (
          $next_token->type === TokenType::OPEN_PAREN # (
          || $next_token->type === TokenType::OPEN_BRACE  # {
          || $next_token->type === TokenType::ARROW
        ) {
          $const_definition_node->children[] = parse_block($tokens, $index);
        } else {
          $expression_term = parse_expression_term($tokens, $index);
          $const_definition_node->children[] = $expression_term;
        }
        $ast_node->children[] = $const_definition_node;
        continue;

      }


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

    }

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