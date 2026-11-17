<?php
/**
 * Outlet navigation selection tests.
 *
 * @package OutletPro
 */

use function OutletPro\get_uncategorized_product_category_id;
use function OutletPro\select_outlet_navigation_pattern;

class Test_Select_Outlet_Navigation_Pattern extends WP_UnitTestCase {

	public function test_navigation_matches_top_level_category_counts(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Exercise each category-count boundary with independently arranged categories.
		// Arrange.
		$uncategorized = get_uncategorized_product_category_id() ?? self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'name'     => 'Uncategorized',
			)
		);
		update_option( 'default_product_cat', $uncategorized );
		$categories = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'fields'     => 'ids',
				'exclude'    => array( $uncategorized ),
			)
		);
		foreach ( $categories as $category ) {
			wp_delete_term( $category, 'product_cat' );
		}
		self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'parent'   => $uncategorized,
			)
		);
		$expected = array( 'price-tiers', 'price-tiers', 'price-tiers', '3', '4', '3', '6', '3', '3' );
		foreach ( $expected as $count => $suffix ) {
			if ( 0 < $count ) {
				self::factory()->term->create( array( 'taxonomy' => 'product_cat' ) );
			}

			// Act.
			$pattern = select_outlet_navigation_pattern();

			// Assert.
			$this->assertSame( 'outletpro/outlet-navigation-' . $suffix, $pattern );
		}
	}

	public function test_category_lookup_errors_throw(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Remove the query filter even when the expected exception is thrown.
		// Arrange.
		$failure = static fn( array $terms, array $taxonomies, array $args ) => ! empty( $args['name'] ) ? $terms : new WP_Error( 'lookup_failed', 'Category lookup failed.' );
		add_filter( 'get_terms', $failure, 10, 3 );

		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Product categories could not be retrieved.' );

		// Act.
		try {
			select_outlet_navigation_pattern();
		} finally {
			remove_filter( 'get_terms', $failure );
		}
	}
}
