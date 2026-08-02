#!/bin/sh

# Copyright 2026 Adrian Duffell
# Licensed under the GNU General Public License v2.0 or later.

version="$1"

if [ -z "$version" ]; then
	echo "Usage: $0 <wordpress-version>"
	exit 1
fi

# Plugin header.
sed -i.bak -E \
	"s/^([[:space:]]*\*[[:space:]]*Requires at least:).*/\1 $version/" \
	outletpro.php
rm outletpro.php.bak

# readme.txt.
sed -i.bak -E \
	"s/^(Requires at least:).*/\1 $version/" \
	readme.txt
rm readme.txt.bak
