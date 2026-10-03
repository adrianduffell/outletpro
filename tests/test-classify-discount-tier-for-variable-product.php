<?php
/**
 * Tests for the classify_discount_tier_for_variable_product function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\classify_discount_tier_for_variable_product;
use function OutletPro\init_taxonomies;
use function OutletPro\seed_outlet_search_index_taxonomy;
use function OutletPro\truncate_outlet_search_index_taxonomy;

class Test_Classify_Discount_Tier_For_Variable_Product extends WP_UnitTestCase {

	public function test_returns_common_discount_tier_for_different_variation_discounts(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		seed_outlet_search_index_taxonomy();
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
		$product = wc_get_product( $product->get_id() );

		// Act.
		$tier = classify_discount_tier_for_variable_product( $product );

		// Assert.
		$this->assertSame( 50, $tier );
	}

	public function test_returns_null_when_variation_discount_tiers_differ(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		seed_outlet_search_index_taxonomy();
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
		$product = wc_get_product( $product->get_id() );

		// Act.
		$tier = classify_discount_tier_for_variable_product( $product );

		// Assert.
		$this->assertNull( $tier );
	}

	public function test_returns_null_when_a_variation_has_no_matching_discount_tier(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		seed_outlet_search_index_taxonomy();
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
		$product = wc_get_product( $product->get_id() );

		// Act.
		$tier = classify_discount_tier_for_variable_product( $product );

		// Assert.
		$this->assertNull( $tier );
	}

	public function test_ignores_private_variations(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		seed_outlet_search_index_taxonomy();
		$product = new WC_Product_Variable();
		$product->save();
		$hidden = new WC_Product_Variation();
		$hidden->set_parent_id( $product->get_id() );
		$hidden->set_regular_price( '100' );
		$hidden->set_sale_price( '20' );
		$hidden->set_status( 'private' );
		$hidden->save();
		$visible = new WC_Product_Variation();
		$visible->set_parent_id( $product->get_id() );
		$visible->set_regular_price( '100' );
		$visible->set_sale_price( '40' );
		$visible->save();
		$product = wc_get_product( $product->get_id() );

		// Act.
		$tier = classify_discount_tier_for_variable_product( $product );

		// Assert.
		$this->assertSame( 50, $tier );
	}

	public function test_ignores_out_of_stock_variations_when_hidden(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_hide_out_of_stock_items', 'yes' );
		seed_outlet_search_index_taxonomy();
		$product = new WC_Product_Variable();
		$product->save();
		$hidden = new WC_Product_Variation();
		$hidden->set_parent_id( $product->get_id() );
		$hidden->set_regular_price( '100' );
		$hidden->set_sale_price( '20' );
		$hidden->set_stock_status( 'outofstock' );
		$hidden->save();
		$visible = new WC_Product_Variation();
		$visible->set_parent_id( $product->get_id() );
		$visible->set_regular_price( '100' );
		$visible->set_sale_price( '40' );
		$visible->save();
		$product = wc_get_product( $product->get_id() );

		// Act.
		$tier = classify_discount_tier_for_variable_product( $product );

		// Assert.
		$this->assertSame( 50, $tier );
	}

	public function test_ignores_unpriced_variations(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		seed_outlet_search_index_taxonomy();
		$product = new WC_Product_Variable();
		$product->save();
		$unpriced = new WC_Product_Variation();
		$unpriced->set_parent_id( $product->get_id() );
		$unpriced->save();
		$priced = new WC_Product_Variation();
		$priced->set_parent_id( $product->get_id() );
		$priced->set_regular_price( '100' );
		$priced->set_sale_price( '40' );
		$priced->save();
		$product = wc_get_product( $product->get_id() );

		// Act.
		$tier = classify_discount_tier_for_variable_product( $product );

		// Assert.
		$this->assertSame( 50, $tier );
	}

	public function test_returns_null_for_variable_product_without_variations(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		seed_outlet_search_index_taxonomy();
		$product = new WC_Product_Variable();

		// Act.
		$tier = classify_discount_tier_for_variable_product( $product );

		// Assert.
		$this->assertNull( $tier );
	}
}
