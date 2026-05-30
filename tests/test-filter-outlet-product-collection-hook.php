<?php
/**
 * Tests for filter_outlet_product_collection_hook().
 *
 * @package WC_Outlet
 */

use function WC_Outlet\deinit_blocks;
use function WC_Outlet\init_blocks;
use function WC_Outlet\init_taxonomies;
use function WC_Outlet\seed_outlet_status_taxonomy;
use const WC_Outlet\OUTLET_STATUS_CANONICAL_TERM;
use const WC_Outlet\OUTLET_STATUS_TAXONOMY;
class Test_Filter_Outlet_Product_Collection_Hook extends WP_UnitTestCase {
	public function tearDown(): void {
		unset( $_GET['orderby'] );
		parent::tearDown();
	}

	public function test_orderby_price_sets_asc_order_for_outlet_product_collection_block(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$parsed_block    = array(
			'blockName' => 'woocommerce/product-collection',
			'attrs'     => array(
				'query' => array(
					'wc_outlet' => true,
					'orderBy'   => 'menu_order',
					'order'     => 'desc',
				),
			),
		);
		$_GET['orderby'] = 'price';

		// Act.
		$result = apply_filters( 'render_block_data', $parsed_block );

		// Assert.
		$this->assertSame( 'price', $result['attrs']['query']['orderBy'] );
		$this->assertSame( 'asc', $result['attrs']['query']['order'] );
	}

	public function test_orderby_price_desc_sets_desc_order_for_outlet_product_collection_block(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$parsed_block    = array(
			'blockName' => 'woocommerce/product-collection',
			'attrs'     => array(
				'query' => array(
					'wc_outlet' => true,
					'orderBy'   => 'menu_order',
					'order'     => 'asc',
				),
			),
		);
		$_GET['orderby'] = 'price-desc';

		// Act.
		$result = apply_filters( 'render_block_data', $parsed_block );

		// Assert.
		$this->assertSame( 'price-desc', $result['attrs']['query']['orderBy'] );
		$this->assertSame( 'desc', $result['attrs']['query']['order'] );
	}

	public function test_orderby_date_sets_desc_order_for_outlet_product_collection_block(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$parsed_block    = array(
			'blockName' => 'woocommerce/product-collection',
			'attrs'     => array(
				'query' => array(
					'wc_outlet' => true,
					'orderBy'   => 'menu_order',
					'order'     => 'asc',
				),
			),
		);
		$_GET['orderby'] = 'date';

		// Act.
		$result = apply_filters( 'render_block_data', $parsed_block );

		// Assert.
		$this->assertSame( 'date', $result['attrs']['query']['orderBy'] );
		$this->assertSame( 'desc', $result['attrs']['query']['order'] );
	}

	public function test_orderby_popularity_sets_desc_order_for_outlet_product_collection_block(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$parsed_block    = array(
			'blockName' => 'woocommerce/product-collection',
			'attrs'     => array(
				'query' => array(
					'wc_outlet' => true,
					'orderBy'   => 'menu_order',
					'order'     => 'asc',
				),
			),
		);
		$_GET['orderby'] = 'popularity';

		// Act.
		$result = apply_filters( 'render_block_data', $parsed_block );

		// Assert.
		$this->assertSame( 'popularity', $result['attrs']['query']['orderBy'] );
		$this->assertSame( 'desc', $result['attrs']['query']['order'] );
	}

	public function test_orderby_rating_sets_desc_order_for_outlet_product_collection_block(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$parsed_block    = array(
			'blockName' => 'woocommerce/product-collection',
			'attrs'     => array(
				'query' => array(
					'wc_outlet' => true,
					'orderBy'   => 'menu_order',
					'order'     => 'asc',
				),
			),
		);
		$_GET['orderby'] = 'rating';

		// Act.
		$result = apply_filters( 'render_block_data', $parsed_block );

		// Assert.
		$this->assertSame( 'rating', $result['attrs']['query']['orderBy'] );
		$this->assertSame( 'desc', $result['attrs']['query']['order'] );
	}

	public function test_orderby_menu_order_sets_asc_order_for_outlet_product_collection_block(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$parsed_block    = array(
			'blockName' => 'woocommerce/product-collection',
			'attrs'     => array(
				'query' => array(
					'wc_outlet' => true,
					'orderBy'   => 'price-desc',
					'order'     => 'desc',
				),
			),
		);
		$_GET['orderby'] = 'menu_order';

		// Act.
		$result = apply_filters( 'render_block_data', $parsed_block );

		// Assert.
		$this->assertSame( 'menu_order', $result['attrs']['query']['orderBy'] );
		$this->assertSame( 'asc', $result['attrs']['query']['order'] );
	}

	public function test_orderby_is_not_applied_when_value_is_invalid(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$parsed_block    = array(
			'blockName' => 'woocommerce/product-collection',
			'attrs'     => array(
				'query' => array(
					'wc_outlet' => true,
					'orderBy'   => 'menu_order',
				),
			),
		);
		$_GET['orderby'] = 'invalid';

		// Act.
		$result = apply_filters( 'render_block_data', $parsed_block );

		// Assert.
		$this->assertSame( 'menu_order', $result['attrs']['query']['orderBy'] );
	}

