<?php
/**
 * Test the report_taxonomies function.
 *
 * @package WC_Outlet
 */

use function WC_Outlet\add_to_outlet;
use function WC_Outlet\register_outlet_status_taxonomy;
use function WC_Outlet\report_taxonomies;
use function WC_Outlet\seed_outlet_status_taxonomy;
use const WC_Outlet\OUTLET_STATUS_CANONICAL_TERM;
use const WC_Outlet\OUTLET_STATUS_TAXONOMY;

class Test_Report_Taxonomies extends WP_UnitTestCase {

	public function test_taxonomy_registered_is_no_when_taxonomy_not_registered(): void {
		// Arrange.
		unregister_taxonomy( OUTLET_STATUS_TAXONOMY );

		// Act.
		$result = report_taxonomies();

		// Assert.
		$this->assertSame( 'No', $result['outlet-taxonomy-registered'][1] );
	}

	public function test_taxonomy_registered_is_yes_when_taxonomy_is_registered(): void {
		// Arrange.
		register_outlet_status_taxonomy();

		// Act.
		$result = report_taxonomies();

		// Assert.
		$this->assertSame( 'Yes', $result['outlet-taxonomy-registered'][1] );
	}

	public function test_canonical_term_id_is_not_found_when_taxonomy_not_registered(): void {
		// Arrange.
		unregister_taxonomy( OUTLET_STATUS_TAXONOMY );

		// Act.
		$result = report_taxonomies();

		// Assert.
		$this->assertSame( 'Not found', $result['outlet-canonical-term-id'][1] );
	}

	public function test_canonical_term_id_is_not_found_when_canonical_term_does_not_exist(): void {
		// Arrange.
		register_outlet_status_taxonomy();

		// Act.
		$result = report_taxonomies();

		// Assert.
		$this->assertSame( 'Not found', $result['outlet-canonical-term-id'][1] );
	}

	public function test_canonical_term_id_is_term_id_when_canonical_term_exists(): void {
		// Arrange.
		register_outlet_status_taxonomy();
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
		unregister_taxonomy( OUTLET_STATUS_TAXONOMY );

		// Act.
		$result = report_taxonomies();

		// Assert.
		$this->assertSame( 'Unknown', $result['outlet-product-count'][1] );
	}

	public function test_product_count_is_zero_when_no_products_in_outlet(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();

		// Act.
		$result = report_taxonomies();

		// Assert.
		$this->assertSame( 0, $result['outlet-product-count'][1] );
	}

	public function test_product_count_matches_number_of_outlet_products(): void {
		// Arrange.
		register_outlet_status_taxonomy();
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
		register_outlet_status_taxonomy();
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
