<?php
/**
 * Test the report_taxonomies function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\deinit_taxonomies;
use function OutletPro\init_taxonomies;
use function OutletPro\report_taxonomies;
use function OutletPro\seed_outlet_status_taxonomy;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;
use const OutletPro\OUTLET_STATUS_CANONICAL_TERM;
use const OutletPro\OUTLET_STATUS_TAXONOMY;

class Test_Report_Taxonomies extends WP_UnitTestCase {

	public function test_search_index_terms_are_unknown_when_taxonomy_not_registered(): void {
		// Arrange.
		deinit_taxonomies();

		// Act.
		$result = report_taxonomies();

		// Assert.
		$this->assertSame( 'Unknown', $result['outlet-search-index-terms'][1] );
	}

	public function test_search_index_terms_are_none_when_taxonomy_is_empty(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();

		// Act.
		$result = report_taxonomies();

		// Assert.
		$this->assertSame( 'None', $result['outlet-search-index-terms'][1] );
	}

	public function test_search_index_terms_include_product_counts_and_unused_terms(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		$price_term    = wp_insert_term( 'outlet-price-42', OUTLET_SEARCH_INDEX_TAXONOMY );
		$discount_term = wp_insert_term( 'outlet-discount-30', OUTLET_SEARCH_INDEX_TAXONOMY );
		$product_one   = WC_Helper_Product::create_simple_product();
		$product_two   = WC_Helper_Product::create_simple_product();
		wp_set_object_terms( $product_one->get_id(), array( $price_term['term_id'] ), OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_set_object_terms( $product_two->get_id(), array( $price_term['term_id'] ), OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		$result = report_taxonomies();

		// Assert.
		$this->assertSame( array( 'Search index', 'outlet-discount-30 (0), outlet-price-42 (2)' ), $result['outlet-search-index-terms'] );
	}

	public function test_search_index_reports_exact_malformed_term_names(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'Malformed Price Name!', OUTLET_SEARCH_INDEX_TAXONOMY, array( 'slug' => 'different-slug' ) );

		// Act.
		$result = report_taxonomies();

		// Assert.
		$this->assertSame( array( 'Search index', 'Malformed Price Name! (0)' ), $result['outlet-search-index-terms'] );
	}

	public function test_taxonomy_registered_is_no_when_taxonomy_not_registered(): void {
		// Arrange.
		deinit_taxonomies();

		// Act.
		$result = report_taxonomies();

		// Assert.
		$this->assertSame( 'No', $result['outlet-taxonomy-registered'][1] );
	}

	public function test_taxonomy_registered_is_yes_when_taxonomy_is_registered(): void {
		// Arrange.
		init_taxonomies();

		// Act.
		$result = report_taxonomies();

		// Assert.
		$this->assertSame( 'Yes', $result['outlet-taxonomy-registered'][1] );
	}

	public function test_canonical_term_id_is_not_found_when_taxonomy_not_registered(): void {
		// Arrange.
		deinit_taxonomies();

		// Act.
		$result = report_taxonomies();

		// Assert.
		$this->assertSame( 'Not found', $result['outlet-canonical-term-id'][1] );
	}

	public function test_canonical_term_id_is_not_found_when_canonical_term_does_not_exist(): void {
		// Arrange.
		init_taxonomies();

		// Act.
		$result = report_taxonomies();

		// Assert.
		$this->assertSame( 'Not found', $result['outlet-canonical-term-id'][1] );
	}

	public function test_canonical_term_id_is_term_id_when_canonical_term_exists(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		$term    = get_term_by( 'name', OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY );
		$term_id = $term->term_id;

		// Act.
		$result = report_taxonomies();

		// Assert.
		$this->assertSame( $term_id, $result['outlet-canonical-term-id'][1] );
	}

	public function test_product_count_is_unknown_when_taxonomy_not_registered(): void {
		// Arrange.
		deinit_taxonomies();

		// Act.
		$result = report_taxonomies();

		// Assert.
		$this->assertSame( 'Unknown', $result['outlet-product-count'][1] );
	}

	public function test_product_count_is_zero_when_no_products_in_outlet(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();

		// Act.
		$result = report_taxonomies();

		// Assert.
		$this->assertSame( 0, $result['outlet-product-count'][1] );
	}

	public function test_product_count_matches_number_of_outlet_products(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();

		$product_one = \WC_Helper_Product::create_simple_product();
		$product_two = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product_one );
		add_to_outlet( $product_two );

		// Act.
		$result = report_taxonomies();

		// Assert.
		$this->assertSame( 2, $result['outlet-product-count'][1] );
	}

	public function test_product_count_ignores_draft_products(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();

		$product = \WC_Helper_Product::create_simple_product();
		$product->set_status( 'draft' );
		$product->save();
		add_to_outlet( $product );

		// Act.
		$result = report_taxonomies();

		// Assert.
		$this->assertSame( 0, $result['outlet-product-count'][1] );
	}
}
