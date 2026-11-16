<?php
/**
 * Tests for the Outlet term archive's native catalog template fallback.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\init_taxonomies;
use function OutletPro\seed_outlet_status_taxonomy;
use const OutletPro\OUTLET_PAGE_OPTION;

class Test_Outlet_Page_Catalog_Template extends WP_UnitTestCase {

	public function test_outlet_uses_the_customized_product_catalog_template(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		add_theme_support( 'block-templates' );
		init_taxonomies();
		seed_outlet_status_taxonomy();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$template_id = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_status'  => 'publish',
				'post_name'    => 'archive-product',
				'post_content' => '<!-- wp:paragraph --><p>Custom catalog</p><!-- /wp:paragraph -->',
			)
		);
		wp_set_object_terms( $template_id, get_stylesheet(), 'wp_theme' );
		$this->go_to( get_permalink( $page_id ) );

		// Act.
		$template = get_archive_template();
		$content  = $GLOBALS['_wp_current_template_content'];

		// Assert.
		$this->assertSame( ABSPATH . WPINC . '/template-canvas.php', $template );
		$this->assertStringContainsString( 'Custom catalog', $content );
	}

	public function test_classic_themes_keep_the_product_catalog_template(): void {
		// Arrange.
		switch_theme( 'storefront' );
		remove_theme_support( 'block-templates' );
		add_theme_support( 'woocommerce' );
		init_taxonomies();
		seed_outlet_status_taxonomy();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		WC_Template_Loader::init();
		$this->go_to( get_permalink( $page_id ) );

		// Act.
		$template = apply_filters( 'template_include', get_archive_template() );

		// Assert.
		$this->assertSame( 'archive-product.php', basename( $template ) );
	}
}
