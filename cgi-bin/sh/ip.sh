#!/usr/bin/env bash
#
. cgi.sh

#
header "text/plain; charset=utf-8" "${#REMOTE_ADDR}" "yes"
echo -n "${REMOTE_ADDR}"

