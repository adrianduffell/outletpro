<?php
/**
 * Tests for product search index synchronization.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\init_products;
use function OutletPro\register_outlet_search_index_taxonomy;
use function OutletPro\register_outlet_status_taxonomy;
use function OutletPro\seed_outlet_search_index_taxonomy;
use function OutletPro\seed_outlet_status_taxonomy;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Products extends WP_UnitTestCase {

	public function test_saving_product_without_prices_has_price_search_index_and_no_discount(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		register_outlet_search_index_taxonomy();
		seed_outlet_status_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		seed_outlet_search_index_taxonomy();
		init_products();
		$product = WC_Helper_Product::create_simple_product();
		$product->set_price( '' );
		$product->set_regular_price( '' );
		add_to_outlet( $product );

		// Act.
		$product->save();

		// Assert.
		$this->assertSame( array( 'outlet-price-10' ), wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) ) );
	}

	public function test_saving_outlet_product_updates_price_and_discount_search_indexes(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		register_outlet_search_index_taxonomy();
		seed_outlet_status_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		seed_outlet_search_index_taxonomy();
		init_products();
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$product->set_regular_price( '100' );
		$product->set_sale_price( '20' );
		$product->set_price( '20' );

		// Act.
		$product->save();

		// Assert.
		$this->assertSame(
			array( 'outlet-discount-70', 'outlet-price-25' ),
			wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) )
		);
	}

	public function test_saving_non_outlet_product_does_not_write_search_indexes(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Observe writes to the search index taxonomy.
		// Arrange.
		register_outlet_status_taxonomy();
		register_outlet_search_index_taxonomy();
		init_products();
		$product = WC_Helper_Product::create_simple_product();
		$writes  = 0;
		add_action(
			'set_object_terms',
			static function ( $object_id, $terms, $term_taxonomy_ids, $taxonomy ) use ( &$writes ): void {
				if ( OUTLET_SEARCH_INDEX_TAXONOMY !== $taxonomy ) {
					return;
				}
				++$writes;
			},
			10,
			4
		);

		// Act.
		$product->save();

		// Assert.
		$this->assertSame( 0, $writes );
	}

	public function test_saving_product_outside_price_tiers_has_only_discount_index(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		register_outlet_search_index_taxonomy();
		seed_outlet_status_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		seed_outlet_search_index_taxonomy();
		init_products();
		$product = WC_Helper_Product::create_simple_product();
		$product->set_price( '100' );
		$product->set_sale_price( '100' );
		$product->set_regular_price( '1000' );
		add_to_outlet( $product );

		// Act.
		$product->save();

		// Assert.
		$this->assertSame( array( 'outlet-discount-70' ), wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) ) );
	}
}
