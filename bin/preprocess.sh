#!/bin/sh

# Copyright 2026 Adrian Duffell
# Licensed under the GNU General Public License v2.0 or later.

for file in outletpro.php src/index.ts; do
	sed -E \
		's@^([[:space:]]*)//[[:space:]]*(#(ifdef|endif).*)$@\1\2@' \
		"$file" |
		unifdef -t -DLICENSE -DUPDATES > "$file.tmp"

	mv "$file.tmp" "$file"
done
