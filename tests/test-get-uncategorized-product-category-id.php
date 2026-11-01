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

	public function test_returns_null_when_no_matching_name_exists(): void {
		// Arrange.
		$category_id = get_term_by( 'name', 'Uncategorized', 'product_cat' )->term_id
			?? self::factory()->term->create(
				array(
					'taxonomy' => 'product_cat',
					'name'     => 'Uncategorized',
				)
			);
		wp_update_term( $category_id, 'product_cat', array( 'name' => 'Uncategorized specials' ) );
		update_option( 'default_product_cat', $category_id );

		// Act.
		$result = get_uncategorized_product_category_id();

		// Assert.
		$this->assertNull( $result );
	}

	public function test_returns_category_with_translated_name(): void {
		// Arrange.
		update_option( 'WPLANG', 'de_DE' );
		switch_to_locale( 'de_DE' );
		$category_id = self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'name'     => 'Ohne Kategorie',
			)
		);

		// Act.
		$result = get_uncategorized_product_category_id();
		restore_previous_locale();

		// Assert.
		$this->assertSame( $category_id, $result );
	}

	public function test_uses_site_locale_for_translated_name(): void {
		// Arrange.
		update_option( 'WPLANG', 'en_US' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		update_user_meta( $user_id, 'locale', 'de_DE' );
		wp_set_current_user( $user_id );
		set_current_screen( 'dashboard' );
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

	public function test_woocommerce_pot_contains_uncategorized_with_default_category_slug_context(): void {
		// Arrange.
		$woocommerce_pot = new PO();
		$expected_entry  = new Translation_Entry(
			array(
				'singular' => 'Uncategorized',
				'context'  => 'Default category slug',
			)
		);

		// Act.
		$woocommerce_pot->import_from_file( WC_ABSPATH . 'i18n/languages/woocommerce.pot' );

		// Assert.
		$this->assertArrayHasKey( $expected_entry->key(), $woocommerce_pot->entries );
	}

	public function test_returns_uncategorized_category_with_custom_slug(): void {
		// Arrange.
		$category_id = get_term_by( 'name', 'Uncategorized', 'product_cat' )->term_id
			?? self::factory()->term->create(
				array(
					'taxonomy' => 'product_cat',
					'name'     => 'Uncategorized',
				)
			);
		wp_update_term( $category_id, 'product_cat', array( 'slug' => 'custom-category-slug' ) );

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
