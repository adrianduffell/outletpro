#!/bin/sh

# Copyright 2026 Adrian Duffell
# Licensed under the GNU General Public License v2.0 or later.

version="$1"

if [ -z "$version" ]; then
	echo "Usage: $0 <woocommerce-version>"
	exit 1
fi

sed -i.bak -E \
	"s/^([[:space:]]*\*[[:space:]]*WC requires at least:).*/\1 $version/" \
	outletpro.php

rm outletpro.php.bak
