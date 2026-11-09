<?php
/**
 * Tests for get_outlet_page_title().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_outlet_page_title;
use const OutletPro\OUTLET_PAGE_OPTION;

class Test_Get_Outlet_Page_Title extends WP_UnitTestCase {

	public function test_returns_the_current_outlet_page_title(): void {
		// Arrange.
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Outlet',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );
		wp_update_post(
			array(
				'ID'         => $page_id,
				'post_title' => 'Last Chance & Final Sale',
			)
		);

		// Act.
		$result = get_outlet_page_title();

		// Assert.
		$this->assertSame( 'Last Chance &amp; Final Sale', $result );
	}

	public function test_returns_null_without_a_configured_page(): void {
		// Arrange.
		delete_option( OUTLET_PAGE_OPTION );

		// Act.
		$result = get_outlet_page_title();

		// Assert.
		$this->assertNull( $result );
	}

	public function test_returns_null_when_the_configured_page_was_deleted(): void {
		// Arrange.
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		wp_delete_post( $page_id, true );

		// Act.
		$result = get_outlet_page_title();

		// Assert.
		$this->assertNull( $result );
	}

	public function test_returns_null_when_the_configured_post_is_not_a_page(): void {
		// Arrange.
		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
		update_option( OUTLET_PAGE_OPTION, $post_id );

		// Act.
		$result = get_outlet_page_title();

		// Assert.
		$this->assertNull( $result );
	}
}
