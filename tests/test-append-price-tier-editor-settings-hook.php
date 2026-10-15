<?php
/**
 * Tests for append_price_tier_editor_settings_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\init_block_editor;
use function OutletPro\init_taxonomies;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Append_Price_Tier_Editor_Settings_Hook extends WP_UnitTestCase {

	public function test_settings_contain_price_tiers(): void {
		// Arrange.
		init_taxonomies();
		init_block_editor();
		update_option( 'woocommerce_currency', 'USD' );
		$term = wp_insert_term( 'outlet-price-15', OUTLET_SEARCH_INDEX_TAXONOMY );

		$expected       = new stdClass();
		$expected->id   = $term['term_id'];
		$expected->name = 'Under $15';

		// Act.
		$settings = apply_filters( 'block_editor_settings_all', array(), new WP_Block_Editor_Context() );

		// Assert.
		$this->assertEquals( $expected, $settings['outletproPriceTiers'][0] );
	}

	public function test_price_tier_query_failure_returns_empty_setting(): void {
		// Arrange.
		init_taxonomies();
		init_block_editor();
		$fail_query = static fn() => new WP_Error( 'query_failed' );
		add_filter( 'get_terms', $fail_query );

		// Act.
		$settings = apply_filters( 'block_editor_settings_all', array(), new WP_Block_Editor_Context() );
		remove_filter( 'get_terms', $fail_query );

		// Assert.
		$this->assertSame( array(), $settings['outletproPriceTiers'] );
	}
}
