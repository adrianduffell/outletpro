<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Clearance_Section_For_WooCommerce
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Forward custom PHPUnit Polyfills configuration to PHPUnit bootstrap file.
$_phpunit_polyfills_path = getenv( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' );
if ( false !== $_phpunit_polyfills_path ) {
	define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_phpunit_polyfills_path );
}

if ( ! file_exists( "{$_tests_dir}/includes/functions.php" ) ) {
	echo "Could not find {$_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once "{$_tests_dir}/includes/functions.php";



/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin(): void {
	// Load WooCommerce plugin dependency.
	require_once WP_PLUGIN_DIR . '/woocommerce/woocommerce.php';

	// Load composer dependencies.
	require_once __DIR__ . '/../vendor/autoload.php';

	// Load includes.
	require_once __DIR__ . '/../includes/system-status.php';
	require_once __DIR__ . '/../includes/taxonomies.php';
	require_once __DIR__ . '/../includes/admin-product-options.php';
	require_once __DIR__ . '/../includes/shortcodes.php';
	require_once __DIR__ . '/../includes/page.php';
	require_once __DIR__ . '/../includes/tools.php';
	require_once __DIR__ . '/../includes/setup-task.php';
	require_once __DIR__ . '/../includes/onboarding-notice.php';
	require_once __DIR__ . '/../wc-clearance.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require "{$_tests_dir}/includes/bootstrap.php";

// WooCommerce test helpers.
require_once dirname( __DIR__ ) . '/vendor/class-wc-helper-product.php';
