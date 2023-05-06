#!/usr/bin/env bash
dirname="$(realpath ./)"
cd ~/git/
tree -d --charset=UTF-8 --nolinks --noreport -o "$dirname/main.txt" -- hardware/
tree -d --charset=UTF-8 --nolinks --noreport -o "$dirname/main.xml" -X -- hardware/
tree -d --charset=UTF-8 --nolinks --noreport -o "$dirname/main.json" -J -- hardware/
