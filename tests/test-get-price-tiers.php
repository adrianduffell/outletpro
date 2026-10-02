<?php
/**
 * Tests for the get_price_tiers function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_price_tiers;
use function OutletPro\register_outlet_search_index_taxonomy;
use function OutletPro\seed_outlet_search_index_taxonomy;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Get_Price_Tiers extends WP_UnitTestCase {

	public function test_gets_only_price_tiers_without_product_assignments(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'AUD' );
		seed_outlet_search_index_taxonomy();
		wp_insert_term( 'custom', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'custom-outlet-price-125', OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		$tiers = get_price_tiers();

		// Assert.
		$this->assertSame( array( 15, 30, 50 ), $tiers );
	}

	public function test_returns_price_tiers_in_numeric_order(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'BRL' );
		seed_outlet_search_index_taxonomy();

		// Act.
		$tiers = get_price_tiers();

		// Assert.
		$this->assertSame( array( 50, 100, 200 ), $tiers );
	}

	public function test_includes_custom_price_tiers(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-price-125', OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		$tiers = get_price_tiers();

		// Assert.
		$this->assertSame( array( 125 ), $tiers );
	}

	public function test_includes_zero_price_tier(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-price-0', OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		$tiers = get_price_tiers();

		// Assert.
		$this->assertSame( array( 0 ), $tiers );
	}

	public function test_returns_empty_when_no_price_tiers_exist(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-discount-50', OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		$tiers = get_price_tiers();

		// Assert.
		$this->assertSame( array(), $tiers );
	}

	public function test_throws_when_taxonomy_is_missing(): void {
		// Arrange.
		unregister_taxonomy( OUTLET_SEARCH_INDEX_TAXONOMY );

		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Outlet price tiers could not be retrieved.' );

		// Act.
		get_price_tiers();
	}
}
