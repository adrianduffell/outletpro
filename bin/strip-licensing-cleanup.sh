#!/usr/bin/env bash
#
# Post-Rector cleanup for the Woo Marketplace build.
#
# Deletes PHP and TypeScript files that cannot be transformed by Rector
# (because the entire file is being removed) and removes the welcome-page
# import from src/index.ts.
#
# Called automatically by: composer run strip-licensing
# (runs after `vendor/bin/rector process --config bin/strip-licensing.php`)

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PLUGIN_DIR="$(dirname "$SCRIPT_DIR")"

echo "[strip-licensing] Removing includes/license.php..."
rm -f "$PLUGIN_DIR/includes/license.php"

echo "[strip-licensing] Removing src/welcome-page/ directory..."
rm -rf "$PLUGIN_DIR/src/welcome-page"

echo "[strip-licensing] Removing licensing-related test files..."
rm -f "$PLUGIN_DIR/tests/test-validate-license.php"
rm -f "$PLUGIN_DIR/tests/test-has-license.php"
rm -f "$PLUGIN_DIR/tests/test-add-plugin-action-links-hook.php"
rm -f "$PLUGIN_DIR/tests/test-render-license-page.php"
rm -f "$PLUGIN_DIR/tests/test-register-license-key-setting.php"
rm -f "$PLUGIN_DIR/tests/test-init-admin-menu-hook.php"
rm -f "$PLUGIN_DIR/tests/test-render-welcome-page.php"

echo "[strip-licensing] Removing welcome-page import from src/index.ts..."
sed -i "/^import '.\/welcome-page';$/d" "$PLUGIN_DIR/src/index.ts"

echo "[strip-licensing] Done."
