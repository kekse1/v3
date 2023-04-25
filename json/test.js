#!/usr/bin/env node.js

const map = require('./color.json');

for(const item of map)
{
	const hex = color.hex(item.value);
	dir(hex, item.name);
}

