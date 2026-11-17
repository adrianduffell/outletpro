<?php
/**
 * Outlet navigation selection tests.
 *
 * @package OutletPro
 */

use function OutletPro\select_outlet_navigation_pattern;

class Test_Select_Outlet_Navigation_Pattern extends WP_UnitTestCase {

	public function test_selects_price_tiers_without_product_categories(): void {
		// Arrange.
		$uncategorized_id = get_term_by( 'name', 'Uncategorized', 'product_cat' )->term_id ?? 0;
		delete_option( 'default_product_cat' );
		wp_delete_term( $uncategorized_id, 'product_cat' );

		// Act.
		$pattern = select_outlet_navigation_pattern();

		// Assert.
		$this->assertSame( 'outletpro/outlet-navigation-price-tiers', $pattern );
	}

	public function test_selects_price_tiers_with_one_top_level_category(): void {
		// Arrange.
		self::factory()->term->create( array( 'taxonomy' => 'product_cat' ) );

		// Act.
		$pattern = select_outlet_navigation_pattern();

		// Assert.
		$this->assertSame( 'outletpro/outlet-navigation-price-tiers', $pattern );
	}

	public function test_selects_price_tiers_with_two_top_level_categories(): void {
		// Arrange.
		self::factory()->term->create_many( 2, array( 'taxonomy' => 'product_cat' ) );

		// Act.
		$pattern = select_outlet_navigation_pattern();

		// Assert.
		$this->assertSame( 'outletpro/outlet-navigation-price-tiers', $pattern );
	}

	public function test_selects_three_tiles_with_three_top_level_categories(): void {
		// Arrange.
		self::factory()->term->create_many( 3, array( 'taxonomy' => 'product_cat' ) );

		// Act.
		$pattern = select_outlet_navigation_pattern();

		// Assert.
		$this->assertSame( 'outletpro/outlet-navigation-3', $pattern );
	}

	public function test_selects_four_tiles_with_four_top_level_categories(): void {
		// Arrange.
		self::factory()->term->create_many( 4, array( 'taxonomy' => 'product_cat' ) );

		// Act.
		$pattern = select_outlet_navigation_pattern();

		// Assert.
		$this->assertSame( 'outletpro/outlet-navigation-4', $pattern );
	}

	public function test_selects_three_tiles_with_five_top_level_categories(): void {
		// Arrange.
		self::factory()->term->create_many( 5, array( 'taxonomy' => 'product_cat' ) );

		// Act.
		$pattern = select_outlet_navigation_pattern();

		// Assert.
		$this->assertSame( 'outletpro/outlet-navigation-3', $pattern );
	}

	public function test_selects_six_tiles_with_six_top_level_categories(): void {
		// Arrange.
		self::factory()->term->create_many( 6, array( 'taxonomy' => 'product_cat' ) );

		// Act.
		$pattern = select_outlet_navigation_pattern();

		// Assert.
		$this->assertSame( 'outletpro/outlet-navigation-6', $pattern );
	}

	public function test_selects_three_tiles_with_seven_top_level_categories(): void {
		// Arrange.
		self::factory()->term->create_many( 7, array( 'taxonomy' => 'product_cat' ) );

		// Act.
		$pattern = select_outlet_navigation_pattern();

		// Assert.
		$this->assertSame( 'outletpro/outlet-navigation-3', $pattern );
	}

	public function test_selects_three_tiles_with_eight_top_level_categories(): void {
		// Arrange.
		self::factory()->term->create_many( 8, array( 'taxonomy' => 'product_cat' ) );

		// Act.
		$pattern = select_outlet_navigation_pattern();

		// Assert.
		$this->assertSame( 'outletpro/outlet-navigation-3', $pattern );
	}

	public function test_excludes_uncategorized_category(): void {
		// Arrange.
		get_term_by( 'name', 'Uncategorized', 'product_cat' )->term_id
			?? self::factory()->term->create(
				array(
					'taxonomy' => 'product_cat',
					'name'     => 'Uncategorized',
				)
			);
		self::factory()->term->create_many( 2, array( 'taxonomy' => 'product_cat' ) );

		// Act.
		$pattern = select_outlet_navigation_pattern();

		// Assert.
		$this->assertSame( 'outletpro/outlet-navigation-price-tiers', $pattern );
	}

	public function test_excludes_child_categories(): void {
		// Arrange.
		$category_ids = self::factory()->term->create_many( 3, array( 'taxonomy' => 'product_cat' ) );
		self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'parent'   => $category_ids[0],
			)
		);

		// Act.
		$pattern = select_outlet_navigation_pattern();

		// Assert.
		$this->assertSame( 'outletpro/outlet-navigation-3', $pattern );
	}

	public function test_category_lookup_errors_throw(): void {
		// Arrange.
		$failure = static fn( array $terms, array $taxonomies, array $args ) => ! empty( $args['name'] ) ? $terms : new WP_Error( 'lookup_failed', 'Category lookup failed.' );
		add_filter( 'get_terms', $failure, 10, 3 );

		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Product categories could not be retrieved.' );

		// Act.
		select_outlet_navigation_pattern();
	}
}
