#!/usr/bin/env bash

if [[ ! -d ~/git/hardware/ ]]; then
	echo " >> No '/git/hardware/' directory found." >&2
	exit 1
fi

dirname="$(realpath ./)"
cd ~/git/
tree -d --charset=UTF-8 --nolinks --noreport -- hardware/ >"$dirname/main.txt"
tree -d --charset=UTF-8 --nolinks --noreport -X -- hardware/ >"$dirname/main.xml"
tree -d --charset=UTF-8 --nolinks --noreport -J -- hardware/ >"$dirname/main.json"

