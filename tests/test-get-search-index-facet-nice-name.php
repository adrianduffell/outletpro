<?php
/**
 * Tests for the search index facet name helper.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_search_index_facet_nice_name;
use function OutletPro\init_taxonomies;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Get_Search_Index_Facet_Nice_Name extends WP_UnitTestCase {

	public function test_returns_formatted_price_tier_name(): void {
		// Arrange.
		init_taxonomies();
		update_option( 'woocommerce_currency', 'GBP' );
		$term = self::factory()->term->create_and_get(
			array(
				'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
				'slug'     => 'outlet-price-25',
			)
		);

		// Act.
		$name = get_search_index_facet_nice_name( $term );

		// Assert.
		$this->assertSame( 'Under £25', $name );
	}

	public function test_returns_formatted_discount_tier_name(): void {
		// Arrange.
		init_taxonomies();
		$term = self::factory()->term->create_and_get(
			array(
				'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
				'slug'     => 'outlet-discount-30',
			)
		);

		// Act.
		$name = get_search_index_facet_nice_name( $term );

		// Assert.
		$this->assertSame( '30% off', $name );
	}

	// phpcs:ignore Generic.Commenting.DocComment.MissingShort -- PHPUnit data provider metadata.
	/**
	 * @dataProvider invalid_terms
	 */
	public function test_rejects_invalid_term( string $taxonomy, string $slug ): void {
		// Arrange.
		init_taxonomies();
		$term = self::factory()->term->create_and_get(
			array(
				'taxonomy' => $taxonomy,
				'slug'     => $slug,
			)
		);

		// Preserve malformed input that WordPress would normalize on insertion.
		$term->slug = $slug;

		// Expect.
		$this->expectException( InvalidArgumentException::class );

		// Act.
		get_search_index_facet_nice_name( $term );
	}

	/**
	 * Provides invalid tier terms.
	 *
	 * @return array<string, array{0: string, 1: string}>
	 */
	public static function invalid_terms(): array {
		return array(
			'wrong taxonomy'    => array( 'product_cat', 'outlet-price-25' ),
			'unsupported type'  => array( OUTLET_SEARCH_INDEX_TAXONOMY, 'outlet-status-25' ),
			'wrong prefix'      => array( OUTLET_SEARCH_INDEX_TAXONOMY, 'other-price-25' ),
			'overflow amount'   => array( OUTLET_SEARCH_INDEX_TAXONOMY, 'outlet-price-' . str_repeat( '9', 30 ) ),
			'invalid amount'    => array( OUTLET_SEARCH_INDEX_TAXONOMY, 'outlet-price-invalid' ),
			'negative amount'   => array( OUTLET_SEARCH_INDEX_TAXONOMY, 'outlet-price--25' ),
			'fractional amount' => array( OUTLET_SEARCH_INDEX_TAXONOMY, 'outlet-price-25.5' ),
		);
	}
}
