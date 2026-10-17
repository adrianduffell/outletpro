<?php
/**
 * Content for the three category tiles pattern.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

defined( 'ABSPATH' ) || exit;

$column_count        = 3;
$tablet_column_count = 3;

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The shared template escapes its block markup.
echo require __DIR__ . '/outlet-category-tiles-pattern.php';
