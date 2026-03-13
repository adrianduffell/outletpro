<?php
/**
 * Test the add_system_status_section_hook function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_system_status_section_hook;
use function WC_Clearance\register_clearance_status_taxonomy;
use const WC_Clearance\CLEARANCE_STATUS_CANONICAL_TERM;
use const WC_Clearance\CLEARANCE_STATUS_TAXONOMY;

class Test_Add_System_Status_Section extends WP_UnitTestCase {

	public function test_shows_yes_when_taxonomy_is_registered(): void {
		// Arrange.
		register_clearance_status_taxonomy();

		// Expect.
		$this->expectOutputRegex( '/data-testid="clearance-taxonomy-registered"[^>]*>\s*Yes\s*</' );

		// Act.
		add_system_status_section_hook();
	}

	public function test_shows_no_when_taxonomy_is_not_registered(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		unregister_taxonomy( CLEARANCE_STATUS_TAXONOMY );

		// Expect.
		$this->expectOutputRegex( '/data-testid="clearance-taxonomy-registered"[^>]*>\s*No\s*</' );

		// Act.
		add_system_status_section_hook();
	}

	public function test_shows_term_id_when_canonical_term_exists(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$result  = wp_insert_term( CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );
		$term_id = $result['term_id'];

		// Expect.
		$this->expectOutputRegex( '/data-testid="clearance-canonical-term-id"[^>]*>\s*' . preg_quote( (string) $term_id, '/' ) . '\s*</' );

		// Act.
		add_system_status_section_hook();
	}

	public function test_shows_warning_when_canonical_term_not_found(): void {
		// Arrange.
		register_clearance_status_taxonomy();

		// Expect.
		$this->expectOutputRegex( '/class="error"><span>Canonical term not found\./' );

		// Act.
		add_system_status_section_hook();
	}

	public function test_shows_zero_when_no_products_in_clearance(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		wp_insert_term( CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

		// Expect.
		$this->expectOutputRegex( '/data-testid="clearance-product-count"[^>]*>\s*0\s*</' );

		// Act.
		add_system_status_section_hook();
	}

	public function test_shows_correct_product_count(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		wp_insert_term( CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );
		$term = get_term_by( 'name', CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

		$product_one = \WC_Helper_Product::create_simple_product();
		$product_two = \WC_Helper_Product::create_simple_product();

		wp_set_object_terms( $product_one->get_id(), $term->term_id, CLEARANCE_STATUS_TAXONOMY );
		wp_set_object_terms( $product_two->get_id(), $term->term_id, CLEARANCE_STATUS_TAXONOMY );

		// Expect.
		$this->expectOutputRegex( '/data-testid="clearance-product-count"[^>]*>\s*2\s*</' );

		// Act.
		add_system_status_section_hook();
	}

	public function test_only_counts_published_products(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		wp_insert_term( CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );
		$term = get_term_by( 'name', CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

		$published_product = \WC_Helper_Product::create_simple_product();
		$draft_product     = \WC_Helper_Product::create_simple_product();
		wp_update_post(
			array(
				'ID'          => $draft_product->get_id(),
				'post_status' => 'draft',
			)
		);

		wp_set_object_terms( $published_product->get_id(), $term->term_id, CLEARANCE_STATUS_TAXONOMY );
		wp_set_object_terms( $draft_product->get_id(), $term->term_id, CLEARANCE_STATUS_TAXONOMY );

		// Expect.
		$this->expectOutputRegex( '/data-testid="clearance-product-count"[^>]*>\s*1\s*</' );

		// Act.
		add_system_status_section_hook();
	}

	public function test_output_contains_section_heading(): void {
		// Arrange.
		register_clearance_status_taxonomy();

		// Expect.
		$this->expectOutputRegex( '/Clearance Section/' );

		// Act.
		add_system_status_section_hook();
	}

	public function test_table_has_correct_css_class(): void {
		// Arrange.
		register_clearance_status_taxonomy();

		// Expect.
		$this->expectOutputRegex( '/<table[^>]*class="(?=[^"]*\bwc_status_table\b)(?=[^"]*\bwidefat\b)[^"]*"/' );

		// Act.
		add_system_status_section_hook();
	}

	public function test_table_has_thead_and_tbody(): void {
		// Arrange.
		register_clearance_status_taxonomy();

		// Expect.
		$this->expectOutputRegex( '/<table[^>]*>.*?<thead>.*?<\/thead>.*?<tbody>.*?<\/tbody>.*?<\/table>/s' );

		// Act.
		add_system_status_section_hook();
	}
}
