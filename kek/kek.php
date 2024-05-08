<?php

# read file

# tokenize files

# parse file

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