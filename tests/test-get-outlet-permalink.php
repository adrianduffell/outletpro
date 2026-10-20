<?php
/**
 * Test the outlet permalink helper.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_outlet_permalink;
use const OutletPro\OUTLET_PAGE_OPTION;

class Test_Get_Outlet_Permalink extends WP_UnitTestCase {

	public function test_returns_configured_page_permalink(): void {
		// Arrange.
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );

		// Act.
		$result = get_outlet_permalink();

		// Assert.
		$this->assertSame( get_permalink( $page_id ), $result );
	}

	public function test_returns_null_without_configured_page_even_with_current_post(): void {
		// Arrange.
		$page_id         = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$GLOBALS['post'] = get_post( $page_id );
		delete_option( OUTLET_PAGE_OPTION );

		// Act.
		$result = get_outlet_permalink();

		// Assert.
		$this->assertNull( $result );
	}

	public function test_throws_when_configured_page_was_deleted(): void {
		// Arrange.
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		wp_delete_post( $page_id, true );
		update_option( OUTLET_PAGE_OPTION, $page_id );

		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Outlet page permalink could not be retrieved.' );

		// Act.
		get_outlet_permalink();
	}

	public function test_throws_when_page_option_is_invalid(): void {
		// Arrange.
		update_option( OUTLET_PAGE_OPTION, 'invalid' );

		// Expect.
		$this->expectException( UnexpectedValueException::class );

		// Act.
		get_outlet_permalink();
	}
}
