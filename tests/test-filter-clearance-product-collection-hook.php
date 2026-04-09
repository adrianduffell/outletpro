<?php
/**
 * Tests for filter_clearance_product_collection_hook().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\init_product_collection;
use function WC_Clearance\init_taxonomies;
use function WC_Clearance\seed_clearance_status_taxonomy;
use const WC_Clearance\CLEARANCE_STATUS_TAXONOMY;

class Test_Filter_Clearance_Product_Collection_Hook extends WP_UnitTestCase {

	/**
	 * Create a WP_Block instance with the given context.
	 *
	 * @param array<string, mixed> $context
	 * @return WP_Block
	 */
	private function make_block( array $context ): WP_Block {
		$block          = new WP_Block(
			array(
				'blockName'    => 'core/query',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			)
		);
		$block->context = $context;
		return $block;
	}

	public function test_query_is_unchanged_when_not_product_collection_block(): void {
		// Arrange.
		$query    = array( 'post_type' => 'product' );
		$block    = $this->make_block( array( 'query' => array( 'isProductCollectionBlock' => false ) ) );
		$expected = $query;

		// Act.
		$result = apply_filters( 'query_loop_block_query_vars', $query, $block, 1 );

		// Assert.
		$this->assertSame( $expected, $result );
	}

	public function test_query_is_unchanged_when_product_collection_block_flag_is_missing(): void {
		// Arrange.
		$query    = array( 'post_type' => 'product' );
		$block    = $this->make_block( array() );
		$expected = $query;

		// Act.
		$result = apply_filters( 'query_loop_block_query_vars', $query, $block, 1 );

		// Assert.
		$this->assertSame( $expected, $result );
	}

	public function test_query_is_unchanged_when_collection_is_different(): void {
		// Arrange.
		$query = array( 'post_type' => 'product' );
		$block = $this->make_block(
			array(
				'query'      => array( 'isProductCollectionBlock' => true ),
				'collection' => 'wc-clearance/product-collection/other',
			)
		);
		$expected = $query;

		// Act.
		$result = apply_filters( 'query_loop_block_query_vars', $query, $block, 1 );

		// Assert.
		$this->assertSame( $expected, $result );
	}

	public function test_query_is_unchanged_when_collection_is_missing(): void {
		// Arrange.
		$query    = array( 'post_type' => 'product' );
		$block    = $this->make_block( array( 'query' => array( 'isProductCollectionBlock' => true ) ) );
		$expected = $query;

		// Act.
		$result = apply_filters( 'query_loop_block_query_vars', $query, $block, 1 );

		// Assert.
		$this->assertSame( $expected, $result );
	}

	public function test_post_in_forces_no_results_when_canonical_term_missing(): void {
		// Arrange.
		init_taxonomies();
		// Do not seed the taxonomy so the canonical term is absent.
		$query = array( 'post_type' => 'product' );
		$block = $this->make_block(
			array(
				'query'      => array( 'isProductCollectionBlock' => true ),
				'collection' => 'wc-clearance/product-collection/clearance',
			)
		);

		// Act.
		$result = apply_filters( 'query_loop_block_query_vars', $query, $block, 1 );

		// Assert.
		$this->assertSame( array( 0 ), $result['post__in'] );
	}

	public function test_tax_query_is_added_when_canonical_term_exists(): void {
		// Arrange.
		init_taxonomies();
		seed_clearance_status_taxonomy();
		$canonical_term = get_term_by( 'name', 'clearance', CLEARANCE_STATUS_TAXONOMY );
		$query          = array( 'post_type' => 'product' );
		$block          = $this->make_block(
			array(
				'query'      => array( 'isProductCollectionBlock' => true ),
				'collection' => 'wc-clearance/product-collection/clearance',
			)
		);

		// Act.
		$result = apply_filters( 'query_loop_block_query_vars', $query, $block, 1 );

		// Assert.
		$this->assertArrayHasKey( 'tax_query', $result );
		$this->assertCount( 1, $result['tax_query'] );
		$this->assertSame( CLEARANCE_STATUS_TAXONOMY, $result['tax_query'][0]['taxonomy'] );
		$this->assertSame( 'term_id', $result['tax_query'][0]['field'] );
		$this->assertSame( $canonical_term->term_id, $result['tax_query'][0]['terms'] );
	}

	public function test_existing_tax_query_entries_are_preserved(): void {
		// Arrange.
		init_taxonomies();
		seed_clearance_status_taxonomy();
		$existing_tax_clause = array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => array( 'clothing' ),
		);
		$query               = array(
			'post_type' => 'product',
			'tax_query' => array( $existing_tax_clause ),
		);
		$block               = $this->make_block(
			array(
				'query'      => array( 'isProductCollectionBlock' => true ),
				'collection' => 'wc-clearance/product-collection/clearance',
			)
		);

		// Act.
		$result = apply_filters( 'query_loop_block_query_vars', $query, $block, 1 );

		// Assert.
		$this->assertCount( 2, $result['tax_query'] );
		$this->assertSame( $existing_tax_clause, $result['tax_query'][0] );
		$this->assertSame( CLEARANCE_STATUS_TAXONOMY, $result['tax_query'][1]['taxonomy'] );
	}

	public function test_filter_is_registered_by_init_product_collection(): void {
		// Arrange.
		remove_all_filters( 'query_loop_block_query_vars' );

		// Act.
		init_product_collection();

		// Assert.
		$this->assertSame( 11, has_filter( 'query_loop_block_query_vars', 'WC_Clearance\filter_clearance_product_collection_hook' ) );
	}
}
