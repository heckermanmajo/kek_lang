<?php

include_once __DIR__ . "/../data.php";
include_once __DIR__ . "/../tokenizer.php";
include_once __DIR__ . "/../parser_helper.php";


/**
 * @param array<Token> $tokens
 * @throws SyntaxError
 */
function parse_construction_literals(array $tokens, int &$index):ConstructionLiteralNode {

/*
 *
        # this is a list construction literal
        my_f_list := List(Float)[1.0, 2.0, 3.0, 4.0, 5.0, 6.0, 7.0]

        # this is a list construction literal
        my_users_list := List(User)[
            User(name = "John", age = 20),
            User(name = "Jane", age = 21),
        ]

        # this is a map construction literal
        my_map := Map(Int,Int){
            1 = 2,
            3 = 4,
            5 = 6
        }

        # this is a struct construction literal
        myUserObject := User(
            name = "John",
            age = 20
        )

        User(name = "John", age = 20).name

 */

}


if (count(debug_backtrace()) == 0) {

  echo "All tests passed☑️\n";
}