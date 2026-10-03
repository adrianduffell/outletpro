<?php
/**
 * Tests for the classify_price_tier_for_variable_product function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\classify_price_tier_for_variable_product;
use function OutletPro\init_taxonomies;
use function OutletPro\seed_outlet_search_index_taxonomy;
use function OutletPro\truncate_outlet_search_index_taxonomy;

class Test_Classify_Price_Tier_For_Variable_Product extends WP_UnitTestCase {

	public function test_selects_smallest_price_tier_across_variations(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		seed_outlet_search_index_taxonomy();
		$product = new WC_Product_Variable();
		$product->save();
		$first = new WC_Product_Variation();
		$first->set_parent_id( $product->get_id() );
		$first->set_regular_price( '40' );
		$first->save();
		$second = new WC_Product_Variation();
		$second->set_parent_id( $product->get_id() );
		$second->set_regular_price( '20' );
		$second->save();
		$product->set_children( array( $first->get_id(), $second->get_id() ) );

		// Act.
		$tier = classify_price_tier_for_variable_product( $product );

		// Assert.
		$this->assertSame( 25, $tier );
	}

	public function test_ignores_variations_without_matching_price_tiers(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		seed_outlet_search_index_taxonomy();
		$product = new WC_Product_Variable();
		$product->save();
		$first = new WC_Product_Variation();
		$first->set_parent_id( $product->get_id() );
		$first->set_regular_price( '60' );
		$first->save();
		$second = new WC_Product_Variation();
		$second->set_parent_id( $product->get_id() );
		$second->set_regular_price( '20' );
		$second->save();
		$product->set_children( array( $first->get_id(), $second->get_id() ) );

		// Act.
		$tier = classify_price_tier_for_variable_product( $product );

		// Assert.
		$this->assertSame( 25, $tier );
	}

	public function test_returns_null_when_no_variation_matches_a_price_tier(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		seed_outlet_search_index_taxonomy();
		$product = new WC_Product_Variable();
		$product->save();
		$first = new WC_Product_Variation();
		$first->set_parent_id( $product->get_id() );
		$first->set_regular_price( '60' );
		$first->save();
		$second = new WC_Product_Variation();
		$second->set_parent_id( $product->get_id() );
		$second->set_regular_price( '80' );
		$second->save();
		$product->set_children( array( $first->get_id(), $second->get_id() ) );

		// Act.
		$tier = classify_price_tier_for_variable_product( $product );

		// Assert.
		$this->assertNull( $tier );
	}

	public function test_returns_null_for_variable_product_without_variations(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		seed_outlet_search_index_taxonomy();
		$product = new WC_Product_Variable();

		// Act.
		$tier = classify_price_tier_for_variable_product( $product );

		// Assert.
		$this->assertNull( $tier );
	}
}