	public function test_orderby_is_not_applied_when_not_outlet_query(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$parsed_block    = array(
			'blockName' => 'woocommerce/product-collection',
			'attrs'     => array(
				'query' => array(
					'wc_outlet' => false,
					'orderBy'   => 'menu_order',
				),
			),
		);
		$_GET['orderby'] = 'date';

		// Act.
		$result = apply_filters( 'render_block_data', $parsed_block );

		// Assert.
		$this->assertSame( 'menu_order', $result['attrs']['query']['orderBy'] );
	}


	public function test_query_is_unchanged_when_not_product_collection_block(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$query          = array( 'post_type' => 'product' );
		$block          = new WP_Block(
			array(
				'blockName'    => 'core/query',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			)
		);
		$block->context = array( 'query' => array( 'isProductCollectionBlock' => false ) );
		$expected       = $query;

		// Act.
		$result = apply_filters( 'query_loop_block_query_vars', $query, $block, 1 );

		// Assert.
		$this->assertSame( $expected, $result );
	}

	public function test_query_is_unchanged_when_product_collection_block_flag_is_missing(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$query          = array( 'post_type' => 'product' );
		$block          = new WP_Block(
			array(
				'blockName'    => 'core/query',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			)
		);
		$block->context = array();
		$expected       = $query;

		// Act.
		$result = apply_filters( 'query_loop_block_query_vars', $query, $block, 1 );

		// Assert.
		$this->assertSame( $expected, $result );
	}

	public function test_query_is_unchanged_when_collection_is_different(): void {
		// Arrange.
		deinit_blocks();
		$query          = array( 'post_type' => 'product' );
		$block          = new WP_Block(
			array(
				'blockName'    => 'core/query',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			)
		);
		$block->context = array(
			'query'      => array( 'isProductCollectionBlock' => true ),
			'collection' => 'wc-outlet/product-collection/other',
		);
		$expected       = apply_filters( 'query_loop_block_query_vars', $query, $block, 1 );  // Account for external filters added.
		init_blocks();

		// Act.
		$result = apply_filters( 'query_loop_block_query_vars', $query, $block, 1 );

		// Assert.
		$this->assertSame( $expected, $result );
	}

	public function test_query_is_unchanged_when_collection_is_missing(): void {
		// Arrange.
		deinit_blocks();
		$query          = array( 'post_type' => 'product' );
		$block          = new WP_Block(
			array(
				'blockName'    => 'core/query',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			)
		);
		$block->context = array( 'query' => array( 'isProductCollectionBlock' => true ) );

		$expected = apply_filters( 'query_loop_block_query_vars', $query, $block, 1 ); // Account for external filters added.
		init_blocks();

		// Act.
		$result = apply_filters( 'query_loop_block_query_vars', $query, $block, 1 );

		// Assert.
		$this->assertSame( $expected, $result );
	}

	public function test_tax_query_is_added_when_canonical_term_exists(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		$canonical_term = get_term_by( 'name', OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY );
		$query          = array( 'post_type' => 'product' );
		$block          = new WP_Block(
			array(
				'blockName'    => 'core/query',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			)
		);
		$block->context = array(
			'query' => array( 'wc_outlet' => true ),
		);

		// Act.
		$result = apply_filters( 'query_loop_block_query_vars', $query, $block, 1 );

		// Assert.
		$this->assertArrayHasKey( 'tax_query', $result );
		$this->assertCount( 1, $result['tax_query'] );
		$this->assertSame( OUTLET_STATUS_TAXONOMY, $result['tax_query'][0]['taxonomy'] );
		$this->assertSame( 'term_id', $result['tax_query'][0]['field'] );
		$this->assertSame( $canonical_term->term_id, $result['tax_query'][0]['terms'] );
	}

	public function test_existing_tax_query_entries_are_preserved(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		$existing_tax_clause = array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => array( 'clothing' ),
		);

		$query = array(
			'post_type' => 'product',
			'tax_query' => array( $existing_tax_clause ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		);

		$block = new WP_Block(
			array(
				'blockName'    => 'core/query',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			)
		);

		$block->context = array(
			'query' => array( 'wc_outlet' => true ),
		);

		// Act.
		$result = apply_filters( 'query_loop_block_query_vars', $query, $block, 1 );

		// Assert.
		$this->assertCount( 2, $result['tax_query'] );
		$this->assertSame( $existing_tax_clause, $result['tax_query'][0] );
		$this->assertSame( OUTLET_STATUS_TAXONOMY, $result['tax_query'][1]['taxonomy'] );
	}

	public function test_filter_is_registered_by_init_blocks(): void {
		// Arrange.
		deinit_blocks();

		// Act.
		init_blocks();

		// Assert.
		$this->assertSame( 11, has_filter( 'query_loop_block_query_vars', 'WC_Outlet\filter_outlet_product_collection_hook' ) );
		$this->assertSame( 11, has_filter( 'render_block_data', 'WC_Outlet\set_outlet_product_collection_orderby_hook' ) );
	}
}
