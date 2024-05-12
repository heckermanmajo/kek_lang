#!/bin/bash

test_folder="./test/scope"
all_test_files=()
for file in $test_folder/*.kek; do
    all_test_files+=($file)
done

# shellcheck disable=SC2068
for file in ${all_test_files[@]}; do
    echo "Running test $file"
    ./kek.php ast $file && echo "Test $file passed" || exit 1
done