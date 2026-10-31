<?php
/**
 * Tests for the public tier batch functions.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_discount_tier;
use function OutletPro\add_price_tier;
use function OutletPro\add_to_outlet;
use function OutletPro\batch_discount_tiers;
use function OutletPro\batch_price_tiers;
use function OutletPro\get_discount_tiers;
use function OutletPro\get_price_tiers;
use function OutletPro\init_products;
use function OutletPro\init_taxonomies;
use function OutletPro\seed_outlet_status_taxonomy;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Batch_Tiers extends WP_UnitTestCase {

	public function test_price_batch_defers_reindexing_and_preserves_updated_term_identity(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Observe real taxonomy events during the batch.
		// Arrange.
		init_taxonomies();
		init_products();
		seed_outlet_status_taxonomy();
		truncate_outlet_search_index_taxonomy();
		add_price_tier( 50 );
		add_price_tier( 100 );
		add_discount_tier( 30 );
		$original = get_term_by( 'slug', 'outlet-price-50', OUTLET_SEARCH_INDEX_TAXONOMY );
		$product  = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		as_unschedule_all_actions( 'outletpro_reindex_search_facets_batch', null, 'outletpro' );
		$assert_deferred = static function (): void {
			self::assertFalse( as_has_scheduled_action( 'outletpro_reindex_search_facets_batch', null, 'outletpro' ) );
		};
		add_action( 'created_' . OUTLET_SEARCH_INDEX_TAXONOMY, $assert_deferred );
		add_action( 'edited_' . OUTLET_SEARCH_INDEX_TAXONOMY, $assert_deferred );
		add_action( 'delete_' . OUTLET_SEARCH_INDEX_TAXONOMY, $assert_deferred );

		// Act.
		batch_price_tiers(
			array( 125, 150 ),
			array(
				50  => 60,
				125 => 130,
			),
			array( 100, 150 )
		);

		// Assert.
		$this->assertSame( array( 60, 130 ), get_price_tiers() );
		$this->assertSame( array( 30 ), get_discount_tiers() );
		$this->assertSame( $original->term_id, get_term_by( 'slug', 'outlet-price-60', OUTLET_SEARCH_INDEX_TAXONOMY )->term_id );
		$this->assertCount(
			1,
			as_get_scheduled_actions(
				array(
					'hook'   => 'outletpro_reindex_search_facets_batch',
					'group'  => 'outletpro',
					'status' => ActionScheduler_Store::STATUS_PENDING,
				),
				'ids'
			)
		);
		$this->assertSame( 10, has_action( 'created_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook' ) );
		$this->assertSame( 10, has_action( 'edited_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook' ) );
		$this->assertSame( 10, has_action( 'delete_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook' ) );
	}

	public function test_discount_batch_applies_all_operations_and_inclusive_bounds(): void {
		// Arrange.
		init_taxonomies();
		init_products();
		seed_outlet_status_taxonomy();
		truncate_outlet_search_index_taxonomy();
		add_discount_tier( 50 );
		add_discount_tier( 70 );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		as_unschedule_all_actions( 'outletpro_reindex_search_facets_batch', null, 'outletpro' );

		// Act.
		batch_discount_tiers( array( 0, 100 ), array( 50 => 60 ), array( 70 ) );

		// Assert.
		$this->assertSame( array( 0, 60, 100 ), get_discount_tiers() );
		$this->assertCount(
			1,
			as_get_scheduled_actions(
				array(
					'hook'   => 'outletpro_reindex_search_facets_batch',
					'group'  => 'outletpro',
					'status' => ActionScheduler_Store::STATUS_PENDING,
				),
				'ids'
			)
		);
	}

	public function test_empty_batches_do_not_reindex_products(): void {
		// Arrange.
		init_taxonomies();
		init_products();
		seed_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		as_unschedule_all_actions( 'outletpro_reindex_search_facets_batch', null, 'outletpro' );

		// Act.
		batch_price_tiers();
		batch_discount_tiers();

		// Assert.
		$this->assertFalse( as_has_scheduled_action( 'outletpro_reindex_search_facets_batch', null, 'outletpro' ) );
	}

	// phpcs:ignore Generic.Commenting.DocComment.MissingShort -- PHPUnit data provider metadata.
	/**
	 * @dataProvider invalid_batches
	 * @param mixed[] $add Amounts to add.
	 * @param mixed[] $update Amounts to update.
	 * @param mixed[] $delete Amounts to delete.
	 */
	public function test_invalid_batch_is_rejected_before_any_writes( callable $batch, callable $get_tiers, array $add, array $update, array $delete, string $exception ): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Check unchanged state while allowing the expected exception to propagate.
		// Arrange.
		init_taxonomies();
		init_products();
		seed_outlet_status_taxonomy();
		truncate_outlet_search_index_taxonomy();
		add_price_tier( 50 );
		add_price_tier( 100 );
		add_discount_tier( 50 );
		add_discount_tier( 100 );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		as_unschedule_all_actions( 'outletpro_reindex_search_facets_batch', null, 'outletpro' );

		// Expect.
		$this->expectException( $exception );

		// Act.
		try {
			$batch( $add, $update, $delete );
		} finally {
			// Assert.
			$this->assertSame( array( 50, 100 ), $get_tiers() );
			$this->assertFalse( as_has_scheduled_action( 'outletpro_reindex_search_facets_batch', null, 'outletpro' ) );
			$this->assertSame( 10, has_action( 'created_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook' ) );
		}
	}

	/**
	 * Provide invalid batch inputs and expected failure types.
	 *
	 * @return array<string, array<mixed>>
	 */
	public static function invalid_batches(): array {
		$price     = 'OutletPro\batch_price_tiers';
		$prices    = 'OutletPro\get_price_tiers';
		$discount  = 'OutletPro\batch_discount_tiers';
		$discounts = 'OutletPro\get_discount_tiers';

		return array(
			'non-integer add'       => array( $price, $prices, array( 125, '150' ), array(), array(), InvalidArgumentException::class ),
			'negative add'          => array( $price, $prices, array( 125, -1 ), array(), array(), InvalidArgumentException::class ),
			'non-integer old price' => array( $price, $prices, array( 125 ), array( 'bad' => 60 ), array(), InvalidArgumentException::class ),
			'non-integer new price' => array( $price, $prices, array( 125 ), array( 50 => '60' ), array(), InvalidArgumentException::class ),
			'negative old price'    => array( $price, $prices, array( 125 ), array( -1 => 60 ), array(), InvalidArgumentException::class ),
			'negative new price'    => array( $price, $prices, array( 125 ), array( 50 => -1 ), array(), InvalidArgumentException::class ),
			'non-integer delete'    => array( $price, $prices, array( 125 ), array(), array( '100' ), InvalidArgumentException::class ),
			'negative delete'       => array( $price, $prices, array( 125 ), array(), array( -1 ), InvalidArgumentException::class ),
			'duplicate add'         => array( $price, $prices, array( 125, 125 ), array(), array(), RuntimeException::class ),
			'existing add'          => array( $price, $prices, array( 125, 50 ), array(), array(), RuntimeException::class ),
			'missing update'        => array( $price, $prices, array( 125 ), array( 25 => 60 ), array(), RuntimeException::class ),
			'occupied update'       => array( $price, $prices, array( 125 ), array( 50 => 100 ), array(), RuntimeException::class ),
			'missing delete'        => array( $price, $prices, array( 125 ), array(), array( 25 ), RuntimeException::class ),
			'duplicate delete'      => array( $price, $prices, array( 125 ), array(), array( 100, 100 ), RuntimeException::class ),
			'discount add limit'    => array( $discount, $discounts, array( 25, 101 ), array(), array(), InvalidArgumentException::class ),
			'discount old limit'    => array( $discount, $discounts, array( 25 ), array( 101 => 60 ), array(), InvalidArgumentException::class ),
			'discount new limit'    => array( $discount, $discounts, array( 25 ), array( 50 => 101 ), array(), InvalidArgumentException::class ),
			'discount delete limit' => array( $discount, $discounts, array( 25 ), array(), array( 101 ), InvalidArgumentException::class ),
		);
	}

	// phpcs:ignore Generic.Commenting.DocComment.MissingShort -- PHPUnit data provider metadata.
	/**
	 * @dataProvider storage_failures
	 * @param int[] $expected_tiers Amounts saved before the failure.
	 */
	public function test_storage_failure_restores_hooks_and_reindexes_saved_changes( int $failed_amount, array $expected_tiers, int $expected_reindexes ): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Verify cleanup while propagating the expected storage failure.
		// Arrange.
		init_taxonomies();
		init_products();
		seed_outlet_status_taxonomy();
		truncate_outlet_search_index_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		as_unschedule_all_actions( 'outletpro_reindex_search_facets_batch', null, 'outletpro' );
		remove_action( 'created_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook' );
		add_action( 'created_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook', 7 );
		add_action( 'created_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook', 21 );
		$fail_insert = static function ( $term ) use ( $failed_amount ) {
			if ( 'outlet-price-' . $failed_amount === $term ) {
				return new WP_Error( 'simulated_storage_failure' );
			}
			return $term;
		};
		add_filter( 'pre_insert_term', $fail_insert );

		// Expect.
		$this->expectException( RuntimeException::class );

		// Act.
		try {
			batch_price_tiers( array( 125, 150 ) );
		} finally {
			// Assert.
			remove_filter( 'pre_insert_term', $fail_insert );
			$this->assertSame( $expected_tiers, get_price_tiers() );
			$this->assertSame( 7, has_action( 'created_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook' ) );
			remove_action( 'created_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook', 7 );
			$this->assertSame( 21, has_action( 'created_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook' ) );
			$this->assertSame( 10, has_action( 'edited_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook' ) );
			$this->assertSame( 10, has_action( 'delete_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook' ) );
			$this->assertCount(
				$expected_reindexes,
				as_get_scheduled_actions(
					array(
						'hook'   => 'outletpro_reindex_search_facets_batch',
						'group'  => 'outletpro',
						'status' => ActionScheduler_Store::STATUS_PENDING,
					),
					'ids'
				)
			);
		}
	}

	/**
	 * Provide failures before and after a successful tier write.
	 *
	 * @return array<string, array{0: int, 1: int[], 2: int}>
	 */
	public static function storage_failures(): array {
		return array(
			'first write'  => array( 125, array(), 0 ),
			'second write' => array( 150, array( 125 ), 1 ),
		);
	}

	public function test_batch_preserves_existing_pause(): void {
		// Arrange.
		init_taxonomies();
		init_products();
		seed_outlet_status_taxonomy();
		truncate_outlet_search_index_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		as_unschedule_all_actions( 'outletpro_reindex_search_facets_batch', null, 'outletpro' );
		remove_action( 'created_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook' );
		remove_action( 'edited_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook' );
		remove_action( 'delete_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook' );

		// Act.
		batch_price_tiers( array( 125, 150 ) );

		// Assert.
		$this->assertSame( array( 125, 150 ), get_price_tiers() );
		$this->assertFalse( has_action( 'created_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook' ) );
		$this->assertFalse( has_action( 'edited_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook' ) );
		$this->assertFalse( has_action( 'delete_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook' ) );
		$this->assertFalse( as_has_scheduled_action( 'outletpro_reindex_search_facets_batch', null, 'outletpro' ) );
	}

	public function test_nested_batches_request_one_reindex_after_the_outer_batch(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Trigger a nested batch through a real taxonomy event.
		// Arrange.
		init_taxonomies();
		init_products();
		seed_outlet_status_taxonomy();
		truncate_outlet_search_index_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		as_unschedule_all_actions( 'outletpro_reindex_search_facets_batch', null, 'outletpro' );
		$nested_batch = static function ( int $term_id ): void {
			$term = get_term( $term_id, OUTLET_SEARCH_INDEX_TAXONOMY );
			if ( 'outlet-price-125' !== $term->slug ) {
				return;
			}
			batch_discount_tiers( array( 40 ) );
			self::assertFalse( as_has_scheduled_action( 'outletpro_reindex_search_facets_batch', null, 'outletpro' ) );
		};
		add_action( 'created_' . OUTLET_SEARCH_INDEX_TAXONOMY, $nested_batch );

		// Act.
		batch_price_tiers( array( 125, 150 ) );

		// Assert.
		$this->assertSame( array( 125, 150 ), get_price_tiers() );
		$this->assertSame( array( 40 ), get_discount_tiers() );
		$this->assertCount(
			1,
			as_get_scheduled_actions(
				array(
					'hook'   => 'outletpro_reindex_search_facets_batch',
					'group'  => 'outletpro',
					'status' => ActionScheduler_Store::STATUS_PENDING,
				),
				'ids'
			)
		);
	}
}
