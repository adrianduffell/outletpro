#!/bin/sh

# Copyright 2026 Adrian Duffell
# Licensed under the GNU General Public License v2.0 or later.

version="$1"

if [ -z "$version" ]; then
	echo "Usage: $0 <wordpress-version>"
	exit 1
fi

sed -i.bak -E \
	"s/^(Tested up to:).*/\1 $version/" \
	readme.txt

rm readme.txt.bak
