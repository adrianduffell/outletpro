<?php
/**
 * Tests for the classify_discount_tier_for_variable_product function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\classify_discount_tier_for_variable_product;
use function OutletPro\register_outlet_search_index_taxonomy;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Classify_Discount_Tier_For_Variable_Product extends WP_UnitTestCase {

	public function test_returns_common_discount_tier_for_different_variation_discounts(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-discount-30', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-discount-50', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-discount-70', OUTLET_SEARCH_INDEX_TAXONOMY );
		$product = new WC_Product_Variable();
		$product->save();
		$first = new WC_Product_Variation();
		$first->set_parent_id( $product->get_id() );
		$first->set_regular_price( '100' );
		$first->set_sale_price( '40' );
		$first->save();
		$second = new WC_Product_Variation();
		$second->set_parent_id( $product->get_id() );
		$second->set_regular_price( '100' );
		$second->set_sale_price( '45' );
		$second->save();
		$product->set_children( array( $first->get_id(), $second->get_id() ) );

		// Act.
		$tier = classify_discount_tier_for_variable_product( $product );

		// Assert.
		$this->assertSame( 50, $tier );
	}

	public function test_returns_null_when_variation_discount_tiers_differ(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-discount-30', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-discount-50', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-discount-70', OUTLET_SEARCH_INDEX_TAXONOMY );
		$product = new WC_Product_Variable();
		$product->save();
		$first = new WC_Product_Variation();
		$first->set_parent_id( $product->get_id() );
		$first->set_regular_price( '100' );
		$first->set_sale_price( '20' );
		$first->save();
		$second = new WC_Product_Variation();
		$second->set_parent_id( $product->get_id() );
		$second->set_regular_price( '100' );
		$second->set_sale_price( '40' );
		$second->save();
		$product->set_children( array( $first->get_id(), $second->get_id() ) );

		// Act.
		$tier = classify_discount_tier_for_variable_product( $product );

		// Assert.
		$this->assertNull( $tier );
	}

	public function test_returns_null_when_a_variation_has_no_matching_discount_tier(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-discount-30', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-discount-50', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-discount-70', OUTLET_SEARCH_INDEX_TAXONOMY );
		$product = new WC_Product_Variable();
		$product->save();
		$first = new WC_Product_Variation();
		$first->set_parent_id( $product->get_id() );
		$first->set_regular_price( '100' );
		$first->set_sale_price( '40' );
		$first->save();
		$second = new WC_Product_Variation();
		$second->set_parent_id( $product->get_id() );
		$second->set_regular_price( '100' );
		$second->set_sale_price( '80' );
		$second->save();
		$product->set_children( array( $first->get_id(), $second->get_id() ) );

		// Act.
		$tier = classify_discount_tier_for_variable_product( $product );

		// Assert.
		$this->assertNull( $tier );
	}

	public function test_returns_null_for_variable_product_without_variations(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-discount-30', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-discount-50', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-discount-70', OUTLET_SEARCH_INDEX_TAXONOMY );
		$product = new WC_Product_Variable();

		// Act.
		$tier = classify_discount_tier_for_variable_product( $product );

		// Assert.
		$this->assertNull( $tier );
	}
}
