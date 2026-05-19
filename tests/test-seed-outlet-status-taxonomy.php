<?php
/**
 * Test the seed_outlet_status_taxonomy function.
 *
 * @package WC_Outlet
 */

use function WC_Outlet\register_outlet_status_taxonomy;
use function WC_Outlet\seed_outlet_status_taxonomy;
use const WC_Outlet\OUTLET_STATUS_CANONICAL_TERM;
use const WC_Outlet\OUTLET_STATUS_TAXONOMY;

class Test_Seed_Outlet_Status_Taxonomy extends WP_UnitTestCase {

	public function test_seeds_term_when_not_exists(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		foreach ( get_terms(
			array(
				'taxonomy'   => OUTLET_STATUS_TAXONOMY,
				'hide_empty' => false,
			)
		) as $term ) {
			wp_delete_term( $term->term_id, OUTLET_STATUS_TAXONOMY );
		}

		// Act.
		seed_outlet_status_taxonomy();

		// Assert.
		$terms = get_terms(
			array(
				'taxonomy'   => OUTLET_STATUS_TAXONOMY,
				'hide_empty' => false,
			)
		);
		$this->assertCount( 1, $terms );
		$this->assertSame( OUTLET_STATUS_CANONICAL_TERM, $terms[0]->name );
	}

	public function test_does_not_seed_term_when_already_exists(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		wp_insert_term( OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY );

		// Act.
		seed_outlet_status_taxonomy();

		// Assert.
		$terms = get_terms(
			array(
				'taxonomy'   => OUTLET_STATUS_TAXONOMY,
				'hide_empty' => false,
			)
		);
		$this->assertCount( 1, $terms );
		$this->assertSame( OUTLET_STATUS_CANONICAL_TERM, $terms[0]->name );
	}

	public function test_throws_exception_when_wp_insert_term_fails(): void {
		// Arrange.
		register_outlet_status_taxonomy();

		// Ensure the taxonomy has no existing terms, to mirror the happy-path setup.
		foreach ( get_terms(
			array(
				'taxonomy'   => OUTLET_STATUS_TAXONOMY,
				'hide_empty' => false,
			)
		) as $term ) {
			wp_delete_term( $term->term_id, OUTLET_STATUS_TAXONOMY );
		}

		// Force wp_insert_term to return a WP_Error for the canonical term.
		$callback = static function ( $term, $taxonomy ) {
			if ( OUTLET_STATUS_CANONICAL_TERM === $term && OUTLET_STATUS_TAXONOMY === $taxonomy ) {
				return new \WP_Error( 'test_wp_insert_term_error', 'Simulated wp_insert_term failure.' );
			}

			return $term;
		};

		add_filter( 'pre_insert_term', $callback, 10, 2 );

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		seed_outlet_status_taxonomy();
	}
}
