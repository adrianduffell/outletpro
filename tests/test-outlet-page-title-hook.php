<?php
/**
 * Tests for outlet_page_title_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_page;
use function OutletPro\init_page;
use const OutletPro\OUTLET_PAGE_OPTION;

class Test_Outlet_Page_Title_Hook extends WP_UnitTestCase {

	public function test_archive_title_block_shows_the_outlet_page_title(): void {
		// Arrange.
		deinit_page();
		init_page();
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Clearance',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$this->go_to( get_permalink( $page_id ) );

		// Act.
		$html = do_blocks( '<!-- wp:query-title {"type":"archive","showPrefix":false} /-->' );

		// Assert.
		$this->assertStringContainsString( '>Clearance</h1>', $html );
	}

	public function test_other_archive_titles_are_preserved(): void {
		// Arrange.
		deinit_page();
		init_page();
		register_post_type(
			'title_test',
			array(
				'public'      => true,
				'has_archive' => true,
				'label'       => 'Books',
			)
		);
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$this->go_to( add_query_arg( 'post_type', 'title_test', home_url( '/' ) ) );

		// Act.
		$html = do_blocks( '<!-- wp:query-title {"type":"archive","showPrefix":false} /-->' );
		unregister_post_type( 'title_test' );

		// Assert.
		$this->assertStringContainsString( '>Books</h1>', $html );
	}

	public function test_classic_catalog_title_shows_the_outlet_page_title(): void {
		// Arrange.
		deinit_page();
		init_page();
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Clearance',
			)
		);
		$shop_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Shop',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );
		update_option( 'woocommerce_shop_page_id', $shop_id );
		$this->go_to( get_permalink( $page_id ) );

		// Act.
		$title = woocommerce_page_title( false );

		// Assert.
		$this->assertSame( 'Clearance', $title );
	}
}
