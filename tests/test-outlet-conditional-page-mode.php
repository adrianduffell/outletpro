<?php
/**
 * Page conditions are independent of the current product loop.
 *
 * @package OutletPro
 */

use function OutletPro\deinit_blocks;
use function OutletPro\init_blocks;
use const OutletPro\OUTLET_PAGE_OPTION;

class Test_Outlet_Conditional_Page_Mode extends WP_UnitTestCase {

	public function test_page_condition_hides_content_when_the_configured_id_is_corrupt(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$this->go_to( '/' );
		update_option( OUTLET_PAGE_OPTION, 'invalid' );

		// Act.
		$result = do_blocks( '<!-- wp:outletpro/outlet-conditional {"conditionType":"page"} --><div class="wp-block-outletpro-outlet-conditional">Page content</div><!-- /wp:outletpro/outlet-conditional -->' );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_page_condition_hides_content_without_a_configured_page(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		delete_option( OUTLET_PAGE_OPTION );
		$this->go_to( '/' );

		// Act.
		$result = do_blocks( '<!-- wp:outletpro/outlet-conditional {"conditionType":"page"} --><div class="wp-block-outletpro-outlet-conditional">Page content</div><!-- /wp:outletpro/outlet-conditional -->' );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_unknown_condition_does_not_enable_the_page_condition(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$this->go_to( get_permalink( $page_id ) );

		// Act.
		$result = do_blocks( '<!-- wp:outletpro/outlet-conditional {"conditionType":"unknown"} --><div class="wp-block-outletpro-outlet-conditional">Page content</div><!-- /wp:outletpro/outlet-conditional -->' );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_page_condition_hides_content_while_the_page_password_is_required(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$page_id = self::factory()->post->create(
			array(
				'post_type'     => 'page',
				'post_password' => 'secret',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$this->go_to( get_permalink( $page_id ) );

		// Act.
		$result = do_blocks( '<!-- wp:outletpro/outlet-conditional {"conditionType":"page"} --><div class="wp-block-outletpro-outlet-conditional">Protected</div><!-- /wp:outletpro/outlet-conditional -->' );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_page_condition_matches_paginated_requests_despite_the_loop_product(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$this->go_to( add_query_arg( 'paged', 2, get_permalink( $page_id ) ) );
		$product         = WC_Helper_Product::create_simple_product();
		$GLOBALS['post'] = get_post( $product->get_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Arrange a product-loop context.
		$content         = '<!-- wp:outletpro/outlet-conditional {"conditionType":"page"} --><div class="wp-block-outletpro-outlet-conditional">Page content</div><!-- /wp:outletpro/outlet-conditional -->';

		// Act.
		$result = do_blocks( $content );

		// Assert.
		$this->assertSame( 2, get_query_var( 'paged' ) );
		$this->assertStringContainsString( 'Page content', $result );
	}

	public function test_page_condition_does_not_render_children_on_other_pages(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Observe the child render boundary.
		// Arrange.
		deinit_blocks();
		init_blocks();
		update_option( OUTLET_PAGE_OPTION, self::factory()->post->create( array( 'post_type' => 'page' ) ) );
		$this->go_to( '/' );
		$render_count = 0;
		register_block_type(
			'outletpro/test-page-child',
			array(
				'render_callback' => static function () use ( &$render_count ): string {
					++$render_count;
					return 'Child content';
				},
			)
		);

		// Act.
		$result = do_blocks( '<!-- wp:outletpro/outlet-conditional {"conditionType":"page"} --><div class="wp-block-outletpro-outlet-conditional"><!-- wp:outletpro/test-page-child /--></div><!-- /wp:outletpro/outlet-conditional -->' );

		// Assert.
		$this->assertSame( '', $result );
		$this->assertSame( 0, $render_count );
		unregister_block_type( 'outletpro/test-page-child' );
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

		// Act.
		$result = do_blocks( '<!-- wp:outletpro/outlet-conditional {"conditionType":"page"} --><div class="wp-block-outletpro-outlet-conditional">Preview</div><!-- /wp:outletpro/outlet-conditional -->' );

		// Assert.
		$this->assertStringContainsString( 'Preview', $result );
	}
}
