#!/bin/sh

for file in outletpro.php src/index.ts; do
	sed -E \
		's@^([[:space:]]*)//[[:space:]]*(#(ifdef|endif).*)$@\1\2@' \
		"$file" |
		unifdef -t -ULICENSE -UUPDATES > "$file.tmp"

	mv "$file.tmp" "$file"
done

grep -rEl --include='*.php' '@subpackage (License|Updates)' includes |
while IFS= read -r file; do
	rm "$file"
done
