<?php
/**
 * Tests for the filtered outlet permalink helper.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_outlet_permalink_with_filter;
use function OutletPro\init_taxonomies;
use const OutletPro\OUTLET_PAGE_OPTION;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Get_Outlet_Permalink_With_Filter extends WP_UnitTestCase {

	public function test_returns_price_tier_permalink(): void {
		// Arrange.
		init_taxonomies();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$term = self::factory()->term->create_and_get(
			array(
				'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
				'slug'     => 'outlet-price-25',
			)
		);

		// Act.
		$permalink = get_outlet_permalink_with_filter( $term );

		// Assert.
		$this->assertSame( add_query_arg( 'outlet_price', 25, get_permalink( $page_id ) ), $permalink );
	}

	public function test_returns_discount_tier_permalink(): void {
		// Arrange.
		init_taxonomies();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$term = self::factory()->term->create_and_get(
			array(
				'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
				'slug'     => 'outlet-discount-50',
			)
		);

		// Act.
		$permalink = get_outlet_permalink_with_filter( $term );

		// Assert.
		$this->assertSame( add_query_arg( 'outlet_discount', 50, get_permalink( $page_id ) ), $permalink );
	}

	public function test_returns_category_permalink(): void {
		// Arrange.
		init_taxonomies();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$term = self::factory()->term->create_and_get(
			array(
				'taxonomy' => 'product_cat',
				'slug'     => 'clothing',
			)
		);

		// Act.
		$permalink = get_outlet_permalink_with_filter( $term );

		// Assert.
		$this->assertSame( add_query_arg( 'outlet_category', 'clothing', get_permalink( $page_id ) ), $permalink );
	}

	public function test_returns_null_without_outlet_page(): void {
		// Arrange.
		init_taxonomies();
		delete_option( OUTLET_PAGE_OPTION );
		$term = self::factory()->term->create_and_get(
			array(
				'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
				'slug'     => 'outlet-price-25',
			)
		);

		// Act.
		$permalink = get_outlet_permalink_with_filter( $term );

		// Assert.
		$this->assertNull( $permalink );
	}

	// phpcs:ignore Generic.Commenting.DocComment.MissingShort -- PHPUnit data provider metadata.
	/**
	 * @dataProvider invalid_facet_slugs
	 */
	public function test_rejects_invalid_facet_slug( string $slug ): void {
		// Arrange.
		init_taxonomies();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$term = self::factory()->term->create_and_get( array( 'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY ) );
		// Preserve malformed input that WordPress would normalize on insertion.
		$term->slug = $slug;

		// Act.
		$permalink = get_outlet_permalink_with_filter( $term );

		// Assert.
		$this->assertNull( $permalink );
	}

	/**
	 * Provides malformed or unsupported facet slugs.
	 *
	 * @return array<string, array{0: string}>
	 */
	public static function invalid_facet_slugs(): array {
		return array(
			'custom term'       => array( 'custom' ),
			'missing amount'    => array( 'outlet-price' ),
			'wrong prefix'      => array( 'custom-price-25' ),
			'extra suffix'      => array( 'outlet-price-25-dollars' ),
			'unsupported facet' => array( 'outlet-status-25' ),
			'negative amount'   => array( 'outlet-price--25' ),
			'fractional amount' => array( 'outlet-price-25.5' ),
			'zero price'        => array( 'outlet-price-0' ),
			'leading zero'      => array( 'outlet-price-025' ),
			'overflow amount'   => array( 'outlet-price-' . str_repeat( '9', 30 ) ),
			'zero discount'     => array( 'outlet-discount-0' ),
			'excess discount'   => array( 'outlet-discount-101' ),
		);
	}
}
