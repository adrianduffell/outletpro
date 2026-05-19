<?php
/**
 * Tests for render_outlet_badge_callback().
 *
 * @package WC_Outlet
 */

use function WC_Outlet\add_to_outlet;
use function WC_Outlet\deinit_blocks;
use function WC_Outlet\init_blocks;
use function WC_Outlet\register_outlet_badge_block;
use function WC_Outlet\register_outlet_status_taxonomy;
use function WC_Outlet\render_outlet_badge_callback;
use function WC_Outlet\seed_outlet_status_taxonomy;
use const WC_Outlet\OUTLET_BADGE_LABEL_OPTION;

class Test_Render_Outlet_Badge_Callback extends WP_UnitTestCase {

	public function test_returns_empty_string_when_product_not_in_outlet(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_badge_block();
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		$block   = new WP_Block(
			array(
				'blockName'    => 'wc-outlet/outlet-badge',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			),
			array( 'postId' => $product->get_id() )
		);

		// Act.
		$result = render_outlet_badge_callback( array(), '', $block );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_returns_badge_html_when_product_is_in_outlet(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_badge_block();
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Clearance' );
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$block = new WP_Block(
			array(
				'blockName'    => 'wc-outlet/outlet-badge',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			),
			array( 'postId' => $product->get_id() )
		);

		// Act.
		$result = $block->render();

		// Assert.
		$this->assertStringContainsString( 'wc-outlet-badge', $result );
		$this->assertStringContainsString( 'Clearance', $result );
		$this->assertMatchesRegularExpression( '/<div[^>]+class="[^"]*wc-outlet-badge/', $result );
	}

	public function test_badge_uses_global_badge_label_option(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_badge_block();
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Sale' );
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$block = new WP_Block(
			array(
				'blockName'    => 'wc-outlet/outlet-badge',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			),
			array( 'postId' => $product->get_id() )
		);

		// Act.
		$result = $block->render();

		// Assert.
		$this->assertStringContainsString( 'Sale', $result );
		$this->assertStringNotContainsString( 'Clearance', $result );
	}

	public function test_returns_empty_string_when_post_id_is_zero(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_badge_block();
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$block = new WP_Block(
			array(
				'blockName'    => 'wc-outlet/outlet-badge',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			),
			array()
		);

		// Act.
		$result = render_outlet_badge_callback( array(), '', $block );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_badge_is_registered_after_init_blocks(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		deinit_blocks();

		// Act.
		init_blocks();

		// Assert.
		$this->assertTrue( \WP_Block_Type_Registry::get_instance()->is_registered( 'wc-outlet/outlet-badge' ) );
	}

	public function test_returns_empty_string_when_label_is_empty(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_badge_block();
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_BADGE_LABEL_OPTION, '' );
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$block = new WP_Block(
			array(
				'blockName'    => 'wc-outlet/outlet-badge',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			),
			array( 'postId' => $product->get_id() )
		);

		// Act.
		$result = $block->render();

		// Assert.
		$this->assertSame( '', $result );
	}
}
