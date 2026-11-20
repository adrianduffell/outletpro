<?php
/**
 * Outlet manager screen integration tests.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_outlet_manager_discount_tiers;
use function OutletPro\get_outlet_manager_price_bands;
use function OutletPro\get_outlet_manager_tier;
use function OutletPro\init_outlet_manager;
use function OutletPro\register_outlet_search_index_taxonomy;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use function OutletPro\uses_outlet_manager;
use const OutletPro\OUTLET_PAGE_OPTION;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Outlet_Manager_Screen extends WP_UnitTestCase {
	public function test_reads_configured_price_bands_in_numeric_order(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		update_option( 'woocommerce_currency_pos', 'left' );
		$high = wp_insert_term( 'outlet-price-120', OUTLET_SEARCH_INDEX_TAXONOMY );
		$low  = wp_insert_term( 'outlet-price-35', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-discount-50', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-price-invalid', OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		$bands = get_outlet_manager_price_bands();

		// Assert.
		$this->assertSame(
			array(
				array(
					'id'   => $low['term_id'],
					'name' => 'Under $35',
				),
				array(
					'id'   => $high['term_id'],
					'name' => 'Under $120',
				),
			),
			$bands
		);
	}

	public function test_returns_empty_price_bands_when_none_are_configured(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();

		// Act.
		$bands = get_outlet_manager_price_bands();

		// Assert.
		$this->assertSame( array(), $bands );
	}

	public function test_fails_if_price_bands_cannot_be_read(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		unregister_taxonomy( OUTLET_SEARCH_INDEX_TAXONOMY );

		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Outlet price tiers could not be retrieved.' );

		// Act.
		get_outlet_manager_price_bands();
	}

	public function test_rejects_a_missing_tier(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();

		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Outlet tier could not be retrieved.' );

		// Act.
		get_outlet_manager_tier( 'outlet-price-99' );
	}

	public function test_reads_configured_discount_tiers_in_numeric_order(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		$high = wp_insert_term( 'outlet-discount-80', OUTLET_SEARCH_INDEX_TAXONOMY );
		$low  = wp_insert_term( 'outlet-discount-15', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-price-50', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-discount-invalid', OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		$tiers = get_outlet_manager_discount_tiers();

		// Assert.
		$this->assertSame(
			array(
				array(
					'id'   => $low['term_id'],
					'name' => '15% off',
				),
				array(
					'id'   => $high['term_id'],
					'name' => '80% off',
				),
			),
			$tiers
		);
	}

	public function test_returns_empty_discount_tiers_when_none_are_configured(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();

		// Act.
		$tiers = get_outlet_manager_discount_tiers();

		// Assert.
		$this->assertSame( array(), $tiers );
	}

	public function test_fails_if_discount_tiers_cannot_be_read(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		unregister_taxonomy( OUTLET_SEARCH_INDEX_TAXONOMY );

		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Outlet discount tiers could not be retrieved.' );

		// Act.
		get_outlet_manager_discount_tiers();
	}

	public function test_accepts_the_configured_archive_page(): void {
		// Arrange.
		$id = $this->factory->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $id );

		// Act.
		$result = uses_outlet_manager( get_post( $id ) );

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_preserves_the_legacy_editor(): void {
		// Arrange.
		$id = $this->factory->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $id );
		update_post_meta( $id, '_wp_page_template', 'outlet-page' );

		// Act.
		$result = uses_outlet_manager( get_post( $id ) );

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_leaves_other_pages_in_the_standard_editor(): void {
		// Arrange.
		$id = $this->factory->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $id + 1 );

		// Act.
		$result = uses_outlet_manager( get_post( $id ) );

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_excludes_non_pages_and_trashed_pages(): void {
		// Arrange.
		$id = $this->factory->post->create();
		update_option( OUTLET_PAGE_OPTION, $id );
		$trashed_id = $this->factory->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'trash',
			)
		);

		// Act.
		$post_result = uses_outlet_manager( get_post( $id ) );
		update_option( OUTLET_PAGE_OPTION, $trashed_id );
		$trash_result = uses_outlet_manager( get_post( $trashed_id ) );

		// Assert.
		$this->assertFalse( $post_result );
		$this->assertFalse( $trash_result );
	}

	public function test_does_not_register_replacement_during_initial_screen_detection(): void {
		// Arrange.
		$id = $this->factory->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $id );
		remove_filter( 'replace_editor', 'OutletPro\\replace_outlet_editor_hook', 10 );

		// Act.
		$result = apply_filters( 'replace_editor', false, get_post( $id ) );

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_registers_replacement_after_loading_the_outlet_edit_screen(): void {
		// Arrange.
		init_outlet_manager();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$id = $this->factory->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $id );
		$_GET['post']   = $id;
		$_GET['action'] = 'edit';
		set_current_screen( 'page' );

		// Act.
		do_action( 'load-post.php' ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Core hook name.
		$registered = has_filter( 'replace_editor', 'OutletPro\\replace_outlet_editor_hook' );
		$is_block   = get_current_screen()->is_block_editor();
		remove_filter( 'replace_editor', 'OutletPro\\replace_outlet_editor_hook', 10 );
		remove_action( 'admin_enqueue_scripts', 'OutletPro\\enqueue_outlet_manager_hook' );
		unset( $_GET['post'], $_GET['action'] );

		// Assert.
		$this->assertSame( 10, $registered );
		$this->assertFalse( $is_block );
	}
}
