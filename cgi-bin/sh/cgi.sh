#!/usr/bin/env bash

#
doNotCache="yes"
contentType="text/plain; charset=utf-8"
contentLength=""

#
yes()
{
	[[ "${1:0:1}" == "y" || "${1:0:1}" == "Y" ]] && return 0
	return 1
}

redirect()
{
	[[ -z "$1" ]] && return
	echo "Location: $1"
}

noCacheHeader()
{
	echo "Cache-Control: max-age=0, no-cache, no-store, must-revalidate"
	echo "Pragma \"no-cache\""
	echo "Expires: 0"
}

statusCode()
{
	[[ -z "$1" ]] && return
	echo "Status: $1"
}

finishHeader()
{
	echo
}

header()
{
	type="$contentType"
	length=""
	cache=""

	[[ -n "$1" ]] && type="$1"
	[[ -n "$2" ]] && length="$2"
	[[ -z "$length" ]] && length="$contentLength"
	[[ -n "$3" ]] && cache="$3"
	[[ -z "$cache" ]] && cache="$doNotCache"

	echo "Content-Type: $type"
	[[ -n "$length" ]] && echo "Content-Length: $length"

	yes "$cache"
	[[ $? -eq 0 ]] && noCacheHeader

	finishHeader
}

#
get()
{
	[[ -z "$QUERY_STRING" ]] && return 1

	IFS='&' read -ra params <<<"${QUERY_STRING}"

	for param in "${params[@]}"; do
		IFS='=' read -ra pair <<<"$param"

		if [[ -z "$1" ]]; then
			key="${pair[0]}"
			value="${pair[1]}"
			echo "['$key'] '$value'"
		elif [[ "$1" = "${pair[0]}" ]]; then
			echo "${pair[1]}"
		fi
	done
}

#

