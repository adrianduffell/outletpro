<?php
/**
 * Tests for auto_insert_clearance_message_hook().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\blocks_init;
use function WC_Clearance\register_clearance_message_block;
use function WC_Clearance\reset_blocks;

class Test_Auto_Insert_Clearance_Message_Hook extends WP_UnitTestCase {

	public function test_clearance_message_has_no_block_hooks_declaration(): void {
		// Arrange.
		reset_blocks();
		register_clearance_message_block();

		// Act.
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'wc-clearance/clearance-message' );

		// Assert.
		$this->assertEmpty( $block_type->block_hooks );
	}

	public function test_message_is_not_added_when_context_is_array(): void {
		// Arrange.
		reset_blocks();
		blocks_init();

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'before', 'woocommerce/product-short-description', array() );

		// Assert.
		$this->assertNotContains( 'wc-clearance/clearance-message', $result );
	}

	public function test_message_is_not_added_when_context_is_null(): void {
		// Arrange.
		reset_blocks();
		blocks_init();

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'before', 'woocommerce/product-short-description', null );

		// Assert.
		$this->assertNotContains( 'wc-clearance/clearance-message', $result );
	}

	public function test_message_is_not_added_when_template_is_not_single_product(): void {
		// Arrange.
		reset_blocks();
		blocks_init();
		$template       = new WP_Block_Template();
		$template->slug = 'archive-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'before', 'woocommerce/product-short-description', $template );

		// Assert.
		$this->assertNotContains( 'wc-clearance/clearance-message', $result );
	}

	public function test_message_is_added_when_template_is_single_product(): void {
		// Arrange.
		reset_blocks();
		blocks_init();
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'before', 'woocommerce/product-short-description', $template );

		// Assert.
		$this->assertContains( 'wc-clearance/clearance-message', $result );
	}

	public function test_other_hooked_blocks_are_not_filtered(): void {
		// Arrange.
		reset_blocks();
		blocks_init();
		$template       = new WP_Block_Template();
		$template->slug = 'archive-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array( 'core/paragraph' ), 'before', 'woocommerce/product-short-description', $template );

		// Assert.
		$this->assertContains( 'core/paragraph', $result );
	}

	public function test_message_is_not_added_for_different_anchor(): void {
		// Arrange.
		reset_blocks();
		blocks_init();
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'before', 'core/heading', $template );

		// Assert.
		$this->assertNotContains( 'wc-clearance/clearance-message', $result );
	}

	public function test_message_is_not_added_for_after_position(): void {
		// Arrange.
		reset_blocks();
		blocks_init();
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'after', 'woocommerce/product-short-description', $template );

		// Assert.
		$this->assertNotContains( 'wc-clearance/clearance-message', $result );
	}
}
