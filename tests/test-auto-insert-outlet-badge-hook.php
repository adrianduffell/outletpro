<?php
/**
 * Tests for auto_insert_outlet_badge_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_blocks;
use function OutletPro\init_blocks;
use function OutletPro\register_outlet_badge_block;
use const OutletPro\SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD;

class Test_Auto_Insert_Outlet_Badge_Hook extends WP_UnitTestCase {

	public function test_badge_is_inserted_at_theme_mod_position(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		set_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD, array( 'core/group', 'first_child' ) );
		$context = array( 'name' => 'purple/hidden-single-product' );
		$content = '<!-- wp:group --><div class="wp-block-group"><!-- wp:post-title /--></div><!-- /wp:group -->';

		// Act.
		$result = apply_block_hooks_to_content( $content, $context );
		$blocks = parse_blocks( $result );

		// Assert.
		$this->assertCount( 1, $blocks );
		$this->assertCount( 2, $blocks[0]['innerBlocks'] );
		$this->assertSame( 'outletpro/outlet-badge', $blocks[0]['innerBlocks'][0]['blockName'] );
		$this->assertSame( 'core/post-title', $blocks[0]['innerBlocks'][1]['blockName'] );
	}

	public function test_does_not_insert_when_theme_mod_is_empty(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		set_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD, array() );
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$content = apply_block_hooks_to_content( '<!-- wp:post-title /-->', $template );

		// Assert.
		$this->assertFalse( has_block( 'outletpro/outlet-badge', $content ) );
	}

	public function test_outlet_badge_has_no_block_hooks_declaration(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_badge_block();

		// Act.
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'outletpro/outlet-badge' );

		// Assert.
		$this->assertEmpty( $block_type->block_hooks );
	}

	public function test_badge_is_not_added_when_context_is_array_without_name(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'before', 'core/post-title', array() );

		// Assert.
		$this->assertNotContains( 'outletpro/outlet-badge', $result );
	}

	public function test_badge_is_not_added_when_context_is_null(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'before', 'core/post-title', null );

		// Assert.
		$this->assertNotContains( 'outletpro/outlet-badge', $result );
	}

	public function test_badge_is_not_added_when_template_is_not_single_product(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$template       = new WP_Block_Template();
		$template->slug = 'archive-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'before', 'core/post-title', $template );

		// Assert.
		$this->assertNotContains( 'outletpro/outlet-badge', $result );
	}

	public function test_badge_is_added_when_template_is_single_product(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'before', 'core/post-title', $template );

		// Assert.
		$this->assertContains( 'outletpro/outlet-badge', $result );
	}

	public function test_badge_is_added_to_woo_hidden_single_product_pattern(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$context = array( 'name' => 'purple/hidden-single-product' );

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'before', 'core/post-title', $context );

		// Assert.
		$this->assertContains( 'outletpro/outlet-badge', $result );
	}

	public function test_other_hooked_blocks_are_not_filtered(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$template       = new WP_Block_Template();
		$template->slug = 'archive-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array( 'core/paragraph' ), 'before', 'core/post-title', $template );

		// Assert.
		$this->assertContains( 'core/paragraph', $result );
	}

	public function test_badge_is_not_added_for_different_anchor(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'before', 'core/heading', $template );

		// Assert.
		$this->assertNotContains( 'outletpro/outlet-badge', $result );
	}
}
