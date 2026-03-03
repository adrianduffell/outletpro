<?php
/**
 * Test the add_system_status_section function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_system_status_section;
use function WC_Clearance\register_taxonomy_for_clearance_status;
use const WC_Clearance\CLEARANCE_STATUS_CANONICAL_TERM;
use const WC_Clearance\CLEARANCE_STATUS_TAXONOMY;

/**
 * Test the add_system_status_section function.
 */
class Test_Add_System_Status_Section extends WP_UnitTestCase {

	/**
	 * Test that "yes" is shown when the taxonomy is registered.
	 */
	public function test_shows_yes_when_taxonomy_is_registered(): void {
		// Arrange.
		register_taxonomy_for_clearance_status();

		// Expect.
		$this->expectOutputRegex( '/data-testid="clearance-taxonomy-registered"[^>]*>\s*yes\s*</' );

		// Act.
		add_system_status_section();
	}

	/**
	 * Test that "no" is shown when the taxonomy is not registered.
	 */
	public function test_shows_no_when_taxonomy_is_not_registered(): void {
		// Arrange.
		register_taxonomy_for_clearance_status();
		unregister_taxonomy( CLEARANCE_STATUS_TAXONOMY );

		// Expect.
		$this->expectOutputRegex( '/data-testid="clearance-taxonomy-registered"[^>]*>\s*no\s*</' );

		// Act.
		add_system_status_section();
	}

	/**
	 * Test that the canonical term ID is shown when the term exists.
	 */
	public function test_shows_term_id_when_canonical_term_exists(): void {
		// Arrange.
		register_taxonomy_for_clearance_status();
		$result  = wp_insert_term( CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );
		$term_id = $result['term_id'];

		// Expect.
		$this->expectOutputRegex( '/data-testid="clearance-canonical-term-id"[^>]*>\s*' . preg_quote( (string) $term_id, '/' ) . '\s*</' );

		// Act.
		add_system_status_section();
	}

	/**
	 * Test that a warning is shown when the canonical term does not exist.
	 */
	public function test_shows_warning_when_canonical_term_not_found(): void {
		// Arrange.
		register_taxonomy_for_clearance_status();

		// Expect.
		$this->expectOutputRegex( '/class="error"><span>Canonical term not found\./' );

		// Act.
		add_system_status_section();
	}

	/**
	 * Test that 0 is shown when there are no products in the clearance section.
	 */
	public function test_shows_zero_when_no_products_in_clearance(): void {
		// Arrange.
		register_taxonomy_for_clearance_status();
		wp_insert_term( CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

		// Expect.
		$this->expectOutputRegex( '/data-testid="clearance-product-count"[^>]*>\s*0\s*</' );

		// Act.
		add_system_status_section();
	}

	/**
	 * Test that the correct product count is shown when products are in clearance.
	 */
	public function test_shows_correct_product_count(): void {
		// Arrange.
		register_taxonomy_for_clearance_status();
		wp_insert_term( CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );
		$term = get_term_by( 'name', CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

		$product_one = \WC_Helper_Product::create_simple_product();
		$product_two = \WC_Helper_Product::create_simple_product();

		wp_set_object_terms( $product_one->get_id(), $term->term_id, CLEARANCE_STATUS_TAXONOMY );
		wp_set_object_terms( $product_two->get_id(), $term->term_id, CLEARANCE_STATUS_TAXONOMY );

		// Expect.
		$this->expectOutputRegex( '/data-testid="clearance-product-count"[^>]*>\s*2\s*</' );

		// Act.
		add_system_status_section();
	}

	/**
	 * Test that only published products are counted.
	 */
	public function test_only_counts_published_products(): void {
		// Arrange.
		register_taxonomy_for_clearance_status();
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
		add_system_status_section();
	}

	/**
	 * Test that the output contains the section heading.
	 */
	public function test_output_contains_section_heading(): void {
		// Arrange.
		register_taxonomy_for_clearance_status();

		// Expect.
		$this->expectOutputRegex( '/Clearance Section/' );

		// Act.
		add_system_status_section();
	}
}
