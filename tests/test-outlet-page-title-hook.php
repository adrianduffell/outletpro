<?php
/**
 * Tests for outlet_page_title_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\init_taxonomies;
use function OutletPro\seed_outlet_status_taxonomy;
use const OutletPro\OUTLET_PAGE_OPTION;

class Test_Outlet_Page_Title_Hook extends WP_UnitTestCase {

	public function test_outlet_archive_uses_the_configured_page_title(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Clearance',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$this->go_to( get_permalink( $page_id ) );

		// Act.
		$block_title   = do_blocks( '<!-- wp:query-title {"type":"archive","showPrefix":false} /-->' );
		$classic_title = woocommerce_page_title( false );
		$document      = wp_get_document_title();

		// Assert.
		$this->assertStringContainsString( '>Clearance</h1>', $block_title );
		$this->assertSame( 'Clearance', $classic_title );
		$this->assertStringContainsString( 'Clearance', $document );
	}

	public function test_other_product_terms_keep_their_titles(): void {
		// Arrange.
		init_taxonomies();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$category = self::factory()->term->create_and_get(
			array(
				'taxonomy' => 'product_cat',
				'name'     => 'Boots',
			)
		);
		$this->go_to( get_term_link( $category ) );

		// Act.
		$title = woocommerce_page_title( false );

		// Assert.
		$this->assertSame( 'Boots', $title );
	}
}
