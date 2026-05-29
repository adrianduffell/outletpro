#!/bin/bash
set -e

HEADER_VERSION=$(awk '/^[ \t]*\*[ \t]*Version:/{gsub(/\r/, "", $NF); print $NF; exit}' outletpro.php)
CONST_VERSION=$(awk '/const VERSION =/{gsub(/['"'"'";]/, "", $NF); print $NF; exit}' outletpro.php)
echo "Header version:   $HEADER_VERSION"
echo "Constant version: $CONST_VERSION"
if [ -z "$HEADER_VERSION" ] || [ -z "$CONST_VERSION" ]; then
  echo "Error: Could not extract one or both version strings from outletpro.php."
  exit 1
fi
if [ "$HEADER_VERSION" != "$CONST_VERSION" ]; then
  echo "Error: Plugin header version ($HEADER_VERSION) does not match VERSION constant ($CONST_VERSION)."
  exit 1
fi
echo "Versions match."
