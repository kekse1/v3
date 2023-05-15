#!/usr/bin/env bash

if [[ ! -d ~/git/hardware/ ]]; then
	echo " >> No '/git/hardware/' directory found." >&2
	exit 1
fi

dir="$(realpath .)"
cd ~/git/
tree -d --charset=UTF-8 --nolinks --noreport -- hardware/ >$dir/main.txt
tree -d --charset=UTF-8 --nolinks --noreport -X -- hardware/ >$dir/main.xml
tree -d --charset=UTF-8 --nolinks --noreport -J -- hardware/ >$dir/main.json

