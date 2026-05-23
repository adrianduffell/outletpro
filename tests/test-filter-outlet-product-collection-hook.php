<?php
/**
 * Tests for filter_outlet_product_collection_hook().
 *
 * @package WC_Outlet
 */

use function WC_Outlet\init_product_collection;
use function WC_Outlet\init_taxonomies;
use function WC_Outlet\inject_outlet_query_flag_hook;
use function WC_Outlet\seed_outlet_status_taxonomy;
use function WC_Outlet\set_outlet_product_collection_orderby_hook;
use const WC_Outlet\OUTLET_STATUS_CANONICAL_TERM;
use const WC_Outlet\OUTLET_STATUS_TAXONOMY;
class Test_Filter_Outlet_Product_Collection_Hook extends WP_UnitTestCase {

	public function test_query_is_unchanged_when_not_product_collection_block(): void {
		// Arrange.
		remove_all_filters( 'query_loop_block_query_vars' );
		init_product_collection();
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
		remove_all_filters( 'query_loop_block_query_vars' );
		init_product_collection();
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
		remove_all_filters( 'query_loop_block_query_vars' );
		init_product_collection();
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
		$expected       = $query;

		// Act.
		$result = apply_filters( 'query_loop_block_query_vars', $query, $block, 1 );

		// Assert.
		$this->assertSame( $expected, $result );
	}

	public function test_query_is_unchanged_when_collection_is_missing(): void {
		// Arrange.
		remove_all_filters( 'query_loop_block_query_vars' );
		init_product_collection();
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
		$expected       = $query;

		// Act.
		$result = apply_filters( 'query_loop_block_query_vars', $query, $block, 1 );

		// Assert.
		$this->assertSame( $expected, $result );
	}

	public function test_tax_query_is_added_when_canonical_term_exists(): void {
		// Arrange.
		remove_all_filters( 'query_loop_block_query_vars' );
		init_product_collection();
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
		remove_all_filters( 'query_loop_block_query_vars' );
		init_product_collection();
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

	public function test_filter_is_registered_by_init_product_collection(): void {
		// Arrange.
		remove_all_filters( 'query_loop_block_query_vars' );

		// Act.
		init_product_collection();

		// Assert.
		$this->assertSame( 11, has_filter( 'query_loop_block_query_vars', 'WC_Outlet\filter_outlet_product_collection_hook' ) );
	}

	public function test_block_is_unchanged_when_not_product_collection_block(): void {
		// Arrange.
		$parsed_block = array(
			'blockName' => 'core/query',
			'attrs'     => array(
				'collection' => 'wc-outlet/product-collection/outlet',
			),
		);
		$expected     = $parsed_block;

		// Act.
		$result = inject_outlet_query_flag_hook( $parsed_block, $parsed_block, null );

		// Assert.
		$this->assertSame( $expected, $result );
	}

	public function test_block_is_unchanged_when_collection_is_different(): void {
		// Arrange.
		$parsed_block = array(
			'blockName' => 'woocommerce/product-collection',
			'attrs'     => array(
				'collection' => 'wc-outlet/product-collection/other',
			),
		);
		$expected     = $parsed_block;

		// Act.
		$result = inject_outlet_query_flag_hook( $parsed_block, $parsed_block, null );

		// Assert.
		$this->assertSame( $expected, $result );
	}

	public function test_wc_outlet_flag_is_injected_for_outlet_collection(): void {
		// Arrange.
		$parsed_block = array(
			'blockName' => 'woocommerce/product-collection',
			'attrs'     => array(
				'collection' => 'wc-outlet/product-collection/outlet',
			),
		);

		// Act.
		$result = inject_outlet_query_flag_hook( $parsed_block, $parsed_block, null );

		// Assert.
		$this->assertTrue( $result['attrs']['query']['wc_outlet'] );
	}

	public function test_orderby_is_set_for_outlet_product_collection_class_from_request(): void {
		// Arrange.
		$_GET['orderby'] = 'price-desc';
		$parsed_block    = array(
			'blockName' => 'woocommerce/product-collection',
			'attrs'     => array(
				'className' => 'foo wc-outlet-product-collection bar',
				'query'     => array(
					'perPage' => 9,
				),
			),
		);

		// Act.
		$result = set_outlet_product_collection_orderby_hook( $parsed_block );

		// Assert.
		$this->assertSame( 'price-desc', $result['attrs']['query']['orderBy'] );
		$this->assertSame( 9, $result['attrs']['query']['perPage'] );
	}

	public function test_orderby_is_not_set_when_request_orderby_is_not_allowed(): void {
		// Arrange.
		$_GET['orderby'] = 'title';
		$parsed_block    = array(
			'blockName' => 'woocommerce/product-collection',
			'attrs'     => array(
				'className' => 'wc-outlet-product-collection',
				'query'     => array(
					'orderBy' => 'menu_order',
				),
			),
		);
		$expected        = $parsed_block;

		// Act.
		$result = set_outlet_product_collection_orderby_hook( $parsed_block );

		// Assert.
		$this->assertSame( $expected, $result );
	}

	public function test_orderby_is_not_set_when_block_class_does_not_match(): void {
		// Arrange.
		$_GET['orderby'] = 'price';
		$parsed_block    = array(
			'blockName' => 'woocommerce/product-collection',
			'attrs'     => array(
				'className' => 'wc-outlet-other-collection',
			),
		);
		$expected        = $parsed_block;

		// Act.
		$result = set_outlet_product_collection_orderby_hook( $parsed_block );

		// Assert.
		$this->assertSame( $expected, $result );
	}

	public function test_existing_query_keys_are_preserved_when_injecting_wc_outlet_flag(): void {
		// Arrange.
		$parsed_block = array(
			'blockName' => 'woocommerce/product-collection',
			'attrs'     => array(
				'collection' => 'wc-outlet/product-collection/outlet',
				'query'      => array(
					'perPage' => 9,
					'order'   => 'asc',
				),
			),
		);

		// Act.
		$result = inject_outlet_query_flag_hook( $parsed_block, $parsed_block, null );

		// Assert.
		$this->assertSame( 9, $result['attrs']['query']['perPage'] );
		$this->assertSame( 'asc', $result['attrs']['query']['order'] );
		$this->assertTrue( $result['attrs']['query']['wc_outlet'] );
	}

	public function test_render_block_data_orderby_filter_is_registered_by_init_product_collection(): void {
		// Arrange.
		remove_all_filters( 'render_block_data' );

		// Act.
		init_product_collection();

		// Assert.
		$this->assertSame( 10, has_filter( 'render_block_data', 'WC_Outlet\set_outlet_product_collection_orderby_hook' ) );
	}
}
