<?php
/**
 * Tests for render_clearance_badge_callback().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_to_clearance;
use function WC_Clearance\deinit_blocks;
use function WC_Clearance\init_blocks;
use function WC_Clearance\register_clearance_badge_block;
use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\render_clearance_badge_callback;
use function WC_Clearance\seed_clearance_status_taxonomy;
use const WC_Clearance\CLEARANCE_BADGE_LABEL_OPTION;

class Test_Render_Clearance_Badge_Callback extends WP_UnitTestCase {

	public function test_returns_empty_string_when_product_not_in_clearance(): void {
		// Arrange.
		deinit_blocks();
		register_clearance_badge_block();
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		$block   = new WP_Block(
			array(
				'blockName'    => 'wc-clearance/clearance-badge',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			),
			array( 'postId' => $product->get_id() )
		);

		// Act.
		$result = render_clearance_badge_callback( array(), '', $block );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_returns_badge_html_when_product_is_in_clearance(): void {
		// Arrange.
		deinit_blocks();
		register_clearance_badge_block();
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		delete_option( CLEARANCE_BADGE_LABEL_OPTION );
		$product = \WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		$block = new WP_Block(
			array(
				'blockName'    => 'wc-clearance/clearance-badge',
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
		$this->assertStringContainsString( 'wc-clearance-badge', $result );
		$this->assertStringContainsString( 'Clearance', $result );
	}

	public function test_badge_uses_global_badge_label_option(): void {
		// Arrange.
		deinit_blocks();
		register_clearance_badge_block();
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		update_option( CLEARANCE_BADGE_LABEL_OPTION, 'Sale' );
		$product = \WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		$block = new WP_Block(
			array(
				'blockName'    => 'wc-clearance/clearance-badge',
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
		register_clearance_badge_block();
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$block = new WP_Block(
			array(
				'blockName'    => 'wc-clearance/clearance-badge',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			),
			array()
		);

		// Act.
		$result = render_clearance_badge_callback( array(), '', $block );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_badge_is_registered_after_init_blocks(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		deinit_blocks();

		// Act.
		init_blocks();

		// Assert.
		$this->assertTrue( \WP_Block_Type_Registry::get_instance()->is_registered( 'wc-clearance/clearance-badge' ) );
	}
}
