<?php
/**
 * Tests for conditionally_render_outlet_group_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\deinit_blocks;
use function OutletPro\init_blocks;
use function OutletPro\register_outlet_status_taxonomy;
use function OutletPro\seed_outlet_status_taxonomy;

class Test_Conditionally_Render_Outlet_Group_Hook extends WP_UnitTestCase {

	public function test_renders_outlet_group_for_outlet_product(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$GLOBALS['post'] = get_post( $product->get_id() );
		deinit_blocks();
		init_blocks();
		$content = '<!-- wp:outletpro/outlet-group --><div class="wp-block-outletpro-outlet-group"><!-- wp:paragraph --><p>Outlet content</p><!-- /wp:paragraph --></div><!-- /wp:outletpro/outlet-group -->';

		// Act.
		$result = do_blocks( $content );

		// Assert.
		$this->assertStringContainsString( 'wp-block-outletpro-outlet-group', $result );
		$this->assertStringContainsString( 'Outlet content', $result );
	}

	public function test_does_not_render_outlet_group_for_non_outlet_product(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product         = \WC_Helper_Product::create_simple_product();
		$GLOBALS['post'] = get_post( $product->get_id() );
		deinit_blocks();
		init_blocks();
		$content = '<!-- wp:outletpro/outlet-group --><div class="wp-block-outletpro-outlet-group"><!-- wp:paragraph --><p>Outlet content</p><!-- /wp:paragraph --></div><!-- /wp:outletpro/outlet-group -->';

		// Act.
		$result = do_blocks( $content );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_does_not_render_inner_blocks_for_non_outlet_product(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product         = \WC_Helper_Product::create_simple_product();
		$GLOBALS['post'] = get_post( $product->get_id() );
		deinit_blocks();
		init_blocks();
		$render_count = 0;
		register_block_type(
			'outletpro/outlet-group-test-inner',
			array(
				'render_callback' => static function () use ( &$render_count ): string {
					++$render_count;
					return '<p>Rendered inner block</p>';
				},
			)
		);
		$content = '<!-- wp:outletpro/outlet-group --><div class="wp-block-outletpro-outlet-group"><!-- wp:outletpro/outlet-group-test-inner /--></div><!-- /wp:outletpro/outlet-group -->';

		// Act.
		$result = do_blocks( $content );

		// Assert.
		$this->assertSame( '', $result );
		$this->assertSame( 0, $render_count );
	}

	public function test_renders_core_group_for_non_outlet_product(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product         = \WC_Helper_Product::create_simple_product();
		$GLOBALS['post'] = get_post( $product->get_id() );
		deinit_blocks();
		init_blocks();
		$content = '<!-- wp:group --><div class="wp-block-group"><!-- wp:paragraph --><p>Regular content</p><!-- /wp:paragraph --></div><!-- /wp:group -->';

		// Act.
		$result = do_blocks( $content );

		// Assert.
		$this->assertStringContainsString( 'Regular content', $result );
	}

	public function test_deinit_blocks_removes_pre_render_filter(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();

		// Act.
		deinit_blocks();

		// Assert.
		$this->assertFalse( has_filter( 'pre_render_block', 'OutletPro\conditionally_render_outlet_group_hook' ) );
	}
}
