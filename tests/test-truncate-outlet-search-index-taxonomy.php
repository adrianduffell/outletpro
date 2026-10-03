<?php
/**
 * Tests for truncate_outlet_search_index_taxonomy.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_taxonomies;
use function OutletPro\register_outlet_search_index_taxonomy;
use function OutletPro\seed_outlet_search_index_taxonomy;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Truncate_Outlet_Search_Index_Taxonomy extends WP_UnitTestCase {

	public function test_removes_all_terms(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		seed_outlet_search_index_taxonomy();
		wp_insert_term( 'custom', OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		truncate_outlet_search_index_taxonomy();

		// Assert.
		$this->assertSame(
			array(),
			get_terms(
				array(
					'taxonomy'   => OUTLET_SEARCH_INDEX_TAXONOMY,
					'hide_empty' => false,
					'fields'     => 'slugs',
				)
			)
		);
	}

	public function test_throws_when_taxonomy_is_missing(): void {
		// Arrange.
		deinit_taxonomies();

		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Outlet search index terms could not be retrieved.' );

		// Act.
		truncate_outlet_search_index_taxonomy();
	}
}
