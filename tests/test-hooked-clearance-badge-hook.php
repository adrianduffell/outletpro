<?php
/**
 * Tests for hooked_clearance_badge_hook().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\block_editor_init;

class Test_Hooked_Clearance_Badge_Hook extends WP_UnitTestCase {

	public function test_badge_inserted_after_product_price(): void {
		// Arrange.
		block_editor_init();

		// Act.
		$hooked_blocks = apply_filters( 'hooked_block_types', array(), 'after', 'woocommerce/product-price', array() );

		// Assert.
		$this->assertContains( 'wc-clearance/clearance-badge', $hooked_blocks );
	}

	public function test_badge_not_inserted_before_product_price(): void {
		// Arrange.
		block_editor_init();

		// Act.
		$hooked_blocks = apply_filters( 'hooked_block_types', array(), 'before', 'woocommerce/product-price', array() );

		// Assert.
		$this->assertNotContains( 'wc-clearance/clearance-badge', $hooked_blocks );
	}

	public function test_badge_not_inserted_after_other_blocks(): void {
		// Arrange.
		block_editor_init();

		// Act.
		$hooked_blocks = apply_filters( 'hooked_block_types', array(), 'after', 'woocommerce/product-title', array() );

		// Assert.
		$this->assertNotContains( 'wc-clearance/clearance-badge', $hooked_blocks );
	}

	public function test_existing_hooked_blocks_are_preserved(): void {
		// Arrange.
		block_editor_init();
		$initial_blocks = array( 'some/other-block' );

		// Act.
		$hooked_blocks = apply_filters( 'hooked_block_types', $initial_blocks, 'after', 'woocommerce/product-price', array() );

		// Assert.
		$this->assertContains( 'some/other-block', $hooked_blocks );
		$this->assertContains( 'wc-clearance/clearance-badge', $hooked_blocks );
	}

	public function test_badge_not_inserted_when_anchor_block_is_null(): void {
		// Arrange.
		block_editor_init();

		// Act.
		$hooked_blocks = apply_filters( 'hooked_block_types', array(), 'after', null, array() );

		// Assert.
		$this->assertNotContains( 'wc-clearance/clearance-badge', $hooked_blocks );
	}

	public function test_badge_not_inserted_when_context_is_wp_post(): void {
		// Arrange.
		block_editor_init();
		$post = self::factory()->post->create_and_get();

		// Act.
		$hooked_blocks = apply_filters( 'hooked_block_types', array(), 'after', 'core/navigation', $post );

		// Assert.
		$this->assertNotContains( 'wc-clearance/clearance-badge', $hooked_blocks );
	}
}
