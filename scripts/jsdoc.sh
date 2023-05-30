#!/usr/bin/env bash

exit 255

#
real="$(realpath "$0")"
dir="$(dirname "$real")"
js="$(realpath "$dir/js/")"
out="$(realpath "$dir/api/")"

#
#cd "$js"
argv="--destination='$api' --encoding=utf8 --recurse"

