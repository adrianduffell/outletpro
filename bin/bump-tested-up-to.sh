#!/bin/sh

version="$1"

if [ -z "$version" ]; then
	echo "Usage: $0 <wordpress-version>"
	exit 1
fi

sed -i.bak -E \
	"s/^([[:space:]]*\*[[:space:]]*Tested up to:).*/\1 $version/" \
	outletpro.php

rm outletpro.php.bak
