<?php
/**
 * Tests for the price levels setting.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\batch_price_tiers;
use function OutletPro\deinit_settings;
use function OutletPro\get_discount_tiers;
use function OutletPro\get_price_tiers;
use function OutletPro\init_settings;
use function OutletPro\init_taxonomies;
use function OutletPro\register_outlet_price_levels_setting;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use function OutletPro\update_price_tier;
use const OutletPro\OUTLET_PRICE_LEVELS_OPTION;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Outlet_Price_Levels_Setting extends WP_UnitTestCase {

	public function test_adding_option_creates_facets_through_initialized_hooks(): void {
		// Arrange.
		init_taxonomies();
		deinit_settings();
		init_settings();
		delete_option( OUTLET_PRICE_LEVELS_OPTION );
		truncate_outlet_search_index_taxonomy();

		// Act.
		$result = add_option( OUTLET_PRICE_LEVELS_OPTION, array( 30, 70 ) );

		// Assert.
		$this->assertTrue( $result );
		$this->assertSame( array( 30, 70 ), get_price_tiers() );
	}

	public function test_deleting_option_removes_only_price_facets(): void {
		// Arrange.
		init_taxonomies();
		init_settings();
		truncate_outlet_search_index_taxonomy();
		update_option( OUTLET_PRICE_LEVELS_OPTION, array( 30, 70 ) );
		wp_insert_term( 'outlet-discount-25', OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		$result = delete_option( OUTLET_PRICE_LEVELS_OPTION );

		// Assert.
		$this->assertTrue( $result );
		$this->assertSame( array(), get_option( OUTLET_PRICE_LEVELS_OPTION ) );
		$this->assertSame( array(), get_price_tiers() );
		$this->assertSame( array( 25 ), get_discount_tiers() );
	}

	public function test_setting_can_be_updated_via_rest_with_large_prices(): void {
		// Arrange.
		init_taxonomies();
		register_outlet_price_levels_setting();
		truncate_outlet_search_index_taxonomy();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$request = new WP_REST_Request( 'POST', '/wp/v2/settings' );
		$request->set_param( OUTLET_PRICE_LEVELS_OPTION, array( 50, 100, 500000 ) );

		// Act.
		$response = rest_do_request( $request );

		// Assert.
		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( array( 50, 100, 500000 ), $response->get_data()[ OUTLET_PRICE_LEVELS_OPTION ] );
		$this->assertSame( array( 50, 100, 500000 ), get_option( OUTLET_PRICE_LEVELS_OPTION ) );
	}

	public function test_changed_option_reconciles_current_facets(): void {
		// Arrange.
		init_taxonomies();
		register_outlet_price_levels_setting();
		truncate_outlet_search_index_taxonomy();
		update_option( OUTLET_PRICE_LEVELS_OPTION, array( 30, 50, 70 ) );
		update_price_tier( 50, 60 );
		batch_price_tiers( array( 20, 90 ), array(), array( 30 ) );

		// Act.
		update_option( OUTLET_PRICE_LEVELS_OPTION, array( 30, 50, 80 ) );

		// Assert.
		$this->assertSame( array( 30, 50, 80 ), get_price_tiers() );
	}

	public function test_unsorted_levels_preserve_renamed_facet_id(): void {
		// Arrange.
		init_taxonomies();
		register_outlet_price_levels_setting();
		truncate_outlet_search_index_taxonomy();
		update_option( OUTLET_PRICE_LEVELS_OPTION, array( 30, 50, 70 ) );
		$term   = get_term_by( 'slug', 'outlet-price-70', OUTLET_SEARCH_INDEX_TAXONOMY );
		$values = array( 80, 50, 30 );

		// Act.
		update_option( OUTLET_PRICE_LEVELS_OPTION, $values );

		// Assert.
		$this->assertSame( $term->term_id, get_term_by( 'slug', 'outlet-price-80', OUTLET_SEARCH_INDEX_TAXONOMY )->term_id );
		$this->assertSame( array( 30, 50, 80 ), get_price_tiers() );
		$this->assertSame( $values, get_option( OUTLET_PRICE_LEVELS_OPTION ) );
	}

	public function test_unchanged_option_does_not_synchronize_facets(): void {
		// Arrange.
		init_taxonomies();
		register_outlet_price_levels_setting();
		truncate_outlet_search_index_taxonomy();
		update_option( OUTLET_PRICE_LEVELS_OPTION, array( 30, 50, 70 ) );
		update_price_tier( 50, 60 );

		// Act.
		update_option( OUTLET_PRICE_LEVELS_OPTION, array( 30, 50, 70 ) );

		// Assert.
		$this->assertSame( array( 30, 60, 70 ), get_price_tiers() );
	}

	public function test_empty_list_removes_only_price_facets(): void {
		// Arrange.
		init_taxonomies();
		register_outlet_price_levels_setting();
		truncate_outlet_search_index_taxonomy();
		update_option( OUTLET_PRICE_LEVELS_OPTION, array( 30, 50, 70 ) );
		wp_insert_term( 'outlet-discount-25', OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		update_option( OUTLET_PRICE_LEVELS_OPTION, array() );

		// Assert.
		$this->assertSame( array(), get_option( OUTLET_PRICE_LEVELS_OPTION ) );
		$this->assertSame( array(), get_price_tiers() );
		$this->assertSame( array( 25 ), get_discount_tiers() );
	}

	public function test_option_save_sanitizes_price_levels(): void {
		// Arrange.
		init_taxonomies();
		register_outlet_price_levels_setting();
		truncate_outlet_search_index_taxonomy();
		$values = array( 70, '50', 30 );

		// Act.
		update_option( OUTLET_PRICE_LEVELS_OPTION, $values );

		// Assert.
		$this->assertSame( array( 70, 30 ), get_option( OUTLET_PRICE_LEVELS_OPTION ) );
		$this->assertSame( array( 30, 70 ), get_price_tiers() );
	}

	public function test_rest_rejects_negative_prices_before_changing_facets(): void {
		// Arrange.
		init_taxonomies();
		register_outlet_price_levels_setting();
		truncate_outlet_search_index_taxonomy();
		update_option( OUTLET_PRICE_LEVELS_OPTION, array( 30 ) );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$request = new WP_REST_Request( 'POST', '/wp/v2/settings' );
		$request->set_param( OUTLET_PRICE_LEVELS_OPTION, array( 50, -1 ) );

		// Act.
		$response = rest_do_request( $request );

		// Assert.
		$this->assertSame( 400, $response->get_status() );
		$this->assertSame( array( 30 ), get_option( OUTLET_PRICE_LEVELS_OPTION ) );
		$this->assertSame( array( 30 ), get_price_tiers() );
	}

	public function test_rest_reset_clears_price_facets(): void {
		// Arrange.
		init_taxonomies();
		register_outlet_price_levels_setting();
		truncate_outlet_search_index_taxonomy();
		update_option( OUTLET_PRICE_LEVELS_OPTION, array( 20, 40 ) );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$request = new WP_REST_Request( 'POST', '/wp/v2/settings' );
		$request->set_param( OUTLET_PRICE_LEVELS_OPTION, null );

		// Act.
		$response = rest_do_request( $request );

		// Assert.
		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( array(), $response->get_data()[ OUTLET_PRICE_LEVELS_OPTION ] );
		$this->assertSame( array(), get_price_tiers() );
	}

	public function test_rest_rejects_zero_price_level(): void {
		// Arrange.
		register_outlet_price_levels_setting();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$request = new WP_REST_Request( 'POST', '/wp/v2/settings' );
		$request->set_param( OUTLET_PRICE_LEVELS_OPTION, array( 0, 30 ) );

		// Act.
		$response = rest_do_request( $request );

		// Assert.
		$this->assertSame( 400, $response->get_status() );
	}

	public function test_missing_option_defaults_to_empty_list(): void {
		// Arrange.
		register_outlet_price_levels_setting();
		delete_option( OUTLET_PRICE_LEVELS_OPTION );

		// Act.
		$values = get_option( OUTLET_PRICE_LEVELS_OPTION );

		// Assert.
		$this->assertSame( array(), $values );
	}

	public function test_failed_synchronization_does_not_interrupt_option_update(): void {
		// Arrange.
		init_taxonomies();
		register_outlet_price_levels_setting();
		update_option( OUTLET_PRICE_LEVELS_OPTION, array( 30 ) );
		unregister_taxonomy( OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		$result = update_option( OUTLET_PRICE_LEVELS_OPTION, array( 50 ) );

		// Assert.
		$this->assertTrue( $result );
		$this->assertSame( array( 50 ), get_option( OUTLET_PRICE_LEVELS_OPTION ) );
	}
}
