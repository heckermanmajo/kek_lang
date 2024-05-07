#!/bin/bash

php parser/parse_identifier.php
php parser/parse_expression.php
php parser/parse_call_argument_list.php
php parser/parse_dot_access_expression.php
php parser/parse_type_expression.php
php parser/parse_const_definition.php
php parser/parse_expression_term.php