<?php
/**
 * Tests for auto_insert_outlet_message_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_blocks;
use function OutletPro\init_blocks;
use function OutletPro\register_outlet_message_block;
use const OutletPro\SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD;

class Test_Auto_Insert_Outlet_Message_Hook extends WP_UnitTestCase {

	public function test_does_not_insert_when_theme_mod_is_empty(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array() );
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';
		$content        = '<!-- wp:post-title /-->';

		// Act.
		$result = apply_block_hooks_to_content( $content, $template );

		// Assert.
		$this->assertFalse( has_block( 'outletpro/outlet-message', $result ) );
	}

	public function test_outlet_message_has_no_block_hooks_declaration(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_message_block();

		// Act.
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'outletpro/outlet-message' );

		// Assert.
		$this->assertEmpty( $block_type->block_hooks );
	}

	public function test_message_is_not_added_when_context_is_array(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-meta', 'first_child' ) );

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'first_child', 'woocommerce/product-meta', array() );

		// Assert.
		$this->assertNotContains( 'outletpro/outlet-message', $result );
	}

	public function test_message_is_not_added_when_context_is_null(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-meta', 'first_child' ) );

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'first_child', 'woocommerce/product-meta', null );

		// Assert.
		$this->assertNotContains( 'outletpro/outlet-message', $result );
	}

	public function test_message_is_not_added_when_template_is_not_single_product(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-meta', 'first_child' ) );
		$template       = new WP_Block_Template();
		$template->slug = 'archive-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'first_child', 'woocommerce/product-meta', $template );

		// Assert.
		$this->assertNotContains( 'outletpro/outlet-message', $result );
	}

	public function test_message_is_added_when_template_is_single_product(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-meta', 'first_child' ) );
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'first_child', 'woocommerce/product-meta', $template );

		// Assert.
		$this->assertContains( 'outletpro/outlet-message', $result );
	}

	public function test_message_is_added_to_woo_hidden_single_product_pattern(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-meta', 'first_child' ) );
		$context = array( 'name' => 'purple/hidden-single-product' );

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'first_child', 'woocommerce/product-meta', $context );

		// Assert.
		$this->assertContains( 'outletpro/outlet-message', $result );
	}

	public function test_existing_hooked_blocks_are_preserved(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-meta', 'first_child' ) );
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array( 'core/paragraph' ), 'first_child', 'woocommerce/product-meta', $template );

		// Assert.
		$this->assertContains( 'core/paragraph', $result );
		$this->assertContains( 'outletpro/outlet-message', $result );
	}

	public function test_message_is_not_added_for_different_anchor(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-meta', 'first_child' ) );
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'first_child', 'core/heading', $template );

		// Assert.
		$this->assertNotContains( 'outletpro/outlet-message', $result );
	}

	public function test_message_is_not_added_for_last_child_position(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-meta', 'first_child' ) );
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'last_child', 'woocommerce/product-meta', $template );

		// Assert.
		$this->assertNotContains( 'outletpro/outlet-message', $result );
	}
}
