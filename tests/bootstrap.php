<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
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

	// Load Outlet Pro.
	require_once __DIR__ . '/../outletpro.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require "{$_tests_dir}/includes/bootstrap.php";

// WP_Customize_Manager and its dependencies are not loaded by default in the test environment.
require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';

// WooCommerce meta box functions are not loaded by default in the test environment, but are needed for some tests.
require_once WP_PLUGIN_DIR . '/woocommerce/includes/admin/wc-meta-box-functions.php';

// WooCommerce test helpers.
require_once dirname( __DIR__ ) . '/vendor/class-wc-helper-product.php';

// Outlet Pro mocks.
require_once __DIR__ . '/mock-http-rest-api-response.php';
