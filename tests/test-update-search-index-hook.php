<?php
/**
 * Tests for the update_search_index_hook function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\init_products;
use function OutletPro\init_taxonomies;
use function OutletPro\seed_outlet_search_index_taxonomy;
use function OutletPro\seed_outlet_status_taxonomy;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Update_Search_Index_Hook extends WP_UnitTestCase {

	public function test_saving_product_without_prices_has_no_search_index_facets(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		truncate_outlet_search_index_taxonomy();
		seed_outlet_search_index_taxonomy();
		init_products();

		$product = WC_Helper_Product::create_simple_product();
		$product->set_price( '' );
		$product->set_regular_price( '' );
		add_to_outlet( $product );

		// Act.
		$product->save();
		self::run_reindex_actions( $product );

		// Assert.
		$this->assertSame( array(), wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) ) );
	}

	public function test_saving_outlet_product_queues_reindexing_without_updating_facets_immediately(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		truncate_outlet_search_index_taxonomy();
		seed_outlet_search_index_taxonomy();
		init_products();

		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$original_facets = wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) );
		$product->set_regular_price( '100' );
		$product->set_sale_price( '20' );
		$product->set_price( '20' );

		// Act.
		$product->save();

		// Assert.
		$this->assertSame( $original_facets, wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) ) );

		// Act.
		self::run_reindex_actions( $product );

		// Assert.
		$this->assertSame(
			array( 'outlet-discount-70', 'outlet-price-25' ),
			wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) )
		);
	}

	public function test_saving_non_outlet_product_does_not_schedule_reindexing(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		init_products();

		$product = WC_Helper_Product::create_simple_product();

		// Act.
		$product->save();

		// Assert.
		$this->assertSame(
			array(),
			as_get_scheduled_actions(
				array(
					'hook' => 'outletpro_reindex_search_facets',
					'args' => array( $product->get_id() ),
				),
				'ids'
			)
		);
	}

	public function test_saving_product_outside_price_tiers_has_only_discount_index(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		truncate_outlet_search_index_taxonomy();
		seed_outlet_search_index_taxonomy();
		init_products();

		$product = WC_Helper_Product::create_simple_product();
		$product->set_price( '100' );
		$product->set_sale_price( '100' );
		$product->set_regular_price( '1000' );
		add_to_outlet( $product );

		// Act.
		$product->save();
		self::run_reindex_actions( $product );

		// Assert.
		$this->assertSame( array( 'outlet-discount-70' ), wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) ) );
	}

	public function test_deferred_parent_save_updates_facets_after_saving_a_variation(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		truncate_outlet_search_index_taxonomy();
		seed_outlet_search_index_taxonomy();
		init_products();

		$product = new WC_Product_Variable();
		$product->save();

		$variation = self::create_variation( $product, '100', '40' );
		self::create_variation( $product, '100', '45' );
		$product = wc_get_product( $product->get_id() );
		add_to_outlet( $product );

		// Act.
		$variation->set_sale_price( '20' );
		$variation->save();
		WC_Post_Data::do_deferred_product_sync();
		self::run_reindex_actions( $product );

		// Assert.
		$this->assertSame( array( 'outlet-price-25' ), wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) ) );
		$this->assertSame( array(), wp_get_object_terms( $variation->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY ) );
	}

	public function test_trashing_variation_reclassifies_parent_using_remaining_variations(): void {
		// Arrange.
		init_taxonomies();
		init_products();
		seed_outlet_status_taxonomy();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		seed_outlet_search_index_taxonomy();

		$product = new WC_Product_Variable();
		$product->save();

		$variation = self::create_variation( $product, '100', '20' );
		self::create_variation( $product, '100', '45' );
		$product = wc_get_product( $product->get_id() );
		add_to_outlet( $product );

		// Act.
		$variation->delete();
		WC_Post_Data::do_deferred_product_sync();
		self::run_reindex_actions( $product );

		// Assert.
		$this->assertSame( array( 'outlet-discount-50', 'outlet-price-50' ), wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) ) );
	}

	public function test_deleting_variation_reclassifies_parent_using_remaining_variations(): void {
		// Arrange.
		init_taxonomies();
		init_products();
		seed_outlet_status_taxonomy();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		seed_outlet_search_index_taxonomy();

		$product = new WC_Product_Variable();
		$product->save();

		$variation = self::create_variation( $product, '100', '20' );
		self::create_variation( $product, '100', '45' );
		$product = wc_get_product( $product->get_id() );
		add_to_outlet( $product );

		// Act.
		$variation->delete( true );
		WC_Post_Data::do_deferred_product_sync();
		self::run_reindex_actions( $product );

		// Assert.
		$this->assertSame( array( 'outlet-discount-50', 'outlet-price-50' ), wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) ) );
	}

	public function test_deleting_last_variation_clears_parent_facets(): void {
		// Arrange.
		init_taxonomies();
		init_products();
		seed_outlet_status_taxonomy();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		seed_outlet_search_index_taxonomy();

		$product = new WC_Product_Variable();
		$product->save();

		$variation = self::create_variation( $product, '100', '20' );
		$product   = wc_get_product( $product->get_id() );
		add_to_outlet( $product );
		self::run_reindex_actions( $product );
		$this->assertNotEmpty( wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY ) );

		// Act.
		$variation->delete( true );
		WC_Post_Data::do_deferred_product_sync();
		self::run_reindex_actions( $product );

		// Assert.
		$this->assertSame( array(), wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY ) );
	}

	private static function run_reindex_actions( WC_Product $product ): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Process each queued action to verify its result.
		$action_ids = as_get_scheduled_actions(
			array(
				'hook'   => 'outletpro_reindex_search_facets',
				'args'   => array( $product->get_id() ),
				'status' => ActionScheduler_Store::STATUS_PENDING,
			),
			'ids'
		);
		self::assertNotEmpty( $action_ids );
		foreach ( $action_ids as $action_id ) {
			ActionScheduler::runner()->process_action( $action_id, 'test' );
			self::assertSame( ActionScheduler_Store::STATUS_COMPLETE, ActionScheduler::store()->get_status( $action_id ) );
		}
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
