<?php
/**
 * Test the update_plugin_hook function.
 *
 * @package OutletPro
 * @group update
 */

use function OutletPro\deinit_update_plugin;
use function OutletPro\init_settings;
use function OutletPro\init_update_plugin;
use const OutletPro\LICENSE_KEY_OPTION;

class Test_Update_Plugin_Hook extends WP_UnitTestCase {

	public function test_returns_false_when_license_key_is_missing(): void {
		// Arrange.
		deinit_update_plugin();
		init_update_plugin();
		init_settings();
		delete_option( LICENSE_KEY_OPTION );

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
		deinit_update_plugin();
		init_update_plugin();
		init_settings();
		$previous = array(
			'slug'    => 'foo',
			'version' => '1.0.0',
		);
		update_option( LICENSE_KEY_OPTION, 'abc123' );

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
		deinit_update_plugin();
		init_update_plugin();
		init_settings();
		update_option( LICENSE_KEY_OPTION, 'abc123' );

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
		deinit_update_plugin();
		init_update_plugin();
		init_settings();
		update_option( LICENSE_KEY_OPTION, 'abc123' );

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
		deinit_update_plugin();
		init_update_plugin();
		init_settings();
		update_option( LICENSE_KEY_OPTION, 'abc123' );

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

	//phpcs:ignore
	public function tear_down(): void { //phpcs:ignore
		if ( ! $this->hasFailed() ) {
			$log_dir = WC_LOG_DIR;
			foreach ( glob( $log_dir . '*.log' ) as $file ) {
				fwrite( //phpcs:ignore
					STDERR,
					"\n===== {$file} =====\n" . file_get_contents( $file ) . "\n" //phpcs:ignore
				);
			}
		}
		parent::tear_down();
	}
}
