<?php


function parse_const(
  array $tokens, int &$index
): ConstDefinitionNode {

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

    # this is for the usage of traits Player :: struct(T_Template_type)(Postion){}
    $next_token = $tokens[$index] ?? null;
    if ($next_token->type === TokenType::OPEN_PAREN) {
      $type_node->children[] = parse_call_argument_list($tokens, $index);
    }

    $body = parse_scope($tokens, $index);
    $type_node->children[] = $body;

    $const_definition_node->children[] = $type_node;
    return $const_definition_node;
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

    $next_token = $tokens[$index] ?? null;

    # we can have the case, that there is no open brace, since this function head
    # is a "abstract" function in a trait ...
    if ($next_token->type === TokenType::OPEN_BRACE) {
      $block = parse_scope($tokens, $index);
      $function_node->children[] = $block;
    }

    $const_definition_node->children[] = $function_node;
    return $const_definition_node;
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

  return $const_definition_node;

}