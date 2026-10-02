<?php
/**
 * Tests for the seed_outlet_search_index_taxonomy function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\register_outlet_search_index_taxonomy;
use function OutletPro\seed_outlet_search_index_taxonomy;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Seed_Outlet_Search_Index_Taxonomy extends WP_UnitTestCase {

	public function test_seeds_usd_price_search_indexes(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );

		// Act.
		seed_outlet_search_index_taxonomy();

		// Assert.
		$this->assertSame(
			array( 'outlet-discount-30', 'outlet-discount-50', 'outlet-discount-70', 'outlet-price-10', 'outlet-price-25', 'outlet-price-50' ),
			get_terms(
				array(
					'taxonomy'   => OUTLET_SEARCH_INDEX_TAXONOMY,
					'hide_empty' => false,
					'fields'     => 'slugs',
				)
			)
		);
	}

	public function test_seeds_aud_price_search_indexes(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'AUD' );

		// Act.
		seed_outlet_search_index_taxonomy();

		// Assert.
		$this->assertSame(
			array( 'outlet-discount-30', 'outlet-discount-50', 'outlet-discount-70', 'outlet-price-15', 'outlet-price-30', 'outlet-price-50' ),
			get_terms(
				array(
					'taxonomy'   => OUTLET_SEARCH_INDEX_TAXONOMY,
					'hide_empty' => false,
					'fields'     => 'slugs',
				)
			)
		);
	}

	public function test_seeds_jpy_price_search_indexes(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'JPY' );

		// Act.
		seed_outlet_search_index_taxonomy();

		// Assert.
		$this->assertSame(
			array( 'outlet-discount-30', 'outlet-discount-50', 'outlet-discount-70', 'outlet-price-1000', 'outlet-price-3000', 'outlet-price-5000' ),
			get_terms(
				array(
					'taxonomy'   => OUTLET_SEARCH_INDEX_TAXONOMY,
					'hide_empty' => false,
					'fields'     => 'slugs',
				)
			)
		);
	}

	public function test_uses_usd_price_search_indexes_for_unknown_currency(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'XYZ' );

		// Act.
		seed_outlet_search_index_taxonomy();

		// Assert.
		$this->assertSame(
			array( 'outlet-discount-30', 'outlet-discount-50', 'outlet-discount-70', 'outlet-price-10', 'outlet-price-25', 'outlet-price-50' ),
			get_terms(
				array(
					'taxonomy'   => OUTLET_SEARCH_INDEX_TAXONOMY,
					'hide_empty' => false,
					'fields'     => 'slugs',
				)
			)
		);
	}

	public function test_preserves_existing_search_indexes(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		wp_insert_term( 'custom', OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		seed_outlet_search_index_taxonomy();

		// Assert.
		$this->assertContains(
			'custom',
			get_terms(
				array(
					'taxonomy'   => OUTLET_SEARCH_INDEX_TAXONOMY,
					'hide_empty' => false,
					'fields'     => 'slugs',
				)
			)
		);
	}
}
