<?php
/**
 * Tests for outlet_page_post_global_hook().
 *
 * @package OutletPro
 */

use function OutletPro\add_to_outlet;
use function OutletPro\init_taxonomies;
use const OutletPro\OUTLET_PAGE_OPTION;

class Test_Outlet_Page_Post_Global_Hook extends WP_UnitTestCase {

	public function test_outlet_request_sets_the_page_as_the_global_post(): void {
		// Arrange.
		init_taxonomies();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );

		// Act.
		$this->go_to( get_permalink( $page_id ) );

		// Assert.
		$this->assertSame( $page_id, $GLOBALS['post']->ID );
		$this->assertSame( $product->get_id(), $GLOBALS['wp_query']->post->ID );
	}

	public function test_other_requests_keep_their_global_post(): void {
		// Arrange.
		$outlet_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$page_id   = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $outlet_id );

		// Act.
		$this->go_to( get_permalink( $page_id ) );

		// Assert.
		$this->assertSame( $page_id, $GLOBALS['post']->ID );
	}
}
