<?php
/**
 * REST API response test helper.
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

/**
 * Mock a JSON REST API response.
 *
 * @param string               $request_method Matching HTTP request method.
 * @param string               $endpoint Matching request URL.
 * @param array<string, mixed> $request_body Matching request body.
 * @param string $json Response JSON.
 * @param int    $response_code HTTP response code.
 */
function mock_http_rest_api_response( string $request_method, string $endpoint, array $request_body, string $json, int $response_code = 200 ): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
	add_filter(
		'pre_http_request',
		function ( $pre, $args, $url ) use ( $request_method, $endpoint, $request_body, $json, $response_code ) {
			if ( $endpoint !== $url ) {
				return $pre;
			}

			if ( ( $args['method'] ?? null ) !== $request_method ) {
				return $pre;
			}

			if ( ( $args['body'] ?? null ) !== $request_body ) {
				return $pre;
			}

			return array(
				'headers'  => array( 'Content-Type' => 'application/json' ),
				'body'     => $json,
				'response' => array(
					'code'    => $response_code,
					'message' => get_status_header_desc( $response_code ),
				),
				'cookies'  => array(),
				'filename' => null,
			);
		},
		10,
		3
	);
}
