#!/usr/bin/env php
<?php

############################################
# KEK-TRANSPILER
############################################

include_once __DIR__ . "/data.php";
include_once __DIR__ . "/tokenizer.php";

include_once __DIR__ . "/parser/parse_block.php";
include_once __DIR__ . "/parser/parse_call_argument_list.php";
include_once __DIR__ . "/parser/parse_const.php";
include_once __DIR__ . "/parser/parse_control_flow.php";
include_once __DIR__ . "/parser/parse_expression_term.php";
include_once __DIR__ . "/parser/parse_identifier.php";
include_once __DIR__ . "/parser/parse_scope.php";
include_once __DIR__ . "/parser/parse_set.php";
include_once __DIR__ . "/parser/parse_type_expression.php";
include_once __DIR__ . "/parser/parse_value_literal.php";

$help = <<<HELP

KEK V1.0.0
KEK transpiler-usage

⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣀⣠⣤⣤⡶⠾⠿⠿⠛⠻⠿⠿⠶⢶⣤⣤⣀⡀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢀⣤⣶⠿⠛⠉⠉⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠉⠙⠻⢷⣶⣄⡀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⢀⣤⡾⠟⠉⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠈⠙⠿⣶⣄⠀⠀⢀⣠⣶⡶⢶⣦⣄⡀⠀⠀
⠀⠀⠀⠀⠀⠀⢀⣴⡿⠋⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠈⠻⢷⣤⣿⠏⠀⠀⠀⠈⠙⢿⣦⠀
⠀⠀⠀⠀⠀⣴⡿⠋⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢀⣀⣀⣀⡀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠹⣿⣄⠀⣠⡶⢶⣄⠀⢹⣇
⠀⠀⠀⢀⣾⠟⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣠⣶⠿⠛⠉⠙⠛⢷⣦⣀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠈⢿⣦⠛⠀⠀⣿⠀⠈⣿
⠀⠀⢠⣿⠋⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢰⡿⠃⠀⠀⠀⠀⠀⠀⠈⠻⣷⡄⠀⠀⠀⠀⠀⠀⠀⠀⠀⠈⢻⣧⣀⣼⠟⠀⢸⡟
⠀⢠⣾⠃⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣿⡇⠁⠀⠀⠀⠀⠀⠀⠀⠀⠈⢿⣆⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢻⣯⠁⠀⣠⣿⠁
⠀⣼⣿⣿⠇⠀⠀⠀⠀⠀⠀⠀⣿⣿⣿⣿⠂⠀⠀⠀⣿⡇⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠈⢻⣆⠀⠀⠀⠀⠀⠀⠀⠀⠀⠈⣿⣶⡾⠟⠁⠀
⢠⣿⣄⡀⠀⠀⠀⠀⠀⠀⠀⢀⣠⣤⡤⢤⣤⣄⠀⠀⢹⣧⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠈⣿⡄⠀⠀⠀⠀⠀⠀⠀⠀⠀⠸⣷⠀⠀⠀⠀
⢸⣿⡭⣷⡀⠀⠀⠀⠀⢀⣶⢟⣭⣴⣶⣶⣬⣝⢷⣄⠀⢿⣧⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢹⡇⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣿⡆⠀⠀⠀
⢸⣿⣿⢸⡇⠀⠀⠀⠀⣿⢃⣾⣿⣿⣿⣿⣿⣿⣆⢻⡆⠀⠻⣷⣄⠀⠀⠀⠀⠀⠀⠀⠀⠀⣾⡇⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣿⡇⠀⠀⠀
⢸⣿⣿⢸⡇⠀⠀⠀⠀⣿⢸⣿⣿⣿⣿⣿⣿⣿⣿⢸⡷⠀⠀⠉⠻⣿⣦⣀⡀⠄⠀⠀⣠⣾⠟⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣿⡇⠀⠀⠀
⢸⣿⣿⣼⣷⣶⣶⣶⣾⣿⡄⢻⣿⣿⣿⣿⣿⣿⡟⣸⡇⠀⠀⠀⠀⠀⠉⠛⠻⠿⠿⠟⠛⠁⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣿⠃⠀⠀⠀
⢻⣷⣾⣿⣗⠙⣿⠋⠀⠈⠻⣦⣍⣛⣛⣛⣛⣩⣴⠟⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢀⣿⠀⠀⠀⠀
⠸⣷⣿⠳⠿⣸⣿⠀⠀⠀⠀⠈⠻⣿⡛⠛⠛⠉⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣸⡇⠀⠀⠀⠀
⠀⣿⡏⠀⣰⣿⢿⡄⠀⠀⠀⠀⠀⠘⣿⡆⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢀⣿⠁⠀⠀⠀⠀
⠀⣿⡇⣂⣿⡘⢘⣿⡄⠀⠀⠀⢀⢠⣜⣿⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣠⣾⢾⠃⠀⠀⠀⠀⠀⣸⡏⠀⠀⠀⠀⠀
⠀⠹⣿⣿⣿⡟⡛⠛⣿⣦⣀⣀⣈⣻⣿⠏⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣠⡿⠃⠁⠀⠀⠀⠀⠀⠀⣰⡿⠀⠀⠀⠀⠀⠀
⠀⠀⠹⣷⡙⠿⠷⣦⣬⣿⠟⠛⠛⠋⠁⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣿⠁⠀⠀⠀⠀⠀⠀⠀⣰⡿⠁⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠘⠿⣦⣄⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢿⣦⡀⠀⠀⠀⠀⠀⣼⡟⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠈⠛⠿⣶⣤⣀⣀⠀⠀⠀⠀⠀⠀⣶⡀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠙⣻⣿⡆⠀⠀⠈⣿⡆⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⢹⣿⡙⢻⡿⢷⣶⣶⣤⣄⣿⣇⠀⠀⢠⣶⠀⠀⠀⠀⣀⣀⣀⣤⣶⡾⣿⠋⠹⣷⡀⠀⢠⣿⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠙⠿⠿⠃⠀⠀⠀⠈⠉⠙⣿⡄⠀⣸⡟⠛⠛⠛⠛⠛⠛⠉⠙⠿⠾⠋⠀⠀⢻⣧⠀⣸⠏⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠙⢿⣴⡿⠀⠂⠀⠀⠀⠐⠠⠠⠄⠯⠋⠁⠐⠄⠀⠙⠛⠛⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀

