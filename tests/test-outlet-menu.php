<?php
/**
 * Tests for the Outlet Menu block.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_blocks;
use function OutletPro\init_blocks;
use function OutletPro\register_block_styles;
use function OutletPro\render_outlet_menu_callback;
use const OutletPro\OUTLET_PAGE_OPTION;
use const OutletPro\PLUGIN_FILE;

class Test_Outlet_Menu extends WP_UnitTestCase {

	public function test_renders_saved_names_and_queries_with_the_outlet_permalink(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$items = array(
			array( 'Clothing & Accessories', 'outlet_category=clothing' ),
			array( 'Under $25', 'outlet_price=25' ),
			array( '50% off', 'outlet_discount=50' ),
		);

		// Act.
		$output = do_blocks( '<!-- wp:outletpro/outlet-menu ' . wp_json_encode( array( 'items' => $items ) ) . ' /-->' );

		// Assert.
		$this->assertStringContainsString( '<a href="' . esc_url( add_query_arg( 'outlet_category', 'clothing', get_permalink( $page_id ) ) ) . '">Clothing &amp; Accessories</a>', $output );
		$this->assertStringContainsString( '<a href="' . esc_url( add_query_arg( 'outlet_price', 25, get_permalink( $page_id ) ) ) . '">Under $25</a>', $output );
		$this->assertStringContainsString( '<a href="' . esc_url( add_query_arg( 'outlet_discount', 50, get_permalink( $page_id ) ) ) . '">50% off</a>', $output );
		$this->assertStringContainsString( 'is-layout-flex', $output );
		$this->assertStringContainsString( 'padding-top:0', $output );
		$this->assertStringContainsString( 'padding-right:0', $output );
		$this->assertStringContainsString( 'padding-bottom:0', $output );
		$this->assertStringContainsString( 'padding-left:0', $output );
	}

	public function test_saved_queries_use_the_current_outlet_permalink(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$old_page = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$new_page = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $old_page );
		$content = '<!-- wp:outletpro/outlet-menu {"items":[["All",""],["30% off","outlet_discount=30"]]} /-->';
		update_option( OUTLET_PAGE_OPTION, $new_page );

		// Act.
		$output = do_blocks( $content );

		// Assert.
		$this->assertStringContainsString( '<a href="' . esc_url( get_permalink( $new_page ) ) . '">All</a>', $output );
		$this->assertStringContainsString( esc_url( add_query_arg( 'outlet_discount', 30, get_permalink( $new_page ) ) ), $output );
		$this->assertStringNotContainsString( esc_url( get_permalink( $old_page ) ), $output );
	}

	public function test_missing_outlet_page_returns_null(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		delete_option( OUTLET_PAGE_OPTION );

		// Act.
		$output = render_outlet_menu_callback( array( 'items' => array( array( 'All', '' ) ) ) );

		// Assert.
		$this->assertNull( $output );
	}

	public function test_missing_items_render_an_empty_menu(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );

		// Act.
		$output = do_blocks( '<!-- wp:outletpro/outlet-menu /-->' );

		// Assert.
		$this->assertStringContainsString( '<menu ', $output );
		$this->assertStringNotContainsString( '<li>', $output );
	}

	public function test_empty_items_render_an_empty_menu(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );

		// Act.
		$output = do_blocks( '<!-- wp:outletpro/outlet-menu {"items":[]} /-->' );

		// Assert.
		$this->assertStringNotContainsString( '<a', $output );
	}

	public function test_saved_labels_and_rendered_urls_are_escaped(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$items = array( array( '<script>alert(1)</script>', 'outlet_category=%22%20onclick%3D%22alert%281%29' ) );

		// Act.
		$output = do_blocks( '<!-- wp:outletpro/outlet-menu ' . wp_json_encode( array( 'items' => $items ) ) . ' /-->' );

		// Assert.
		$this->assertStringNotContainsString( '<script>', $output );
		$this->assertStringNotContainsString( '" onclick=', $output );
		$this->assertStringContainsString( '&lt;script&gt;', $output );
	}

	public function test_invalid_items_return_null(): void {
		// Arrange.
		$attributes = array( 'items' => 'invalid' );

		// Act.
		$output = render_outlet_menu_callback( $attributes );

		// Assert.
		$this->assertNull( $output );
	}

	public function test_invalid_pair_returns_null(): void {
		// Arrange.
		$attributes = array( 'items' => array( array( 'Name', 25 ) ) );

		// Act.
		$output = render_outlet_menu_callback( $attributes );

		// Assert.
		$this->assertNull( $output );
	}

	public function test_menu_supports_native_styles_without_metadata_stylesheets(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();

		// Act.
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'outletpro/outlet-menu' );

		// Assert.
		$this->assertSame( array(), $block_type->style_handles );
		$this->assertTrue( $block_type->supports['typography']['__experimentalTextDecoration'] );
	}

	public function test_menu_uses_the_built_block_stylesheet(): void {
		// Arrange.
		wp_dequeue_style( 'outletpro-menu-block' );
		wp_deregister_style( 'outletpro-menu-block' );
		register_block_styles();

		// Act.
		do_action( 'wp_enqueue_scripts' );
		apply_filters( 'render_block', '', array( 'blockName' => 'outletpro/outlet-menu' ), new WP_Block( array( 'blockName' => 'outletpro/outlet-menu' ) ) );

		// Assert.
		$this->assertTrue( wp_style_is( 'outletpro-menu-block', 'registered' ) );
		$this->assertSame( plugins_url( 'build/style-index.css', PLUGIN_FILE ), wp_styles()->registered['outletpro-menu-block']->src );
	}

	public function test_menu_applies_native_block_styles(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );

		// Act.
		$output = do_blocks( '<!-- wp:outletpro/outlet-menu {"style":{"spacing":{"blockGap":"2em","padding":{"top":"2em"}},"typography":{"fontSize":"24px"}}} /-->' );

		// Assert.
		$this->assertStringContainsString( 'wp-block-outletpro-outlet-menu', $output );
		$this->assertStringContainsString( 'padding-top:2em', $output );
		$this->assertStringContainsString( 'font-size:24px', $output );
	}
}
