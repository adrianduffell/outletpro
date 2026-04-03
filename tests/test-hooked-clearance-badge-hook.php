<?php
/**
 * Tests for hooked_clearance_badge_hook().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\blocks_init;

class Test_Hooked_Clearance_Badge_Hook extends WP_UnitTestCase {

	public function test_badge_is_not_added_when_context_is_null(): void {
		// Arrange.
		blocks_init();

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'after', 'woocommerce/product-price', null );

		// Assert.
		$this->assertNotContains( 'wc-clearance/clearance-badge', $result );
	}

	public function test_badge_is_not_added_when_template_is_not_single_product(): void {
		// Arrange.
		blocks_init();
		$template       = new WP_Block_Template();
		$template->slug = 'archive-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'after', 'woocommerce/product-price', $template );

		// Assert.
		$this->assertNotContains( 'wc-clearance/clearance-badge', $result );
	}

	public function test_badge_is_added_when_template_is_single_product(): void {
		// Arrange.
		blocks_init();
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'after', 'woocommerce/product-price', $template );

		// Assert.
		$this->assertContains( 'wc-clearance/clearance-badge', $result );
	}

	public function test_other_hooked_blocks_are_not_filtered(): void {
		// Arrange.
		blocks_init();
		$template       = new WP_Block_Template();
		$template->slug = 'archive-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array( 'core/paragraph' ), 'after', 'woocommerce/product-price', $template );

		// Assert.
		$this->assertContains( 'core/paragraph', $result );
	}

	public function test_badge_is_not_added_for_different_anchor(): void {
		// Arrange.
		blocks_init();
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'after', 'core/heading', $template );

		// Assert.
		$this->assertNotContains( 'wc-clearance/clearance-badge', $result );
	}
}
