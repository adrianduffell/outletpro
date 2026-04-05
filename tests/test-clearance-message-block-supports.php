<?php
/**
 * Tests for clearance message block supports.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\register_clearance_message_block;
use function WC_Clearance\reset_blocks;

class Test_Clearance_Message_Block_Supports extends WP_UnitTestCase {

	public function test_supports_font_weight(): void {
		// Arrange.
		reset_blocks();
		register_clearance_message_block();

		// Act.
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'wc-clearance/clearance-message' );

		// Assert.
		$this->assertTrue( $block_type->supports['typography']['fontWeight'] );
	}

	public function test_supports_font_family(): void {
		// Arrange.
		reset_blocks();
		register_clearance_message_block();

		// Act.
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'wc-clearance/clearance-message' );

		// Assert.
		$this->assertTrue( $block_type->supports['typography']['fontFamily'] );
	}

	public function test_does_not_support_experimental_font_weight(): void {
		// Arrange.
		reset_blocks();
		register_clearance_message_block();

		// Act.
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'wc-clearance/clearance-message' );

		// Assert.
		$this->assertArrayNotHasKey( '__experimentalFontWeight', $block_type->supports['typography'] );
	}

	public function test_does_not_support_experimental_font_family(): void {
		// Arrange.
		reset_blocks();
		register_clearance_message_block();

		// Act.
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'wc-clearance/clearance-message' );

		// Assert.
		$this->assertArrayNotHasKey( '__experimentalFontFamily', $block_type->supports['typography'] );
	}
}
