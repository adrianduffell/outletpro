<?php
/**
 * Tests for render_outlet_tile_callback().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_blocks;
use function OutletPro\init_blocks;
use function OutletPro\init_taxonomies;
use const OutletPro\OUTLET_BADGE_LABEL_OPTION;
use const OutletPro\OUTLET_PAGE_OPTION;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Render_Outlet_Tile_Callback extends WP_UnitTestCase {

	public function test_renders_category(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Last chance' );
		$term_id = self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'name'     => 'Clothing',
				'slug'     => 'clothing',
			)
		);
		$content = '<!-- wp:outletpro/outlet-tile ' . wp_json_encode(
			array(
				'category' => $term_id,
			)
		) . ' /-->';

		// Act.
		$output = do_blocks( $content );

		// Assert.
		$this->assertStringContainsString( 'outlet_category=clothing', $output );
		$this->assertStringContainsString( 'Clothing', $output );
		$this->assertStringContainsString( 'Last chance', $output );
		$this->assertStringContainsString( 'text-decoration:none', $output );
	}

	public function test_uses_default_text_decoration_with_custom_styles(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$term_id = self::factory()->term->create( array( 'taxonomy' => 'product_cat' ) );
		$content = '<!-- wp:outletpro/outlet-tile ' . wp_json_encode(
			array(
				'category' => $term_id,
				'style'    => array(
					'spacing'    => array( 'padding' => array( 'top' => '2em' ) ),
					'typography' => array( 'textAlign' => 'center' ),
				),
			)
		) . ' /-->';

		// Act.
		$output = do_blocks( $content );

		// Assert.
		$this->assertStringContainsString( 'text-decoration:none', $output );
		$this->assertStringContainsString( 'padding-top:2em', $output );
		$this->assertStringContainsString( 'has-text-align-center', $output );
	}

	public function test_renders_selected_text_decoration(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$term_id = self::factory()->term->create( array( 'taxonomy' => 'product_cat' ) );
		$content = '<!-- wp:outletpro/outlet-tile ' . wp_json_encode(
			array(
				'category' => $term_id,
				'style'    => array(
					'typography' => array( 'textDecoration' => 'underline' ),
				),
			)
		) . ' /-->';

		// Act.
		$output = do_blocks( $content );

		// Assert.
		$this->assertStringContainsString( 'text-decoration:underline', $output );
		$this->assertStringNotContainsString( 'text-decoration:none', $output );
	}

	public function test_renders_price(): void {
		// Arrange.
		update_option( 'woocommerce_currency', 'USD' );
		init_taxonomies();
		deinit_blocks();
		init_blocks();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$term_id = self::factory()->term->create(
			array(
				'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
				'slug'     => 'outlet-price-25',
			)
		);
		$content = '<!-- wp:outletpro/outlet-tile ' . wp_json_encode(
			array(
				'category' => $term_id,
			)
		) . ' /-->';

		// Act.
		$output = do_blocks( $content );

		// Assert.
		$this->assertStringContainsString( 'outlet_price=25', $output );
		$this->assertStringContainsString( 'Under $25', $output );
	}

	public function test_renders_discount(): void {
		// Arrange.
		init_taxonomies();
		deinit_blocks();
		init_blocks();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$term_id = self::factory()->term->create(
			array(
				'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
				'slug'     => 'outlet-discount-50',
			)
		);
		$content = '<!-- wp:outletpro/outlet-tile ' . wp_json_encode(
			array(
				'category' => $term_id,
			)
		) . ' /-->';

		// Act.
		$output = do_blocks( $content );

		// Assert.
		$this->assertStringContainsString( 'outlet_discount=50', $output );
		$this->assertStringContainsString( '50% off', $output );
	}

	public function test_hides_badge_label(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Last chance' );
		$term_id = self::factory()->term->create( array( 'taxonomy' => 'product_cat' ) );
		$content = '<!-- wp:outletpro/outlet-tile ' . wp_json_encode(
			array(
				'category'                => $term_id,
				'displayOutletBadgeLabel' => false,
			)
		) . ' /-->';

		// Act.
		$output = do_blocks( $content );

		// Assert.
		$this->assertStringContainsString( '<a ', $output );
		$this->assertStringNotContainsString( 'Last chance', $output );
	}

	public function test_unselected_term_does_not_render(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();

		// Act.
		$output = do_blocks( '<!-- wp:outletpro/outlet-tile /-->' );

		// Assert.
		$this->assertSame( '', $output );
	}
}
