<?php
/**
 * Tests for the deinit_taxonomies function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_taxonomies;
use function OutletPro\init_taxonomies;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;
use const OutletPro\OUTLET_STATUS_TAXONOMY;

class Test_Deinit_Taxonomies extends WP_UnitTestCase {

	public function test_unregisters_outlet_status_taxonomy(): void {
		// Arrange.
		init_taxonomies();

		// Act.
		deinit_taxonomies();

		// Assert.
		$this->assertFalse( taxonomy_exists( OUTLET_STATUS_TAXONOMY ) );
	}

	public function test_unregisters_outlet_search_index_taxonomy(): void {
		// Arrange.
		init_taxonomies();

		// Act.
		deinit_taxonomies();

		// Assert.
		$this->assertFalse( taxonomy_exists( OUTLET_SEARCH_INDEX_TAXONOMY ) );
	}

	public function test_preserves_unrelated_taxonomy(): void {
		// Arrange.
		init_taxonomies();
		register_taxonomy( 'unrelated', 'product' );

		// Act.
		deinit_taxonomies();

		// Assert.
		$this->assertTrue( taxonomy_exists( 'unrelated' ) );
	}
}
