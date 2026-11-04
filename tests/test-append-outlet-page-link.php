<?php
/**
 * Tests for append_outlet_page_link().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\append_outlet_page_link;
use const OutletPro\OUTLET_PAGE_OPTION;

class Test_Append_Outlet_Page_Link extends WP_UnitTestCase {

	public function test_appends_link_using_the_outlet_page_title(): void {
		// Arrange.
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'draft',
				'post_title'  => 'Last Chance & Final Sale',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );

		// Act.
		$result = append_outlet_page_link( '<strong>Final sale.</strong>' );

		// Assert.
		$this->assertSame(
			'<strong>Final sale.</strong> <a href="' . esc_url( get_permalink( $page_id ) ) . '">Shop all last chance &amp; final sale</a>',
			$result
		);
	}

	public function test_preserves_message_when_outlet_page_is_not_configured(): void {
		// Arrange.
		$page_id         = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$GLOBALS['post'] = get_post( $page_id );
		delete_option( OUTLET_PAGE_OPTION );

		// Act.
		$result = append_outlet_page_link( 'Final sale.' );

		// Assert.
		$this->assertSame( 'Final sale.', $result );
	}
}
