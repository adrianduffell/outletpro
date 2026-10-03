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

		self::create_variation( $product, '100', '40' );
		self::create_variation( $product, '100', '45' );
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

		self::create_variation( $product, '100', '20' );
		self::create_variation( $product, '100', '40' );
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

		self::create_variation( $product, '100', '40' );
		self::create_variation( $product, '100', '80' );
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

		$hidden = self::create_variation( $product, '100', '20' );
		$hidden->set_status( 'private' );
		$hidden->save();

		self::create_variation( $product, '100', '40' );
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

		$hidden = self::create_variation( $product, '100', '20' );
		$hidden->set_stock_status( 'outofstock' );
		$hidden->save();

		self::create_variation( $product, '100', '40' );
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

		self::create_variation( $product, '', '' );
		self::create_variation( $product, '100', '40' );
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

	private static function create_variation( WC_Product_Variable $product, string $regular_price, string $sale_price ): WC_Product_Variation {
		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $product->get_id() );
		$variation->set_regular_price( $regular_price );
		$variation->set_sale_price( $sale_price );
		$variation->save();

		return $variation;
	}
}
