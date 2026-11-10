<?php
/**
 * Tests for conditionally_render_outlet_conditional_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\deinit_blocks;
use function OutletPro\init_blocks;
use function OutletPro\init_taxonomies;
use function OutletPro\seed_outlet_status_taxonomy;
use const OutletPro\OUTLET_PAGE_OPTION;

class Test_Conditionally_Render_Outlet_Conditional_Hook extends WP_UnitTestCase {

	public function test_page_condition_renders_on_outlet_pagination_independently_of_the_loop_product(): void {
		// Arrange.
		init_taxonomies();
		deinit_blocks();
		init_blocks();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$this->go_to( add_query_arg( 'paged', 2, get_permalink( $page_id ) ) );
		$product         = WC_Helper_Product::create_simple_product();
		$GLOBALS['post'] = get_post( $product->get_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Prove page mode does not use the product-loop context.
		$content         = '<!-- wp:outletpro/outlet-conditional {"conditionType":"page"} --><div class="wp-block-outletpro-outlet-conditional"><!-- wp:paragraph --><p>Outlet page content</p><!-- /wp:paragraph --></div><!-- /wp:outletpro/outlet-conditional -->';

		// Act.
		$result = do_blocks( $content );

		// Assert.
		$this->assertSame( 2, get_query_var( 'paged' ) );
		$this->assertStringContainsString( 'Outlet page content', $result );
	}

	public function test_page_condition_skips_its_children_outside_the_outlet(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Count child rendering without mocking internal block rendering.
		// Arrange.
		deinit_blocks();
		init_blocks();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$this->go_to( '/' );
		$render_count = 0;
		register_block_type(
			'outletpro/test-page-child',
			array(
				'render_callback' => static function () use ( &$render_count ): string {
					++$render_count;
					return 'Unexpected child';
				},
			)
		);
		$content = '<!-- wp:outletpro/outlet-conditional {"conditionType":"page"} --><div class="wp-block-outletpro-outlet-conditional"><!-- wp:outletpro/test-page-child /--></div><!-- /wp:outletpro/outlet-conditional -->';

		// Act.
		$result = do_blocks( $content );

		// Assert.
		$this->assertSame( '', $result );
		$this->assertSame( 0, $render_count );
	}

	public function test_page_condition_renders_in_an_authorized_draft_preview(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'draft',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$this->go_to(
			add_query_arg(
				array(
					'page_id' => $page_id,
					'preview' => 'true',
				),
				home_url( '/' )
			)
		);
		$content = '<!-- wp:outletpro/outlet-conditional {"conditionType":"page"} --><div class="wp-block-outletpro-outlet-conditional">Preview</div><!-- /wp:outletpro/outlet-conditional -->';

		// Act.
		$result = do_blocks( $content );

		// Assert.
		$this->assertStringContainsString( 'Preview', $result );
	}

	public function test_explicit_product_condition_keeps_existing_behavior(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$GLOBALS['post'] = get_post( $product->get_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Arrange the product context.
		deinit_blocks();
		init_blocks();
		$content = '<!-- wp:outletpro/outlet-conditional {"conditionType":"product"} --><div class="wp-block-outletpro-outlet-conditional">Product content</div><!-- /wp:outletpro/outlet-conditional -->';

		// Act.
		$result = do_blocks( $content );

		// Assert.
		$this->assertStringContainsString( 'Product content', $result );
	}

	public function test_renders_outlet_conditional_for_outlet_product(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$GLOBALS['post'] = get_post( $product->get_id() );
		deinit_blocks();
		init_blocks();
		$content = '<!-- wp:outletpro/outlet-conditional --><div class="wp-block-outletpro-outlet-conditional"><!-- wp:paragraph --><p>Outlet content</p><!-- /wp:paragraph --></div><!-- /wp:outletpro/outlet-conditional -->';

		// Act.
		$result = do_blocks( $content );

		// Assert.
		$this->assertStringContainsString( 'wp-block-outletpro-outlet-conditional', $result );
		$this->assertStringContainsString( 'Outlet content', $result );
	}

	public function test_does_not_render_outlet_conditional_for_non_outlet_product(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		$product         = \WC_Helper_Product::create_simple_product();
		$GLOBALS['post'] = get_post( $product->get_id() );
		deinit_blocks();
		init_blocks();
		$content = '<!-- wp:outletpro/outlet-conditional --><div class="wp-block-outletpro-outlet-conditional"><!-- wp:paragraph --><p>Outlet content</p><!-- /wp:paragraph --></div><!-- /wp:outletpro/outlet-conditional -->';

		// Act.
		$result = do_blocks( $content );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_does_not_render_inner_blocks_for_non_outlet_product(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		$product         = \WC_Helper_Product::create_simple_product();
		$GLOBALS['post'] = get_post( $product->get_id() );
		deinit_blocks();
		init_blocks();
		$render_count = 0;
		register_block_type(
			'outletpro/outlet-conditional-test-inner',
			array(
				'render_callback' => static function () use ( &$render_count ): string {
					++$render_count;
					return '<p>Rendered inner block</p>';
				},
			)
		);
		$content = '<!-- wp:outletpro/outlet-conditional --><div class="wp-block-outletpro-outlet-conditional"><!-- wp:outletpro/outlet-conditional-test-inner /--></div><!-- /wp:outletpro/outlet-conditional -->';

		// Act.
		$result = do_blocks( $content );

		// Assert.
		$this->assertSame( '', $result );
		$this->assertSame( 0, $render_count );
	}

	public function test_renders_core_group_for_non_outlet_product(): void {
		// Arrange.
		init_taxonomies();
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

	public function test_renders_outlet_conditional_only_for_outlet_products_in_query_loop(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();

		$outlet_product = \WC_Helper_Product::create_simple_product();
		$outlet_product->set_name( 'Outlet Query Loop Product' );
		$outlet_product->save();
		add_to_outlet( $outlet_product );

		$regular_product = \WC_Helper_Product::create_simple_product();
		$regular_product->set_name( 'Regular Query Loop Product' );
		$regular_product->save();

		$unrelated_post_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);
		$GLOBALS['post']   = get_post( $unrelated_post_id );

		deinit_blocks();
		init_blocks();

		$content = '<!-- wp:query {"query":{"perPage":2,"pages":0,"offset":0,"postType":"product","order":"asc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false}} --><div class="wp-block-query"><!-- wp:post-template --><!-- wp:post-title /--><!-- wp:outletpro/outlet-conditional --><div class="wp-block-outletpro-outlet-conditional"><!-- wp:paragraph --><p>Outlet-only marker</p><!-- /wp:paragraph --></div><!-- /wp:outletpro/outlet-conditional --><!-- /wp:post-template --></div><!-- /wp:query -->';

		// Act.
		$result = do_blocks( $content );

		// Assert.
		$this->assertStringContainsString( 'Outlet Query Loop Product', $result );
		$this->assertStringContainsString( 'Regular Query Loop Product', $result );
		$this->assertSame( 1, substr_count( $result, 'Outlet-only marker' ) );
	}

	public function test_deinit_blocks_removes_pre_render_filter(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();

		// Act.
		deinit_blocks();

		// Assert.
		$this->assertFalse( has_filter( 'pre_render_block', 'OutletPro\conditionally_render_outlet_conditional_hook' ) );
	}
}
