<?php
/**
 * Tests for outlet_page_term_link_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\init_taxonomies;
use function OutletPro\seed_outlet_status_taxonomy;
use const OutletPro\OUTLET_PAGE_OPTION;
use const OutletPro\OUTLET_STATUS_CANONICAL_TERM;
use const OutletPro\OUTLET_STATUS_TAXONOMY;

class Test_Outlet_Page_Term_Link_Hook extends WP_UnitTestCase {

	public function test_outlet_term_links_follow_the_configured_page(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		$page_id = self::factory()->post->create(
			array(
				'post_type' => 'page',
				'post_name' => 'clearance',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$this->set_permalink_structure( '/%postname%/' );

		// Act.
		$url = get_term_link( OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY );

		// Assert.
		$this->assertSame( get_permalink( $page_id ), $url );
	}

	public function test_pretty_outlet_page_url_stays_canonical(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$this->set_permalink_structure( '/%postname%/' );
		$url = get_permalink( $page_id );
		$this->go_to( $url );

		// Act.
		$redirect = redirect_canonical( $url, false );

		// Assert.
		$this->assertNull( $redirect );
	}

	public function test_other_product_terms_keep_their_urls(): void {
		// Arrange.
		init_taxonomies();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$category = self::factory()->term->create_and_get( array( 'taxonomy' => 'product_cat' ) );
		$this->set_permalink_structure( '' );

		// Act.
		$url = get_term_link( $category );

		// Assert.
		$this->assertSame( add_query_arg( 'product_cat', $category->slug, home_url( '/' ) ), $url );
	}
}
