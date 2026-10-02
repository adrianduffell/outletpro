<?php
/**
 * Tests for the get_discount_tiers function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_discount_tiers;
use function OutletPro\register_outlet_search_index_taxonomy;
use function OutletPro\seed_outlet_search_index_taxonomy;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Get_Discount_Tiers extends WP_UnitTestCase {

	public function test_gets_only_discount_tiers_without_product_assignments(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		seed_outlet_search_index_taxonomy();
		wp_insert_term( 'custom', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'custom-outlet-discount-80', OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		$tiers = get_discount_tiers();

		// Assert.
		$this->assertSame( array( 30, 50, 70 ), $tiers );
	}

	public function test_returns_discount_tiers_in_numeric_order(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-discount-5', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-discount-30', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-discount-100', OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		$tiers = get_discount_tiers();

		// Assert.
		$this->assertSame( array( 5, 30, 100 ), $tiers );
	}

	public function test_includes_custom_discount_tiers(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-discount-80', OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		$tiers = get_discount_tiers();

		// Assert.
		$this->assertSame( array( 80 ), $tiers );
	}

	public function test_includes_zero_discount_tier(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-discount-0', OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		$tiers = get_discount_tiers();

		// Assert.
		$this->assertSame( array( 0 ), $tiers );
	}

	public function test_returns_empty_when_no_discount_tiers_exist(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-price-50', OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		$tiers = get_discount_tiers();

		// Assert.
		$this->assertSame( array(), $tiers );
	}

	public function test_throws_when_taxonomy_is_missing(): void {
		// Arrange.
		unregister_taxonomy( OUTLET_SEARCH_INDEX_TAXONOMY );

		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Outlet discount tiers could not be retrieved.' );

		// Act.
		get_discount_tiers();
	}
}
