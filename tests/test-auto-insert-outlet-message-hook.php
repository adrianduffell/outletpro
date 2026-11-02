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

class Test_Auto_Insert_Outlet_Message_Hook extends WP_UnitTestCase {

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

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'first_child', 'woocommerce/product-meta', array() );

		// Assert.
		$this->assertNotContains( 'outletpro/outlet-message', $result );
	}

	public function test_message_is_not_added_when_context_is_null(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'first_child', 'woocommerce/product-meta', null );

		// Assert.
		$this->assertNotContains( 'outletpro/outlet-message', $result );
	}

	public function test_message_is_not_added_when_template_is_not_single_product(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$template          = new WP_Block_Template();
		$template->slug    = 'archive-product';
		$template->content = '<!-- wp:woocommerce/product-meta /-->';

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'first_child', 'woocommerce/product-meta', $template );

		// Assert.
		$this->assertNotContains( 'outletpro/outlet-message', $result );
	}

	public function test_message_is_added_when_template_is_single_product(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$template          = new WP_Block_Template();
		$template->slug    = 'single-product';
		$template->content = '<!-- wp:woocommerce/product-meta /-->';

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'first_child', 'woocommerce/product-meta', $template );

		// Assert.
		$this->assertContains( 'outletpro/outlet-message', $result );
	}

	public function test_message_is_added_to_single_product_pattern(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$context = array(
			'name'    => 'theme/hidden-single-product',
			'content' => '<!-- wp:woocommerce/product-meta /-->',
		);

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'first_child', 'woocommerce/product-meta', $context );

		// Assert.
		$this->assertContains( 'outletpro/outlet-message', $result );
	}

	public function test_existing_hooked_blocks_are_preserved(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$template          = new WP_Block_Template();
		$template->slug    = 'single-product';
		$template->content = '<!-- wp:woocommerce/product-meta /-->';

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
		$template          = new WP_Block_Template();
		$template->slug    = 'single-product';
		$template->content = '<!-- wp:woocommerce/product-meta /-->';

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'last_child', 'woocommerce/product-meta', $template );

		// Assert.
		$this->assertNotContains( 'outletpro/outlet-message', $result );
	}

	public function test_message_is_inserted_after_add_to_cart_with_options(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$template          = new WP_Block_Template();
		$template->slug    = 'single-product';
		$template->content = '<!-- wp:woocommerce/add-to-cart-with-options /--><!-- wp:woocommerce/product-details /-->';

		// Act.
		$result = apply_block_hooks_to_content( $template->content, $template );
		$blocks = parse_blocks( $result );

		// Assert.
		$this->assertSame(
			array( 'woocommerce/add-to-cart-with-options', 'outletpro/outlet-message', 'woocommerce/product-details' ),
			array_column( $blocks, 'blockName' )
		);
	}

	public function test_message_is_inserted_after_add_to_cart_form(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$template          = new WP_Block_Template();
		$template->slug    = 'single-product';
		$template->content = '<!-- wp:woocommerce/add-to-cart-form /-->';

		// Act.
		$result = apply_block_hooks_to_content( $template->content, $template );
		$blocks = parse_blocks( $result );

		// Assert.
		$this->assertSame(
			array( 'woocommerce/add-to-cart-form', 'outletpro/outlet-message' ),
			array_column( $blocks, 'blockName' )
		);
	}

	public function test_message_is_inserted_once_when_multiple_anchors_exist(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$template          = new WP_Block_Template();
		$template->slug    = 'single-product';
		$template->content = '<!-- wp:woocommerce/add-to-cart-with-options /--><!-- wp:woocommerce/product-meta --><div class="wp-block-woocommerce-product-meta"><!-- wp:post-terms {"term":"product_cat"} /--></div><!-- /wp:woocommerce/product-meta -->';

		// Act.
		$result = apply_block_hooks_to_content( $template->content, $template );
		$blocks = parse_blocks( $result );

		// Assert.
		$this->assertSame( 1, substr_count( $result, '<!-- wp:outletpro/outlet-message ' ) );
		$this->assertSame( 'outletpro/outlet-message', $blocks[1]['innerBlocks'][0]['blockName'] );
	}
}
