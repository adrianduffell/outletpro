<?php
/**
 * Tests for render_outlet_message_callback().
 *
 * @package WC_Outlet
 */

use function WC_Outlet\add_to_outlet;
use function WC_Outlet\deinit_blocks;
use function WC_Outlet\init_blocks;
use function WC_Outlet\register_outlet_message_block;
use function WC_Outlet\register_outlet_status_taxonomy;
use function WC_Outlet\render_outlet_message_callback;
use function WC_Outlet\seed_outlet_status_taxonomy;
use const WC_Outlet\OUTLET_MESSAGE_OPTION;

class Test_Render_Outlet_Message_Callback extends WP_UnitTestCase {

	public function test_returns_empty_string_when_product_not_in_clearance(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_message_block();
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		$block   = new WP_Block(
			array(
				'blockName'    => 'wc-outlet/outlet-message',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			),
			array( 'postId' => $product->get_id() )
		);

		// Act.
		$result = render_outlet_message_callback( array(), '', $block );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_returns_message_html_when_product_is_in_clearance(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_message_block();
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_MESSAGE_OPTION, 'Not eligible for change of mind returns' );
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$block = new WP_Block(
			array(
				'blockName'    => 'wc-outlet/outlet-message',
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
		$this->assertStringContainsString( 'wc-outlet-message', $result );
		$this->assertStringContainsString( 'Not eligible for change of mind returns', $result );

		// Cleanup.
		delete_option( OUTLET_MESSAGE_OPTION );
	}

	public function test_message_uses_global_message_option(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_message_block();
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_MESSAGE_OPTION, 'Final sale — no returns.' );
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$block = new WP_Block(
			array(
				'blockName'    => 'wc-outlet/outlet-message',
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
		$this->assertStringContainsString( 'Final sale — no returns.', $result );
		$this->assertStringNotContainsString( 'Not eligible for change of mind returns', $result );

		// Cleanup.
		delete_option( OUTLET_MESSAGE_OPTION );
	}

	public function test_returns_empty_string_when_post_id_is_zero(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_message_block();
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$block = new WP_Block(
			array(
				'blockName'    => 'wc-outlet/outlet-message',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			),
			array()
		);

		// Act.
		$result = render_outlet_message_callback( array(), '', $block );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_message_is_registered_after_init_blocks(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		deinit_blocks();

		// Act.
		init_blocks();

		// Assert.
		$this->assertTrue( \WP_Block_Type_Registry::get_instance()->is_registered( 'wc-outlet/outlet-message' ) );
	}

	public function test_empty_option_returns_empty_string(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_message_block();
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_MESSAGE_OPTION, '' );
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$block = new WP_Block(
			array(
				'blockName'    => 'wc-outlet/outlet-message',
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

		// Cleanup.
		delete_option( OUTLET_MESSAGE_OPTION );
	}

	public function test_missing_option_returns_empty_string(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_message_block();
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		delete_option( OUTLET_MESSAGE_OPTION );
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$block = new WP_Block(
			array(
				'blockName'    => 'wc-outlet/outlet-message',
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

	public function test_message_is_wrapped_in_paragraph_tag(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_message_block();
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_MESSAGE_OPTION, 'Not eligible for change of mind returns' );
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$block = new WP_Block(
			array(
				'blockName'    => 'wc-outlet/outlet-message',
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
		$this->assertMatchesRegularExpression( '/<p\b[^>]*>.+<\/p>/', $result );

		// Cleanup.
		delete_option( OUTLET_MESSAGE_OPTION );
	}
}
