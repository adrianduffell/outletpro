#!/bin/sh

for file in outletpro.php src/index.ts; do
	sed -E \
		's@^([[:space:]]*)//[[:space:]]*(#(ifdef|endif).*)$@\1\2@' \
		"$file" |
		unifdef -t -ULICENSE -UUPDATES > "$file.tmp"

	mv "$file.tmp" "$file"
done

# Delete License and Updates subpackage files.
grep -rEl --include='*.php' '@subpackage (License|Updates)' includes |
while IFS= read -r file; do
	rm "$file"
done

# Strip Update URI plugin header.
sed '/^[[:space:]]*\* Update URI:/d' outletpro.php > outletpro.php.tmp &&
	mv outletpro.php.tmp outletpro.php
