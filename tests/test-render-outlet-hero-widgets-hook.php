<?php
/**
 * Tests for render_outlet_hero_widgets_hook().
 *
 * @package OutletPro
 */

use function OutletPro\add_to_outlet;
use function OutletPro\init_taxonomies;
use function OutletPro\init_widgets;
use function OutletPro\init_woocommerce_template_hooks;
use function OutletPro\seed_outlet_status_taxonomy;
use const OutletPro\OUTLET_HERO_TEMPLATE_HOOK_THEME_MOD;
use const OutletPro\OUTLET_HERO_WIDGET_AREA;
use const OutletPro\OUTLET_PAGE_OPTION;

class Test_Render_Outlet_Hero_Widgets_Hook extends WP_UnitTestCase {

	// phpcs:ignore Generic.Commenting.DocComment.MissingShort -- PHPUnit data provider metadata.
	/**
	 * @testWith [1]
	 *           [2]
	 */
	public function test_saved_widgets_render_in_the_outlet_archive_header( int $page_number ): void {
		// Arrange.
		switch_theme( 'storefront' );
		init_widgets();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		update_option( 'woocommerce_catalog_columns', 1 );
		update_option( 'woocommerce_catalog_rows', 1 );
		add_to_outlet( WC_Helper_Product::create_simple_product() );
		add_to_outlet( WC_Helper_Product::create_simple_product() );
		update_option( 'widget_block', array( 2 => array( 'content' => '<!-- wp:paragraph --><p>Saved hero</p><!-- /wp:paragraph -->' ) ) );
		$GLOBALS['wp_registered_widgets'] = array();
		wp_widgets_init();
		wp_set_sidebars_widgets( array( OUTLET_HERO_WIDGET_AREA => array( 'block-2' ) ) );
		remove_theme_mod( OUTLET_HERO_TEMPLATE_HOOK_THEME_MOD );
		init_woocommerce_template_hooks();
		$this->go_to( add_query_arg( 'paged', $page_number, get_permalink( $page_id ) ) );

		// Expect.
		$this->expectOutputRegex( '/<header class="woocommerce-products-header">.*Saved hero.*<\/header>/s' );

		// Act.
		woocommerce_product_taxonomy_archive_header();
	}

	public function test_other_requests_do_not_render_outlet_widgets(): void {
		// Arrange.
		switch_theme( 'storefront' );
		init_widgets();
		update_option( 'widget_block', array( 2 => array( 'content' => '<!-- wp:paragraph --><p>Saved hero</p><!-- /wp:paragraph -->' ) ) );
		$GLOBALS['wp_registered_widgets'] = array();
		wp_widgets_init();
		wp_set_sidebars_widgets( array( OUTLET_HERO_WIDGET_AREA => array( 'block-2' ) ) );
		remove_theme_mod( OUTLET_HERO_TEMPLATE_HOOK_THEME_MOD );
		init_woocommerce_template_hooks();
		set_query_var( 'outletpro', false );

		// Expect.
		$this->expectOutputString( '' );

		// Act.
		do_action( 'woocommerce_archive_description' );
	}
}
