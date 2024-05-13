<?php

# we need to extract "." operators into its own thing
# we need to resolve the type-definitions
# into some kind of refined nodes
# also manage the checking ...


function generate_php_code(AstNode $node): string {
  global $OPERATORS;

  $OPERATORS_ON_FUNCTIONS = [
    "+" => "add",
    "-" => "sub",
    "*" => "mul",
    "/" => "div",
    "%" => "mod",
    "==" => "eq",
    "!=" => "neq",
    "**" => "pow",
  ];

  ob_start();

  switch ($node::class) {

    case ScopeNode::class:
      ?>
      $LAST_DEFINED_SCOPE = function(array $parent_scope = []){
      $scope = ["___parent_scope" => $parent_scope];
      <?php
      foreach ($node->children as $child) {
        echo generate_php_code($child);
        echo "\n";
      }
      echo "\n";
      ?>
      }
      <?php
      break;


    case ConstDefinitionNode::class:
      $name = $node->children[0]->tokens[0]->value;
      $right = generate_php_code($node->children[1]);
      ?>
      $scope["<?= $name ?>"] = <?= $right ?>;
      <?php
      break;


    case ExpressionTermNode::class:

      if ($node->children[0]::class === ValueLiteralNode::class) {
        echo generate_php_code($node->children[0]);
      }

      elseif (
        $node->children[0]::class === IdentifierNode::class
        && count($node->children) === 1
      ) {
        echo generate_php_code($node->children[0]);
      }

      elseif ($node->children[1] && $node->children[1]::class === CallArgumentListNode::class) {
        echo generate_php_code($node->children[0]);
        echo generate_php_code($node->children[1]);
      }

      elseif (in_array($node->tokens[0]->value, $OPERATORS)){
        $operator_token  = $node->tokens[0];
        echo "operator('{$operator_token->value}', ";
        echo generate_php_code($node->children[0]);
        echo ", ";
        echo generate_php_code($node->children[1]);
        echo ")";
      }

      elseif($node->children[0] instanceof ExpressionTermNode){
        foreach ($node->children as $child) {
          if ($child){
            echo generate_php_code($child);
          }
        }
      }

      elseif ($node->children[0] instanceof TypeExpressionNode){
        # type expression is call to a static function
        # so, we need to get the name of the type as string
        $type_name = $node->children[0]->children[0]->tokens[0]->value;
        echo "$type_name";
      }

      else {
        echo "Not implemented yet for " . $node->children[0]::class . "\n";
      }

      if ($node->parent::class == ScopeNode::class) {
        echo ";";
      }

      break;

    case IdentifierNode::class:
      echo '$scope["' . $node->tokens[0]->value . '"]';
      break;

    case CallArgumentListNode::class:
      echo "(";
      foreach ($node->children as $child) {
        echo generate_php_code($child);
        echo ",";
      }
      echo ")";
      break;

    case ValueLiteralNode::class:
      if ($node->children[0]::class == NumberLiteralNode::class) {
        echo $node->children[0]->tokens[0]->value;
      } elseif ($node->children[0]::class == StringLiteralNode::class) {
        echo '"' . $node->children[0]->tokens[0]->value . '"';
      } else {
        echo "Not implemented yet for " . $node->children[0]::class . "\n";
      }
      break;

    case ReturnNode::class:
      echo "return ";
      if (count($node->children) > 0) {
        echo generate_php_code($node->children[0]);
      }
      echo ";";
      break;

    case IfNode::class:

      echo "if (\n";
      echo generate_php_code($node->children[0]);
      echo "\n) {\n";
      echo generate_php_code($node->children[1]);
      echo "\n}\n";

      $count = count($node->children);
      for($i = 2; $i < $count; $i++) {
        $child = $node->children[$i];
        echo generate_php_code($child);
      }

      break;

    case ConditionNode::class:
      echo generate_php_code($node->children[0]);
      break;

    case BlockNode::class:
      foreach ($node->children as $child) {
        echo generate_php_code($child);
        echo "\n";
      }
      echo '$LAST_DEFINED_SCOPE();\n';
      break;

    case ElseNode::class:
      echo "else {\n";
      echo generate_php_code($node->children[0]);
      echo "\n}\n";
      break;

    case ElifNode::class:
      echo "else if (\n";
      echo generate_php_code($node->children[0]);
      echo "\n) {\n";
      echo generate_php_code($node->children[1]);
      echo "\n}\n";
      break;

    case FunctionNode::class:
      echo 'function(';
      # assemble the argument lists
      $index = 0;
      $node = $node->children[$index];
      while($node instanceof FieldDefinitionNode){
        $name = $node->children[0]->tokens[0]->value;
        echo '$' . $name;
        echo ",";
        $index++;
        if ($index >= count($node->children)) {
          break;
        }
        $node = $node->children[$index];
      }
      echo ") {\n";
      if ($node instanceof ScopeNode) {
        echo generate_php_code($node);
      }
      echo "\n};\n";
      break;

    case TypeExpressionNode::class:
      echo "# ignore type expression\n";
      break;

    default:
      echo "Not implemented yet for " . $node::class . "\n";

  }
  return ob_get_clean();
}
