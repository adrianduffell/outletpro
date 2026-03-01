<?php
/**
 * Test the seed_clearance_status_taxonomy function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\register_taxonomy_for_clearance_status;
use function WC_Clearance\seed_clearance_status_taxonomy;
use const WC_Clearance\CLEARANCE_STATUS_CANONICAL_TERM;
use const WC_Clearance\CLEARANCE_STATUS_TAXONOMY;

/**
 * Test the seed_clearance_status_taxonomy function.
 */
class Test_Seed_Clearance_Status_Taxonomy extends WP_UnitTestCase {

	/**
	 * Test the term is created when it doesn't exist.
	 */
	public function test_seeds_term_when_not_exists(): void {
		// Arrange.
		register_taxonomy_for_clearance_status();
		foreach ( get_terms(
			array(
				'taxonomy'   => CLEARANCE_STATUS_TAXONOMY,
				'hide_empty' => false,
			)
		) as $term ) {
			wp_delete_term( $term->term_id, CLEARANCE_STATUS_TAXONOMY );
		}

		// Act.
		seed_clearance_status_taxonomy();

		// Assert.
		$terms = get_terms(
			array(
				'taxonomy'   => CLEARANCE_STATUS_TAXONOMY,
				'hide_empty' => false,
			)
		);
		$this->assertCount( 1, $terms );
		$this->assertSame( CLEARANCE_STATUS_CANONICAL_TERM, $terms[0]->name );
	}

	/**
	 * Test a duplicate term isn't created when it already exists.
	 */
	public function test_does_not_seed_term_when_already_exists(): void {
		// Arrange.
		register_taxonomy_for_clearance_status();
		wp_insert_term( CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

		// Act.
		seed_clearance_status_taxonomy();

		// Assert.
		$terms = get_terms(
			array(
				'taxonomy'   => CLEARANCE_STATUS_TAXONOMY,
				'hide_empty' => false,
			)
		);
		$this->assertCount( 1, $terms );
		$this->assertSame( CLEARANCE_STATUS_CANONICAL_TERM, $terms[0]->name );
	}
}
