<?php
/**
 * Tests for auto_insert_clearance_message_hook().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\init_blocks;
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
		init_blocks();

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'first_child', 'woocommerce/product-meta', array() );

		// Assert.
		$this->assertNotContains( 'wc-clearance/clearance-message', $result );
	}

	public function test_message_is_not_added_when_context_is_null(): void {
		// Arrange.
		reset_blocks();
		init_blocks();

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'first_child', 'woocommerce/product-meta', null );

		// Assert.
		$this->assertNotContains( 'wc-clearance/clearance-message', $result );
	}

	public function test_message_is_not_added_when_template_is_not_single_product(): void {
		// Arrange.
		reset_blocks();
		init_blocks();
		$template       = new WP_Block_Template();
		$template->slug = 'archive-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'first_child', 'woocommerce/product-meta', $template );

		// Assert.
		$this->assertNotContains( 'wc-clearance/clearance-message', $result );
	}

	public function test_message_is_added_when_template_is_single_product(): void {
		// Arrange.
		reset_blocks();
		init_blocks();
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'first_child', 'woocommerce/product-meta', $template );

		// Assert.
		$this->assertContains( 'wc-clearance/clearance-message', $result );
	}

	public function test_existing_hooked_blocks_are_preserved(): void {
		// Arrange.
		reset_blocks();
		init_blocks();
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array( 'core/paragraph' ), 'first_child', 'woocommerce/product-meta', $template );

		// Assert.
		$this->assertContains( 'core/paragraph', $result );
		$this->assertContains( 'wc-clearance/clearance-message', $result );
	}

	public function test_message_is_not_added_for_different_anchor(): void {
		// Arrange.
		reset_blocks();
		init_blocks();
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'first_child', 'core/heading', $template );

		// Assert.
		$this->assertNotContains( 'wc-clearance/clearance-message', $result );
	}

	public function test_message_is_not_added_for_last_child_position(): void {
		// Arrange.
		reset_blocks();
		init_blocks();
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'last_child', 'woocommerce/product-meta', $template );

		// Assert.
		$this->assertNotContains( 'wc-clearance/clearance-message', $result );
	}
}
