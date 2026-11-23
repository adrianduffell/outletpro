<?php
/**
 * Archive page screen selection.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\is_outlet_editor_page;
use const OutletPro\OUTLET_PAGE_OPTION;

class Test_Is_Outlet_Editor_Page extends WP_UnitTestCase {
	public function test_accepts_the_configured_archive_page(): void {
		// Arrange.
		$id = $this->factory->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $id );
		// Act.
		$result = is_outlet_editor_page( get_post( $id ) );
		// Assert.
		$this->assertTrue( $result );
	}

	public function test_preserves_legacy_and_other_pages(): void {
		// Arrange.
		$id    = $this->factory->post->create( array( 'post_type' => 'page' ) );
		$other = $this->factory->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $id );
		update_post_meta( $id, '_wp_page_template', 'outlet-page' );
		// Act.
		$legacy    = is_outlet_editor_page( get_post( $id ) );
		$unrelated = is_outlet_editor_page( get_post( $other ) );
		// Assert.
		$this->assertFalse( $legacy );
		$this->assertFalse( $unrelated );
	}

	public function test_excludes_non_pages_and_trashed_pages(): void {
		// Arrange.
		$id    = $this->factory->post->create();
		$trash = $this->factory->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'trash',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $id );
		// Act.
		$post_result = is_outlet_editor_page( get_post( $id ) );
		update_option( OUTLET_PAGE_OPTION, $trash );
		$trash_result = is_outlet_editor_page( get_post( $trash ) );
		// Assert.
		$this->assertFalse( $post_result );
		$this->assertFalse( $trash_result );
	}
}
