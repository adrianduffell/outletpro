<?php
/**
 * Tests for batch_price_tiers() function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_price_tier;
use function OutletPro\add_to_outlet;
use function OutletPro\batch_price_tiers;
use function OutletPro\get_discount_tiers;
use function OutletPro\get_price_tiers;
use function OutletPro\init_products;
use function OutletPro\init_taxonomies;
use function OutletPro\seed_outlet_status_taxonomy;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Batch_Price_Tiers extends WP_UnitTestCase {

	public function test_batch_defers_reindexing_and_preserves_updated_term_identity(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Observe real taxonomy events during the batch.
		// Arrange.
		init_taxonomies();
		init_products();
		seed_outlet_status_taxonomy();
		truncate_outlet_search_index_taxonomy();
		add_price_tier( 50 );
		add_price_tier( 100 );
		wp_insert_term( 'outlet-discount-30', OUTLET_SEARCH_INDEX_TAXONOMY );
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

	public function test_empty_batch_requests_reindexing(): void {
		// Arrange.
		init_taxonomies();
		init_products();
		seed_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		as_unschedule_all_actions( 'outletpro_reindex_search_facets_batch', null, 'outletpro' );

		// Act.
		batch_price_tiers();

		// Assert.
		$this->assertTrue( as_has_scheduled_action( 'outletpro_reindex_search_facets_batch', null, 'outletpro' ) );
	}

	public function test_storage_failure_restores_hooks_and_reindexes_saved_changes(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Verify cleanup while propagating the expected storage failure.
		// Arrange.
		init_taxonomies();
		init_products();
		seed_outlet_status_taxonomy();
		truncate_outlet_search_index_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		as_unschedule_all_actions( 'outletpro_reindex_search_facets_batch', null, 'outletpro' );
		$fail_insert = static fn( string $term ) => 'outlet-price-150' === $term ? new WP_Error( 'simulated_storage_failure' ) : $term;
		add_filter( 'pre_insert_term', $fail_insert );

		// Expect.
		$this->expectException( RuntimeException::class );

		// Act.
		try {
			batch_price_tiers( array( 125, 150 ) );
		} finally {
			// Assert.
			remove_filter( 'pre_insert_term', $fail_insert );
			$this->assertSame( array( 125 ), get_price_tiers() );
			$this->assertSame( 10, has_action( 'created_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook' ) );
			$this->assertSame( 10, has_action( 'edited_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook' ) );
			$this->assertSame( 10, has_action( 'delete_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook' ) );
			$this->assertTrue( as_has_scheduled_action( 'outletpro_reindex_search_facets_batch', null, 'outletpro' ) );
		}
	}

	// phpcs:ignore Generic.Commenting.DocComment.MissingShort -- PHPUnit data provider metadata.
	/**
	 * @dataProvider invalid_batches
	 * @param mixed[] $add Amounts to add.
	 * @param mixed[] $update Amounts to update.
	 * @param mixed[] $delete Amounts to delete.
	 * @param int[] $expected_tiers Amounts saved before the failure.
	 */
	public function test_invalid_batch_retains_and_reindexes_successful_operations( array $add, array $update, array $delete, string $exception, array $expected_tiers ): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Verify saved changes while propagating the expected failure.
		// Arrange.
		init_taxonomies();
		init_products();
		seed_outlet_status_taxonomy();
		truncate_outlet_search_index_taxonomy();
		add_price_tier( 50 );
		add_price_tier( 100 );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		as_unschedule_all_actions( 'outletpro_reindex_search_facets_batch', null, 'outletpro' );

		// Expect.
		$this->expectException( $exception );

		// Act.
		try {
			batch_price_tiers( $add, $update, $delete );
		} finally {
			// Assert.
			$this->assertSame( $expected_tiers, get_price_tiers() );
			$this->assertSame( array( 50, 100 ) !== $expected_tiers, as_has_scheduled_action( 'outletpro_reindex_search_facets_batch', null, 'outletpro' ) );
			$this->assertSame( 10, has_action( 'created_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook' ) );
		}
	}

	/**
	 * Provide invalid price batch inputs.
	 *
	 * @return array<string, array<mixed>>
	 */
	public static function invalid_batches(): array {
		return array(
			'non-integer add'       => array( array( 125, '150' ), array(), array(), InvalidArgumentException::class, array( 50, 100 ) ),
			'negative add'          => array( array( 125, -1 ), array(), array(), InvalidArgumentException::class, array( 50, 100, 125 ) ),
			'non-integer old price' => array( array( 125 ), array( 'bad' => 60 ), array(), InvalidArgumentException::class, array( 50, 100 ) ),
			'non-integer new price' => array( array( 125 ), array( 50 => '60' ), array(), InvalidArgumentException::class, array( 50, 100 ) ),
			'negative old price'    => array( array( 125 ), array( -1 => 60 ), array(), InvalidArgumentException::class, array( 50, 100, 125 ) ),
			'negative new price'    => array( array( 125 ), array( 50 => -1 ), array(), InvalidArgumentException::class, array( 50, 100, 125 ) ),
			'non-integer delete'    => array( array( 125 ), array(), array( '100' ), InvalidArgumentException::class, array( 50, 100 ) ),
			'negative delete'       => array( array( 125 ), array(), array( -1 ), InvalidArgumentException::class, array( 50, 100, 125 ) ),
			'duplicate add'         => array( array( 125, 125 ), array(), array(), RuntimeException::class, array( 50, 100, 125 ) ),
			'existing add'          => array( array( 125, 50 ), array(), array(), RuntimeException::class, array( 50, 100, 125 ) ),
			'missing update'        => array( array( 125 ), array( 25 => 60 ), array(), RuntimeException::class, array( 50, 100, 125 ) ),
			'occupied update'       => array( array( 125 ), array( 50 => 100 ), array(), RuntimeException::class, array( 50, 100, 125 ) ),
			'missing delete'        => array( array( 125 ), array(), array( 25 ), RuntimeException::class, array( 50, 100, 125 ) ),
			'duplicate delete'      => array( array( 125 ), array(), array( 100, 100 ), RuntimeException::class, array( 50, 125 ) ),
		);
	}
}
