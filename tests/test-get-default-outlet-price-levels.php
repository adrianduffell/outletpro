<?php
/**
 * Tests for get_default_outlet_price_levels().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_default_outlet_price_levels;

class Test_Get_Default_Outlet_Price_Levels extends WP_UnitTestCase {

	public function test_returns_null_for_an_empty_catalog(): void {
		// Act.
		$levels = get_default_outlet_price_levels();

		// Assert.
		$this->assertNull( $levels );
	}

	public function test_uses_regular_prices(): void {
		// Arrange.
		WC_Helper_Product::create_simple_product(
			true,
			array(
				'regular_price' => '70',
				'sale_price'    => '1',
				'price'         => '1',
			)
		);

		// Act.
		$levels = get_default_outlet_price_levels();

		// Assert.
		$this->assertSame( array( 10, 25, 50, 100 ), $levels );
	}

	public function test_rounds_ties_up(): void {
		// Arrange.
		WC_Helper_Product::create_simple_product( true, array( 'regular_price' => '100' ) );

		// Act.
		$levels = get_default_outlet_price_levels();

		// Assert.
		$this->assertSame( array( 20, 40, 75, 100 ), $levels );
	}

	public function test_uses_the_middle_price_for_an_odd_catalog(): void {
		// Arrange.
		array_map( static fn( string $price ): WC_Product => WC_Helper_Product::create_simple_product( true, array( 'regular_price' => $price ) ), array( '100', '100', '1000' ) );

		// Act.
		$levels = get_default_outlet_price_levels();

		// Assert.
		$this->assertSame( array( 20, 40, 75, 100 ), $levels );
	}

	public function test_averages_the_middle_prices_for_an_even_catalog(): void {
		// Arrange.
		WC_Helper_Product::create_simple_product( true, array( 'regular_price' => '140' ) );
		WC_Helper_Product::create_simple_product( true, array( 'regular_price' => '60' ) );

		// Act.
		$levels = get_default_outlet_price_levels();

		// Assert.
		$this->assertSame( array( 20, 40, 75, 100 ), $levels );
	}

	public function test_returns_four_distinct_levels_when_rounding_would_create_duplicates(): void {
		// Arrange.
		WC_Helper_Product::create_simple_product( true, array( 'regular_price' => '14.99' ) );

		// Act.
		$levels = get_default_outlet_price_levels();

		// Assert.
		$this->assertSame( array( 10, 15, 20, 25 ), $levels );
	}

	public function test_returns_distinct_levels_for_overlapping_raw_levels(): void {
		// Arrange.
		WC_Helper_Product::create_simple_product( true, array( 'regular_price' => '22000' ) );

		// Act.
		$levels = get_default_outlet_price_levels();

		// Assert.
		$this->assertSame( array( 3000, 5000, 10000, 30000 ), $levels );
	}

	public function test_returns_four_distinct_levels_when_prices_exceed_the_allowed_levels(): void {
		// Arrange.
		WC_Helper_Product::create_simple_product( true, array( 'regular_price' => '20000000' ) );

		// Act.
		$levels = get_default_outlet_price_levels();

		// Assert.
		$this->assertSame( array( 1000000, 2500000, 5000000, 10000000 ), $levels );
	}

	public function test_returns_null_when_no_prices_are_eligible(): void {
		// Arrange.
		array_map( static fn( string $price ): WC_Product => WC_Helper_Product::create_simple_product( true, array( 'regular_price' => $price ) ), array( '', '0', '-100', '0.99' ) );

		// Act.
		$levels = get_default_outlet_price_levels();

		// Assert.
		$this->assertNull( $levels );
	}

	public function test_rounds_prices_to_allowed_levels(): void {
		// Arrange.
		WC_Helper_Product::create_simple_product( true, array( 'regular_price' => '1142.86' ) );

		// Act.
		$levels = get_default_outlet_price_levels();

		// Assert.
		$this->assertSame( array( 200, 400, 1000, 2000 ), $levels );
	}

	public function test_includes_variation_prices_in_the_median(): void {
		// Arrange.
		$parent = new WC_Product_Variable();
		$parent->save();
		WC_Helper_Product::create_product_variation_object( $parent->get_id(), 'price-tier-100', '100', array() );
		WC_Helper_Product::create_product_variation_object( $parent->get_id(), 'price-tier-1000', '1000', array() );
		WC_Product_Variable::sync( $parent->get_id() );
		WC_Helper_Product::create_simple_product( true, array( 'regular_price' => '300' ) );

		// Act.
		$levels = get_default_outlet_price_levels();

		// Assert.
		$this->assertSame( array( 50, 100, 200, 400 ), $levels );
	}

	public function test_counts_equally_priced_variations_individually(): void {
		// Arrange.
		$parent = new WC_Product_Variable();
		$parent->save();
		WC_Helper_Product::create_product_variation_object( $parent->get_id(), 'price-tier-first', '100', array() );
		WC_Helper_Product::create_product_variation_object( $parent->get_id(), 'price-tier-second', '100', array() );
		WC_Helper_Product::create_product_variation_object( $parent->get_id(), 'price-tier-third', '100', array() );
		WC_Product_Variable::sync( $parent->get_id() );
		WC_Helper_Product::create_simple_product( true, array( 'regular_price' => '1000' ) );

		// Act.
		$levels = get_default_outlet_price_levels();

		// Assert.
		$this->assertSame( array( 20, 40, 75, 100 ), $levels );
	}

	public function test_includes_hidden_products(): void {
		// Arrange.
		WC_Helper_Product::create_simple_product(
			true,
			array(
				'regular_price'      => '100',
				'catalog_visibility' => 'hidden',
			)
		);

		// Act.
		$levels = get_default_outlet_price_levels();

		// Assert.
		$this->assertSame( array( 20, 40, 75, 100 ), $levels );
	}

	public function test_includes_out_of_stock_products(): void {
		// Arrange.
		WC_Helper_Product::create_simple_product(
			true,
			array(
				'regular_price' => '100',
				'stock_status'  => 'outofstock',
			)
		);

		// Act.
		$levels = get_default_outlet_price_levels();

		// Assert.
		$this->assertSame( array( 20, 40, 75, 100 ), $levels );
	}

	public function test_excludes_unpublished_products(): void {
		// Arrange.
		WC_Helper_Product::create_simple_product( true, array( 'regular_price' => '100' ) );
		array_map(
			static fn( string $status ): WC_Product => WC_Helper_Product::create_simple_product(
				true,
				array(
					'status'        => $status,
					'regular_price' => '10000',
				)
			),
			array( 'draft', 'private', 'trash' )
		);

		// Act.
		$levels = get_default_outlet_price_levels();

		// Assert.
		$this->assertSame( array( 20, 40, 75, 100 ), $levels );
	}

	public function test_limits_the_random_priced_item_sample_to_200(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Observe the sampled result without mocking the WooCommerce query.
		// Arrange.
		$product_ids = self::factory()->post->create_many(
			205,
			array(
				'post_type'   => 'product',
				'post_status' => 'publish',
				'meta_input'  => array(
					'_regular_price' => '100',
					'_price'         => '100',
				),
			)
		);
		array_map( static fn( int $id ): array => wp_set_object_terms( $id, 'simple', 'product_type' ), $product_ids );
		$sample_count   = 0;
		$observe_sample = static function ( array $products ) use ( &$sample_count ): array {
			$sample_count = count( $products );
			return $products;
		};
		add_filter( 'woocommerce_product_object_query', $observe_sample );

		// Act.
		get_default_outlet_price_levels();
		remove_filter( 'woocommerce_product_object_query', $observe_sample );

		// Assert.
		$this->assertSame( 200, $sample_count );
	}

	public function test_only_samples_products_with_regular_prices_at_least_one(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Observe the sampled result without mocking the WooCommerce query.
		// Arrange.
		WC_Helper_Product::create_simple_product( true, array( 'regular_price' => '100' ) );
		WC_Helper_Product::create_simple_product( true, array( 'regular_price' => '1' ) );
		array_map( static fn( string $price ): WC_Product => WC_Helper_Product::create_simple_product( true, array( 'regular_price' => $price ) ), array( '0', '-100', '0.99' ) );
		WC_Helper_Product::create_simple_product(
			true,
			array(
				'regular_price' => '',
				'price'         => '',
			)
		);
		$sample_count   = 0;
		$observe_sample = static function ( array $products ) use ( &$sample_count ): array {
			$sample_count = count( $products );
			return $products;
		};
		add_filter( 'woocommerce_product_object_query', $observe_sample );

		// Act.
		get_default_outlet_price_levels();
		remove_filter( 'woocommerce_product_object_query', $observe_sample );

		// Assert.
		$this->assertSame( 2, $sample_count );
	}
}
