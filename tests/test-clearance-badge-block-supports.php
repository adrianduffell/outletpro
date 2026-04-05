<?php
/**
 * Tests for clearance badge block supports.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\register_clearance_badge_block;
use function WC_Clearance\reset_blocks;

class Test_Clearance_Badge_Block_Supports extends WP_UnitTestCase {

	public function test_supports_font_weight(): void {
		// Arrange.
		reset_blocks();
		register_clearance_badge_block();

		// Act.
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'wc-clearance/clearance-badge' );

		// Assert.
		$this->assertTrue( $block_type->supports['typography']['fontWeight'] );
	}

	public function test_does_not_support_experimental_font_weight(): void {
		// Arrange.
		reset_blocks();
		register_clearance_badge_block();

		// Act.
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'wc-clearance/clearance-badge' );

		// Assert.
		$this->assertArrayNotHasKey( '__experimentalFontWeight', $block_type->supports['typography'] );
	}
}
