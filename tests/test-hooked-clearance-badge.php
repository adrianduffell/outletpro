<?php
/**
 * Tests for the clearance badge block hooks configuration.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\register_clearance_badge_block;

class Test_Hooked_Clearance_Badge extends WP_UnitTestCase {

	public function test_clearance_badge_has_no_block_hooks_declaration(): void {
		// Arrange.
		unregister_block_type( 'wc-clearance/clearance-badge' );
		register_clearance_badge_block();

		// Act.
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'wc-clearance/clearance-badge' );

		// Assert: the PHP filter is the sole source of truth; block.json must not declare blockHooks.
		$this->assertEmpty( $block_type->block_hooks );
	}
}
