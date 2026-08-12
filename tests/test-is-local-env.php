<?php
/**
 * Tests for is_local_env().
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\is_local_env;

class Test_Is_Local_Env extends WP_UnitTestCase {

	public function test_returns_true_for_localhost(): void {
		// Arrange.
		update_option( 'home', 'http://localhost:8888/wordpress' );

		// Act.
		$result = is_local_env();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_localhost_tld(): void {
		// Arrange.
		update_option( 'home', 'https://shop.localhost' );

		// Act.
		$result = is_local_env();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_ipv4_loopback(): void {
		// Arrange.
		update_option( 'home', 'http://127.0.0.1' );

		// Act.
		$result = is_local_env();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_ipv6_loopback(): void {
		// Arrange.
		update_option( 'home', 'http://[::1]:8080' );

		// Act.
		$result = is_local_env();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_local_tld(): void {
		// Arrange.
		update_option( 'home', 'https://foo.local' );

		// Act.
		$result = is_local_env();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_case_test_tld(): void {
		// Arrange.
		update_option( 'home', 'https://foo.test' );

		// Act.
		$result = is_local_env();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_case_insensitive(): void {
		// Arrange.
		update_option( 'home', 'http://LOCALHOST' );

		// Act.
		$result = is_local_env();

		// Assert.
		$this->assertTrue( $result );
	}
}
