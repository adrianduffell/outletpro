<?php
/**
 * Tests for reset_blocks().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\blocks_init;
use function WC_Clearance\reset_blocks;

class Test_Reset_Blocks extends WP_UnitTestCase {

	public function test_badge_block_is_unregistered_after_reset_blocks(): void {
		// Arrange.
		reset_blocks();
		blocks_init();

		// Act.
		reset_blocks();

		// Assert.
		$this->assertFalse( \WP_Block_Type_Registry::get_instance()->is_registered( 'wc-clearance/clearance-badge' ) );
	}

	public function test_message_block_is_unregistered_after_reset_blocks(): void {
		// Arrange.
		reset_blocks();
		blocks_init();

		// Act.
		reset_blocks();

		// Assert.
		$this->assertFalse( \WP_Block_Type_Registry::get_instance()->is_registered( 'wc-clearance/clearance-message' ) );
	}

	public function test_calling_reset_blocks_when_blocks_not_registered_is_safe(): void {
		// Arrange - ensure blocks are not registered.
		reset_blocks();

		// Act.
		reset_blocks();

		// Assert.
		$this->assertFalse( \WP_Block_Type_Registry::get_instance()->is_registered( 'wc-clearance/clearance-badge' ) );
		$this->assertFalse( \WP_Block_Type_Registry::get_instance()->is_registered( 'wc-clearance/clearance-message' ) );
	}

}
