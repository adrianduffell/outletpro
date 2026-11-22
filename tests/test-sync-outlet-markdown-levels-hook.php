<?php
/**
 * Tests for sync_outlet_markdown_levels_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\batch_discount_tiers;
use function OutletPro\deinit_settings;
use function OutletPro\get_discount_tiers;
use function OutletPro\get_price_tiers;
use function OutletPro\init_settings;
use function OutletPro\init_taxonomies;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use function OutletPro\update_discount_tier;
use const OutletPro\OUTLET_MARKDOWN_LEVELS_OPTION;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Sync_Outlet_Markdown_Levels_Hook extends WP_UnitTestCase {

	public function test_creates_facets_when_option_is_added(): void {
		// Arrange.
		init_taxonomies();
		deinit_settings();
		init_settings();
		delete_option( OUTLET_MARKDOWN_LEVELS_OPTION );
		truncate_outlet_search_index_taxonomy();

		// Act.
		add_option( OUTLET_MARKDOWN_LEVELS_OPTION, array( 30, 70 ) );

		// Assert.
		$this->assertSame( array( 30, 70 ), get_discount_tiers() );
	}

	public function test_deleting_option_removes_only_discount_facets(): void {
		// Arrange.
		init_taxonomies();
		init_settings();
		truncate_outlet_search_index_taxonomy();
		delete_option( OUTLET_MARKDOWN_LEVELS_OPTION );
		update_option( OUTLET_MARKDOWN_LEVELS_OPTION, array( 30, 70 ) );
		wp_insert_term( 'outlet-price-25', OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		delete_option( OUTLET_MARKDOWN_LEVELS_OPTION );

		// Assert.
		$this->assertSame( array(), get_discount_tiers() );
		$this->assertSame( array( 25 ), get_price_tiers() );
	}

	public function test_changed_option_reconciles_current_facets(): void {
		// Arrange.
		init_taxonomies();
		init_settings();
		truncate_outlet_search_index_taxonomy();
		delete_option( OUTLET_MARKDOWN_LEVELS_OPTION );
		update_option( OUTLET_MARKDOWN_LEVELS_OPTION, array( 30, 50, 70 ) );
		update_discount_tier( 50, 60 );
		batch_discount_tiers( array( 20, 90 ), array(), array( 30 ) );

		// Act.
		update_option( OUTLET_MARKDOWN_LEVELS_OPTION, array( 30, 50, 80 ) );

		// Assert.
		$this->assertSame( array( 30, 50, 80 ), get_discount_tiers() );
	}

	public function test_unsorted_levels_preserve_renamed_facet_id(): void {
		// Arrange.
		init_taxonomies();
		init_settings();
		truncate_outlet_search_index_taxonomy();
		delete_option( OUTLET_MARKDOWN_LEVELS_OPTION );
		update_option( OUTLET_MARKDOWN_LEVELS_OPTION, array( 30, 50, 70 ) );
		$term   = get_term_by( 'slug', 'outlet-discount-70', OUTLET_SEARCH_INDEX_TAXONOMY );
		$values = array( 80, 50, 30 );

		// Act.
		update_option( OUTLET_MARKDOWN_LEVELS_OPTION, $values );

		// Assert.
		$this->assertSame( $term->term_id, get_term_by( 'slug', 'outlet-discount-80', OUTLET_SEARCH_INDEX_TAXONOMY )->term_id );
	}

	public function test_empty_list_removes_only_discount_facets(): void {
		// Arrange.
		init_taxonomies();
		init_settings();
		truncate_outlet_search_index_taxonomy();
		delete_option( OUTLET_MARKDOWN_LEVELS_OPTION );
		update_option( OUTLET_MARKDOWN_LEVELS_OPTION, array( 30, 50, 70 ) );
		wp_insert_term( 'outlet-price-25', OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		update_option( OUTLET_MARKDOWN_LEVELS_OPTION, array() );

		// Assert.
		$this->assertSame( array(), get_discount_tiers() );
		$this->assertSame( array( 25 ), get_price_tiers() );
	}

	public function test_failed_synchronization_does_not_interrupt_option_update(): void {
		// Arrange.
		init_taxonomies();
		init_settings();
		update_option( OUTLET_MARKDOWN_LEVELS_OPTION, array( 30 ) );
		unregister_taxonomy( OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		update_option( OUTLET_MARKDOWN_LEVELS_OPTION, array( 50 ) );

		// Assert.
		$this->assertSame( array( 50 ), get_option( OUTLET_MARKDOWN_LEVELS_OPTION ) );
	}
}
