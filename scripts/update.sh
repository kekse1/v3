#!/usr/bin/env bash

realpath="$(realpath "$0")"
dirname="$(dirname "$realpath")"
status="$(realpath "$dirname/../status/")"
file="update.now"

if [[ ! -d "$status" ]]; then
	echo " >> The target directory '$status' doesn\'t exist [as directory]!" >&2
	exit 1
fi

now="$((`date +'%s%N'`/1000000))"
echo "$now"
echo "$now" >"$status/$file"

