<?php
/**
 * Tests for render_clearance_message_callback().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_to_clearance;
use function WC_Clearance\blocks_init;
use function WC_Clearance\register_clearance_message_block;
use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\render_clearance_message_callback;
use function WC_Clearance\seed_clearance_status_taxonomy;
use const WC_Clearance\CLEARANCE_MESSAGE_OPTION;

class Test_Render_Clearance_Message_Callback extends WP_UnitTestCase {

	public function test_returns_empty_string_when_product_not_in_clearance(): void {
		// Arrange.
		unregister_block_type( 'wc-clearance/clearance-message' );
		register_clearance_message_block();
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		$block   = new WP_Block(
			array(
				'blockName'    => 'wc-clearance/clearance-message',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			),
			array( 'postId' => $product->get_id() )
		);

		// Act.
		$result = render_clearance_message_callback( array(), '', $block );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_returns_message_html_when_product_is_in_clearance(): void {
		// Arrange.
		unregister_block_type( 'wc-clearance/clearance-message' );
		register_clearance_message_block();
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		delete_option( CLEARANCE_MESSAGE_OPTION );
		$product = \WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		$block = new WP_Block(
			array(
				'blockName'    => 'wc-clearance/clearance-message',
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
		$this->assertStringContainsString( 'wc-clearance-message', $result );
		$this->assertStringContainsString( 'Not eligible for change of mind returns', $result );
	}

	public function test_message_uses_global_message_option(): void {
		// Arrange.
		unregister_block_type( 'wc-clearance/clearance-message' );
		register_clearance_message_block();
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		update_option( CLEARANCE_MESSAGE_OPTION, 'Final sale — no returns.' );
		$product = \WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		$block = new WP_Block(
			array(
				'blockName'    => 'wc-clearance/clearance-message',
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
		delete_option( CLEARANCE_MESSAGE_OPTION );
	}

	public function test_returns_empty_string_when_post_id_is_zero(): void {
		// Arrange.
		unregister_block_type( 'wc-clearance/clearance-message' );
		register_clearance_message_block();
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$block = new WP_Block(
			array(
				'blockName'    => 'wc-clearance/clearance-message',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			),
			array()
		);

		// Act.
		$result = render_clearance_message_callback( array(), '', $block );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_message_is_registered_after_blocks_init(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		unregister_block_type( 'wc-clearance/clearance-badge' );
		unregister_block_type( 'wc-clearance/clearance-message' );

		// Act.
		blocks_init();

		// Assert.
		$this->assertTrue( \WP_Block_Type_Registry::get_instance()->is_registered( 'wc-clearance/clearance-message' ) );
	}

	public function test_message_is_wrapped_in_paragraph_tag(): void {
		// Arrange.
		unregister_block_type( 'wc-clearance/clearance-message' );
		register_clearance_message_block();
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		update_option( CLEARANCE_MESSAGE_OPTION, 'Test message' );
		$product = \WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		$block = new WP_Block(
			array(
				'blockName'    => 'wc-clearance/clearance-message',
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
		$this->assertMatchesRegularExpression( '/<p\b[^>]*>Test message<\/p>/', $result );

		// Cleanup.
		delete_option( CLEARANCE_MESSAGE_OPTION );
	}
}
