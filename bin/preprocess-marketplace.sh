for file in outletpro.php src/index.ts; do
	echo "Processing: $file"

	sed -E \
		's@^([[:space:]]*)//[[:space:]]*(#(ifdef|endif).*)$@\1\2@' \
		"$file" |
		unifdef -t -ULICENSE -UUPDATES > "$file.tmp"

	mv "$file.tmp" "$file"
done

echo "Removing license/update files..."

grep -rEl --include='*.php' '@subpackage (License|Updates)' includes |
while IFS= read -r file; do
	echo "Deleting: $file"
	rm "$file"
done
