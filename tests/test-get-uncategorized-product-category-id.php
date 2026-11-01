<?php
/**
 * Tests for get_uncategorized_product_category_id().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_uncategorized_product_category_id;

class Test_Get_Uncategorized_Product_Category_Id extends WP_UnitTestCase {

	public function test_returns_uncategorized_category(): void {
		// Arrange.
		$category_id = get_term_by( 'name', 'Uncategorized', 'product_cat' )->term_id
			?? self::factory()->term->create(
				array(
					'taxonomy' => 'product_cat',
					'name'     => 'Uncategorized',
				)
			);

		// Act.
		$result = get_uncategorized_product_category_id();

		// Assert.
		$this->assertSame( $category_id, $result );
	}

	public function test_returns_null_when_no_matching_slug_exists(): void {
		// Arrange.
		$category_id = get_term_by( 'name', 'Uncategorized', 'product_cat' )->term_id
			?? self::factory()->term->create(
				array(
					'taxonomy' => 'product_cat',
					'name'     => 'Uncategorized',
				)
			);
		wp_update_term( $category_id, 'product_cat', array( 'slug' => 'uncategorized-specials' ) );
		update_option( 'default_product_cat', $category_id );

		// Act.
		$result = get_uncategorized_product_category_id();

		// Assert.
		$this->assertNull( $result );
	}

	public function test_returns_translated_category_for_italian_site(): void {
		// Arrange.
		update_option( 'WPLANG', 'it_IT' );
		switch_to_locale( 'it_IT' );
		$category_id = self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'name'     => 'Senza categoria',
			)
		);

		// Act.
		$result = get_uncategorized_product_category_id();
		restore_previous_locale();

		// Assert.
		$this->assertSame( $category_id, $result );
	}

	public function test_returns_renamed_category_with_uncategorized_slug(): void {
		// Arrange.
		$category_id = get_term_by( 'slug', 'uncategorized', 'product_cat' )->term_id
			?? self::factory()->term->create(
				array(
					'taxonomy' => 'product_cat',
					'name'     => 'Uncategorized',
				)
			);
		wp_update_term( $category_id, 'product_cat', array( 'name' => 'Necklaces' ) );

		// Act.
		$result = get_uncategorized_product_category_id();

		// Assert.
		$this->assertSame( $category_id, $result );
	}

	public function test_throws_when_categories_cannot_be_retrieved(): void {
		// Arrange.
		$fail_query = static fn() => new WP_Error( 'query_failed' );
		add_filter( 'get_terms', $fail_query );

		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Product categories could not be retrieved.' );

		// Act.
		get_uncategorized_product_category_id();
	}
}