php kek.php <command> <filename>

Commands:
  ast <filename> - returns the ast of the file as overview
  astfull <filename> - returns the ast of the file, with all needed data

\n\n
HELP;


# cmd Args
$args = $argv;
$command = $args[1] ?? "";
$filename = $args[2] ?? "";

if (count($args) < 3) {
  echo $help;
  exit;
}

if ($command === "ast") {
  echo "Parsing file: $filename\n";
} else {
  echo $help;
  exit;
}

echo "Read file $filename\n";
# read file
$file = file_get_contents($filename);
echo "CONTENT:\n\n$file";
#echo "\n\nFile read\n";

# tokenize files
#echo "Tokenizing file\n";
$tokens = tokenize($file);
#echo "File tokenized\n";

# parse file
#echo "Parsing file\n";
$index = 0;
$ast = parse_scope($tokens, $index);

try {
  if ($command === "ast") {
    echo "\n\nAST:\n\n";
    $ast->print_as_tree();
  }
}catch (Exception $e) {
  echo "Error: " . $e->getMessage();
  echo "\n\n";
  echo $e->getTraceAsString();
  exit(1);
}

# name-resolution

# type & RULE -checking
# applies ALL THE RULES that are not syntax rules
# a.b.c = 5 -> this stays allowed
# a = a.lol().kek() -> this is allowed
# a().lol = 5 -> this is not allowed
# but we can parse this here by hand
# NO expression term on the left side of the assignment
# this allows for better control over passing around references

# transpile to end-language (php, js, (c, go))
# generics and traits will always be resolved into working simple structs
# local and const is also relevant in the checker phase
# all instances are passed via ref