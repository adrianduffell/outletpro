<?php
/**
 * Test the update_plugin_hook function.
 *
 * @package OutletPro
 * @group updates
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_update_plugin;
use function OutletPro\init_settings;
use function OutletPro\init_update_plugin;
use const OutletPro\LICENSE_ACTIVATION_OPTION;
use const OutletPro\LICENSE_KEY_OPTION;

class Test_Update_Plugin_Hook extends WP_UnitTestCase {

	/**
	 * Mocks the license server response.
	 *
	 * @param bool $success Whether the license validation succeeds or fails.
	 */
	private function mock_license_server_response( bool $success ): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) use ( $success ) {
				if ( strpos( $url, 'https://api.lemonsqueezy.com/v1/licenses/validate' ) !== false ) {
					return array(
						'headers'  => array(),
						'body'     => wp_json_encode(
							array(
								'valid' => $success,
								'meta'  => array(
									'product_id' => 1279790,
								),
							)
						),
						'response' => array(
							'code'    => 200,
							'message' => 'OK',
						),
						'cookies'  => array(),
						'filename' => null,
					);
				}

				return $pre;
			},
			10,
			3
		);
	}

	private function mock_license_server_downtime(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) {
				if ( strpos( $url, 'https://api.lemonsqueezy.com/v1/licenses/validate' ) !== false ) {
					return new WP_Error(
						'http_request_failed',
						'Simulated HTTP failure'
					);
				}
				return $pre;
			},
			10,
			3
		);
	}

	public function test_returns_false_when_license_activation_is_missing(): void {
		// Arrange.
		deinit_update_plugin();
		init_update_plugin();
		init_settings();
		delete_option( LICENSE_ACTIVATION_OPTION );

		// Act.
		$result = apply_filters(
			'update_plugins_adrianduffell.store', //phpcs:ignore WordPress.NamingConventions.ValidHookName
			false,
			array( 'UpdateURI' => 'https://adrianduffell.store/outletpro' ),
		);

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_returns_previous_update_when_plugin_does_not_match(): void {
		// Arrange.
		$this->mock_license_server_response( true );
		deinit_update_plugin();
		init_update_plugin();
		init_settings();
		$previous = array(
			'slug'    => 'foo',
			'version' => '1.0.0',
		);

		// Act.
		$result = apply_filters(
			'update_plugins_adrianduffell.store', //phpcs:ignore WordPress.NamingConventions.ValidHookName
			$previous,
			array( 'UpdateURI' => 'https://adrianduffell.store/foo-plugin' ),
		);

		// Assert.
		$this->assertSame( $previous, $result );
	}

	public function test_returns_false_when_remote_request_fails(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		$this->mock_license_server_response( true );
		deinit_update_plugin();
		init_update_plugin();
		init_settings();
		update_option( 'blogname', 'Foo' );
		update_option( LICENSE_KEY_OPTION, 'abc123' );
		update_option( LICENSE_ACTIVATION_OPTION, array( 'abc123', 'activation-id', 'Foo' ) );

		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) {
				if ( strpos( $url, 'v1/outletpro/updates' ) !== false ) {
					return array(
						'headers'  => array(),
						'body'     => '',
						'response' => array(
							'code'    => 401,
							'message' => 'Unauthorized',
						),
						'cookies'  => array(),
						'filename' => null,
					);
				}

				return $pre;
			},
			10,
			3
		);

		// Act.
		$result = apply_filters(
			'update_plugins_adrianduffell.store', //phpcs:ignore WordPress.NamingConventions.ValidHookName
			false,
			array( 'UpdateURI' => 'https://adrianduffell.store/outletpro' ),
		);

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_returns_previous_value_when_response_is_invalid(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		$this->mock_license_server_response( true );
		deinit_update_plugin();
		init_update_plugin();
		init_settings();
		update_option( 'blogname', 'Foo' );
		update_option( LICENSE_KEY_OPTION, 'abc123' );
		update_option( LICENSE_ACTIVATION_OPTION, array( 'abc123', 'activation-id', 'Foo' ) );

		$previous = false;
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) {
				if ( strpos( $url, 'v1/outletpro/updates' ) !== false ) {
					return array(
						'headers'  => array(),
						'body'     => '',
						'response' => array(
							'code'    => 200,
							'message' => 'OK',
						),
						'cookies'  => array(),
						'filename' => null,
					);
				}

				return $pre;
			},
			10,
			3
		);

		// Act.
		$result = apply_filters(
			'update_plugins_adrianduffell.store', //phpcs:ignore WordPress.NamingConventions.ValidHookName
			$previous,
			array( 'UpdateURI' => 'https://adrianduffell.store/outletpro' ),
		);

		// Assert.
		$this->assertSame( $previous, $result );
	}

	public function test_returns_update_when_new_version_is_available(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		$this->mock_license_server_response( true );
		deinit_update_plugin();
		init_update_plugin();
		init_settings();
		update_option( 'blogname', 'Foo' );
		update_option( LICENSE_KEY_OPTION, 'abc123' );
		update_option( LICENSE_ACTIVATION_OPTION, array( 'abc123', 'activation-id', 'Foo' ) );

		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) {
				if ( strpos( $url, 'v1/outletpro/updates' ) !== false ) {
					return array(
						'headers'  => array(),
						'body'     => wp_json_encode(
							array(
								'version' => '1.0.1',
								'url'     => 'https://example.com',
								'package' => 'https://example.com/outletpro.zip',
							)
						),
						'response' => array(
							'code'    => 200,
							'message' => 'OK',
						),
						'cookies'  => array(),
						'filename' => null,

					);
				}
				return $pre;
			},
			10,
			3
		);

		// Act.
		$result = apply_filters(
			'update_plugins_adrianduffell.store', //phpcs:ignore WordPress.NamingConventions.ValidHookName
			false,
			array( 'UpdateURI' => 'https://adrianduffell.store/outletpro' ),
		);

		// Assert.
		$this->assertIsArray( $result );
		$this->assertSame( 'outletpro', $result['slug'] );
		$this->assertSame( '1.0.1', $result['version'] );
		$this->assertSame(
			'https://example.com/outletpro.zip',
			$result['package']
		);
	}

	public function test_gracefully_handles_exception_when_checking_license(): void {
		// Arrange.
		$this->mock_license_server_downtime();
		update_option( 'blogname', 'Foo' );
		update_option( LICENSE_ACTIVATION_OPTION, array( 'valid-license', 'activation-id', 'Foo' ) );

		// Act.
		$result = apply_filters(
			'update_plugins_adrianduffell.store', //phpcs:ignore WordPress.NamingConventions.ValidHookName
			'my-previous-value',
			array( 'UpdateURI' => 'https://adrianduffell.store/outletpro' ),
		);

		// Assert.
		$this->assertSame( 'my-previous-value', $result );
	}
}
