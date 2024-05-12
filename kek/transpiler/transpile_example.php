<?php

$ast = [""];

function std_out($str){
  echo $str;
}

/**
 * Every scope needs to generate a scope id.
 * The need to be appended to the variable names.
 * The need to be a post-fix on the parent scope id.
 *
 *
 * Resolve ast function name function, that resolves
 * the name into the correct scope name.
 *
 *
 *
 */

/**
 * @return void
 */
function translate_expression(){
  $name = "kek_value";
  ?>

  $<?=$name?> = 1;

  <?php
}

$s1__s2__name_of_var = "kek_value";
# construction literals

# all structs are simple classes.
# all unions, options, lists are associative arrays
# using simple classes might be also an option ...
$s1__s2__name_of_var = [];

s1__s2__name_of_function(1, 2);

function s1__s2__name_of_function(
  $a,
  $b
){


  # comment for SCOPE_12

  # END OF SCOPE_12


}

# if == if

# for -> into while loop
$i = 0;
while(true){
  if($i > 10){
    break;
  }
  $scope_12__i = $i;
}


# const and var are the same



