<?php
/**
 * Tests for mock_http_rest_api_response().
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

/**
 * Tests for mock_http_rest_api_response().
 */
class Test_Mock_Http_Rest_Api_Response extends WP_UnitTestCase {

	public function test_mocks_http_rest_api_response(): void {
		// Arrange.
		$json = '{"valid":true}';
		mock_http_rest_api_response( 'POST', 'https://example.com/path/to/endpoint', $json, 201 );

		// Act.
		$response = wp_remote_post( 'https://example.com/path/to/endpoint' );

		// Assert.
		$this->assertSame( 201, wp_remote_retrieve_response_code( $response ) );
		$this->assertSame( 'application/json', wp_remote_retrieve_header( $response, 'Content-Type' ) );
		$this->assertSame( $json, wp_remote_retrieve_body( $response ) );
	}

	public function test_does_not_override_response_for_another_endpoint(): void {
		// Arrange.
		mock_http_rest_api_response( 'POST', 'https://test.invalid/path/to/endpoint', '{"endpoint":"test.invalid"}' );
		mock_http_rest_api_response( 'POST', 'https://example.com/path/to/endpoint', '{"endpoint":"example.com"}' );

		// Act.
		$response = wp_remote_post( 'https://test.invalid/path/to/endpoint' );

		// Assert.
		$this->assertSame( '{"endpoint":"test.invalid"}', wp_remote_retrieve_body( $response ) );
	}

	public function test_does_not_mock_another_request_method(): void {
		// Arrange.
		mock_http_rest_api_response( 'POST', 'https://example.com/path/to/endpoint', '{"valid":true}' );

		// Act.
		$response = apply_filters(
			'pre_http_request',
			array( 'body' => '{"foo":"bar"}' ),
			array( 'method' => 'GET' ),
			'https://example.com/path/to/endpoint'
		);

		// Assert.
		$this->assertSame( array( 'body' => '{"foo":"bar"}' ), $response );
	}
}
