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
	 * Ensure the taxonomy is registered and clean before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		register_taxonomy_for_clearance_status();

		foreach (
			get_terms(
				array(
					'taxonomy'   => CLEARANCE_STATUS_TAXONOMY,
					'hide_empty' => false,
				)
			) as $term
		) {
			wp_delete_term( $term->term_id, CLEARANCE_STATUS_TAXONOMY );
		}
	}

	/**
	 * Re-register the taxonomy after any test that may have unregistered it.
	 */
	public function tearDown(): void {
		if ( ! taxonomy_exists( CLEARANCE_STATUS_TAXONOMY ) ) {
			register_taxonomy_for_clearance_status();
		}
		parent::tearDown();
	}

	/**
	 * Test that "yes" is shown when the taxonomy is registered.
	 */
	public function test_shows_yes_when_taxonomy_is_registered(): void {
		// Arrange: taxonomy is registered by setUp.

		// Act.
		ob_start();
		add_system_status_section();
		$output = ob_get_clean();

		// Assert.
		$this->assertStringContainsString( 'clearance-taxonomy-registered', $output );
		$this->assertMatchesRegularExpression(
			'/data-testid="clearance-taxonomy-registered"[^>]*>\s*yes\s*</',
			$output
		);
	}

	/**
	 * Test that "no" is shown when the taxonomy is not registered.
	 */
	public function test_shows_no_when_taxonomy_is_not_registered(): void {
		// Arrange.
		unregister_taxonomy( CLEARANCE_STATUS_TAXONOMY );

		// Act.
		ob_start();
		add_system_status_section();
		$output = ob_get_clean();

		// Assert.
		$this->assertMatchesRegularExpression(
			'/data-testid="clearance-taxonomy-registered"[^>]*>\s*no\s*</',
			$output
		);
	}

	/**
	 * Test that the canonical term ID is shown when the term exists.
	 */
	public function test_shows_term_id_when_canonical_term_exists(): void {
		// Arrange.
		$result = wp_insert_term( CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );
		$term_id = $result['term_id'];

		// Act.
		ob_start();
		add_system_status_section();
		$output = ob_get_clean();

		// Assert.
		$this->assertMatchesRegularExpression(
			'/data-testid="clearance-canonical-term-id"[^>]*>\s*' . $term_id . '\s*</',
			$output
		);
		$this->assertStringNotContainsString( 'Canonical term not found.', $output );
	}

	/**
	 * Test that a warning is shown when the canonical term does not exist.
	 */
	public function test_shows_warning_when_canonical_term_not_found(): void {
		// Arrange: no terms seeded (setUp cleared them).

		// Act.
		ob_start();
		add_system_status_section();
		$output = ob_get_clean();

		// Assert.
		$this->assertStringContainsString( 'Canonical term not found.', $output );
		$this->assertStringContainsString( 'class="error"', $output );
	}

	/**
	 * Test that 0 is shown when there are no products in the clearance section.
	 */
	public function test_shows_zero_when_no_products_in_clearance(): void {
		// Arrange.
		wp_insert_term( CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

		// Act.
		ob_start();
		add_system_status_section();
		$output = ob_get_clean();

		// Assert.
		$this->assertMatchesRegularExpression(
			'/data-testid="clearance-product-count"[^>]*>\s*0\s*</',
			$output
		);
	}

	/**
	 * Test that the correct product count is shown when products are in clearance.
	 */
	public function test_shows_correct_product_count(): void {
		// Arrange.
		wp_insert_term( CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );
		$term = get_term_by( 'name', CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

		$product_one = \WC_Helper_Product::create_simple_product();
		$product_two = \WC_Helper_Product::create_simple_product();

		wp_set_object_terms( $product_one->get_id(), $term->term_id, CLEARANCE_STATUS_TAXONOMY );
		wp_set_object_terms( $product_two->get_id(), $term->term_id, CLEARANCE_STATUS_TAXONOMY );

		// Act.
		ob_start();
		add_system_status_section();
		$output = ob_get_clean();

		// Assert.
		$this->assertMatchesRegularExpression(
			'/data-testid="clearance-product-count"[^>]*>\s*2\s*</',
			$output
		);
	}

	/**
	 * Test that only published products are counted.
	 */
	public function test_only_counts_published_products(): void {
		// Arrange.
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

		// Act.
		ob_start();
		add_system_status_section();
		$output = ob_get_clean();

		// Assert.
		$this->assertMatchesRegularExpression(
			'/data-testid="clearance-product-count"[^>]*>\s*1\s*</',
			$output
		);
	}

	/**
	 * Test that the output contains the section heading.
	 */
	public function test_output_contains_section_heading(): void {
		// Act.
		ob_start();
		add_system_status_section();
		$output = ob_get_clean();

		// Assert.
		$this->assertStringContainsString( 'Clearance Section', $output );
	}
}
