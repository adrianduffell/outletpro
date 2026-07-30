#!/bin/sh

for file in outletpro.php src/index.ts; do
	sed -E \
		's@^([[:space:]]*)//[[:space:]]*(#(ifdef|endif).*)$@\1\2@' \
		"$file" |
		unifdef -t -DLICENSE -DUPDATES > "$file.tmp"

	mv "$file.tmp" "$file"
done
