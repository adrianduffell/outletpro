<?php
/**
 * Test the report_taxonomies function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_to_clearance;
use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\report_taxonomies;
use function WC_Clearance\seed_clearance_status_taxonomy;
use const WC_Clearance\CLEARANCE_STATUS_CANONICAL_TERM;
use const WC_Clearance\CLEARANCE_STATUS_TAXONOMY;

class Test_Report_Taxonomies extends WP_UnitTestCase {

	public function test_taxonomy_registered_is_no_when_taxonomy_not_registered(): void {
		// Arrange.
		unregister_taxonomy( CLEARANCE_STATUS_TAXONOMY );

		// Act.
		$result = report_taxonomies();

		// Assert.
		$this->assertSame( 'No', $result['clearance-taxonomy-registered'][1] );
	}

	public function test_taxonomy_registered_is_yes_when_taxonomy_is_registered(): void {
		// Arrange.
		register_clearance_status_taxonomy();

		// Act.
		$result = report_taxonomies();

		// Assert.
		$this->assertSame( 'Yes', $result['clearance-taxonomy-registered'][1] );
	}

	public function test_canonical_term_id_is_not_found_when_taxonomy_not_registered(): void {
		// Arrange.
		unregister_taxonomy( CLEARANCE_STATUS_TAXONOMY );

		// Act.
		$result = report_taxonomies();

		// Assert.
		$this->assertStringContainsString( 'Canonical term not found', $result['clearance-canonical-term-id'][1] );
	}

	public function test_canonical_term_id_is_not_found_when_canonical_term_does_not_exist(): void {
		// Arrange.
		register_clearance_status_taxonomy();

		// Act.
		$result = report_taxonomies();

		// Assert.
		$this->assertStringContainsString( 'Canonical term not found', $result['clearance-canonical-term-id'][1] );
	}

	public function test_canonical_term_id_is_term_id_when_canonical_term_exists(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$term    = get_term_by( 'name', CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );
		$term_id = $term->term_id;

		// Act.
		$result = report_taxonomies();

		// Assert.
		$this->assertSame( (string) $term_id, $result['clearance-canonical-term-id'][1] );
	}

	public function test_product_count_is_unknown_when_taxonomy_not_registered(): void {
		// Arrange.
		unregister_taxonomy( CLEARANCE_STATUS_TAXONOMY );

		// Act.
		$result = report_taxonomies();

		// Assert.
		$this->assertSame( 'Unknown', $result['clearance-product-count'][1] );
	}

	public function test_product_count_is_zero_when_no_products_in_clearance(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();

		// Act.
		$result = report_taxonomies();

		// Assert.
		$this->assertSame( '0', $result['clearance-product-count'][1] );
	}

	public function test_product_count_matches_number_of_clearance_products(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();

		$product_one = \WC_Helper_Product::create_simple_product();
		$product_two = \WC_Helper_Product::create_simple_product();
		add_to_clearance( $product_one );
		add_to_clearance( $product_two );

		// Act.
		$result = report_taxonomies();

		// Assert.
		$this->assertSame( '2', $result['clearance-product-count'][1] );
	}

	public function test_product_count_ignores_draft_products(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();

		$product = \WC_Helper_Product::create_simple_product();
		$product->set_status( 'draft' );
		$product->save();
		add_to_clearance( $product );

		// Act.
		$result = report_taxonomies();

		// Assert.
		$this->assertSame( '0', $result['clearance-product-count'][1] );
	}
}
