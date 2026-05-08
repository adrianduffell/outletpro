<?php
/**
 * Tests for restrict_clearance_blocks_to_site_editor_hook().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\deinit_blocks;
use function WC_Clearance\init_block_editor;
use function WC_Clearance\init_blocks;

class Test_Restrict_Clearance_Blocks_To_Site_Editor_Hook extends WP_UnitTestCase {

	public function test_clearance_blocks_excluded_from_page_editor_when_all_blocks_allowed(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		init_block_editor();
		$context = new WP_Block_Editor_Context( array( 'name' => 'core/edit-post' ) );

		// Act.
		$result = apply_filters( 'allowed_block_types_all', true, $context );

		// Assert.
		$this->assertIsArray( $result );
		$this->assertNotContains( 'wc-clearance/clearance-badge', $result );
		$this->assertNotContains( 'wc-clearance/clearance-message', $result );
	}

	public function test_clearance_blocks_excluded_from_page_editor_when_block_list_provided(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		init_block_editor();
		$context = new WP_Block_Editor_Context( array( 'name' => 'core/edit-post' ) );

		// Act.
		$result = apply_filters(
			'allowed_block_types_all',
			array( 'core/paragraph', 'wc-clearance/clearance-badge', 'wc-clearance/clearance-message' ),
			$context
		);

		// Assert.
		$this->assertNotContains( 'wc-clearance/clearance-badge', $result );
		$this->assertNotContains( 'wc-clearance/clearance-message', $result );
	}

	public function test_other_blocks_not_excluded_from_page_editor(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		init_block_editor();
		$context = new WP_Block_Editor_Context( array( 'name' => 'core/edit-post' ) );

		// Act.
		$result = apply_filters(
			'allowed_block_types_all',
			array( 'core/paragraph', 'wc-clearance/clearance-badge' ),
			$context
		);

		// Assert.
		$this->assertContains( 'core/paragraph', $result );
	}

	public function test_clearance_blocks_allowed_in_site_editor_when_all_blocks_allowed(): void {
		// Arrange.
		init_block_editor();
		$context = new WP_Block_Editor_Context( array( 'name' => 'core/edit-site' ) );

		// Act.
		$result = apply_filters( 'allowed_block_types_all', true, $context );

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_clearance_blocks_allowed_in_site_editor_when_block_list_provided(): void {
		// Arrange.
		init_block_editor();
		$context = new WP_Block_Editor_Context( array( 'name' => 'core/edit-site' ) );

		// Act.
		$result = apply_filters(
			'allowed_block_types_all',
			array( 'core/paragraph', 'wc-clearance/clearance-badge' ),
			$context
		);

		// Assert.
		$this->assertContains( 'wc-clearance/clearance-badge', $result );
	}

	public function test_false_allowed_block_types_unchanged_in_page_editor(): void {
		// Arrange.
		init_block_editor();
		$context = new WP_Block_Editor_Context( array( 'name' => 'core/edit-post' ) );

		// Act.
		$result = apply_filters( 'allowed_block_types_all', false, $context );

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_other_blocks_remain_when_all_blocks_allowed_in_page_editor(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		init_block_editor();
		$context = new WP_Block_Editor_Context( array( 'name' => 'core/edit-post' ) );
		register_block_type( 'test/my-block', array( 'render_callback' => '__return_empty_string' ) );

		// Act.
		$result = apply_filters( 'allowed_block_types_all', true, $context );

		// Assert.
		$this->assertContains( 'test/my-block', $result );

		// Cleanup.
		unregister_block_type( 'test/my-block' );
	}
}
