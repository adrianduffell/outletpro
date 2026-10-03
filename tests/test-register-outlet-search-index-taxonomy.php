<?php
/**
 * Tests for the register_outlet_search_index_taxonomy function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_taxonomies;
use function OutletPro\register_outlet_search_index_taxonomy;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Register_Outlet_Search_Index_Taxonomy extends WP_UnitTestCase {

	public function test_registers_outlet_search_index_taxonomy(): void {
		// Arrange.
		deinit_taxonomies();

		// Act.
		register_outlet_search_index_taxonomy();

		// Assert.
		$this->assertTrue( taxonomy_exists( OUTLET_SEARCH_INDEX_TAXONOMY ) );
	}

	public function test_registers_taxonomy_for_products(): void {
		// Arrange.
		deinit_taxonomies();

		// Act.
		register_outlet_search_index_taxonomy();

		// Assert.
		$this->assertContains( 'product', get_taxonomy( OUTLET_SEARCH_INDEX_TAXONOMY )->object_type );
	}
}
