#!/usr/bin/env bash

if [[ ! -d ~/git/hardware/ ]]; then
	echo " >> No '/git/hardware/' directory found." >&2
	exit 1
fi

dirname="$(realpath ./)"
cd ~/git/
tree -d --charset=UTF-8 --nolinks --noreport -o "$dirname/main.txt" -- hardware/
tree -d --charset=UTF-8 --nolinks --noreport -o "$dirname/main.xml" -X -- hardware/
tree -d --charset=UTF-8 --nolinks --noreport -o "$dirname/main.json" -J -- hardware/

