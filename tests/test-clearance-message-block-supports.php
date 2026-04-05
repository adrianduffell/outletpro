<?php
/**
 * Tests for clearance message block supports.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\register_clearance_message_block;
use function WC_Clearance\reset_blocks;

class Test_Clearance_Message_Block_Supports extends WP_UnitTestCase {

	public function test_supports_experimental_font_weight(): void {
		// Arrange.
		reset_blocks();

		// Act.
		register_clearance_message_block();

		// Assert.
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'wc-clearance/clearance-message' );
		$this->assertTrue( $block_type->supports['typography']['__experimentalFontWeight'] );
	}

	public function test_supports_experimental_font_family(): void {
		// Arrange.
		reset_blocks();

		// Act.
		register_clearance_message_block();

		// Assert.
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'wc-clearance/clearance-message' );
		$this->assertTrue( $block_type->supports['typography']['__experimentalFontFamily'] );
	}

	public function test_does_not_support_non_experimental_font_weight(): void {
		// Arrange.
		reset_blocks();

		// Act.
		register_clearance_message_block();

		// Assert.
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'wc-clearance/clearance-message' );
		$this->assertArrayNotHasKey( 'fontWeight', $block_type->supports['typography'] );
	}

	public function test_does_not_support_non_experimental_font_family(): void {
		// Arrange.
		reset_blocks();

		// Act.
		register_clearance_message_block();

		// Assert.
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'wc-clearance/clearance-message' );
		$this->assertArrayNotHasKey( 'fontFamily', $block_type->supports['typography'] );
	}

	public function test_supports_experimental_text_transform(): void {
		// Arrange.
		reset_blocks();

		// Act.
		register_clearance_message_block();

		// Assert.
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'wc-clearance/clearance-message' );
		$this->assertTrue( $block_type->supports['typography']['__experimentalTextTransform'] );
	}

	public function test_supports_experimental_text_decoration(): void {
		// Arrange.
		reset_blocks();

		// Act.
		register_clearance_message_block();

		// Assert.
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'wc-clearance/clearance-message' );
		$this->assertTrue( $block_type->supports['typography']['__experimentalTextDecoration'] );
	}

	public function test_supports_experimental_letter_spacing(): void {
		// Arrange.
		reset_blocks();

		// Act.
		register_clearance_message_block();

		// Assert.
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'wc-clearance/clearance-message' );
		$this->assertTrue( $block_type->supports['typography']['__experimentalLetterSpacing'] );
	}
}
