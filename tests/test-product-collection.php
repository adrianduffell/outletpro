<?php
/**
 * Test the product collection functions.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\enqueue_product_collection_script;
use function WC_Clearance\init_product_collection;
use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\seed_clearance_status_taxonomy;
use const WC_Clearance\CLEARANCE_STATUS_TAXONOMY;

class Test_Product_Collection extends WP_UnitTestCase {

	public function test_init_product_collection_registers_enqueue_action(): void {
		// Arrange.
		remove_all_actions( 'enqueue_block_editor_assets' );

		// Act.
		init_product_collection();

		// Assert.
		$this->assertSame( 10, has_action( 'enqueue_block_editor_assets', 'WC_Clearance\enqueue_product_collection_script' ) );
	}

	public function test_enqueue_product_collection_script_does_not_enqueue_when_term_missing(): void {
		// Arrange.
		register_clearance_status_taxonomy();

		// Act.
		enqueue_product_collection_script();

		// Assert.
		$this->assertFalse( wp_script_is( 'wc-clearance-product-collection', 'enqueued' ) );
	}

	public function test_enqueue_product_collection_script_enqueues_when_term_exists(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();

		// Act.
		enqueue_product_collection_script();

		// Assert.
		$this->assertTrue( wp_script_is( 'wc-clearance-product-collection', 'enqueued' ) );
	}

	public function test_enqueue_product_collection_script_localizes_term_id(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$canonical_term = get_term_by( 'name', 'clearance', CLEARANCE_STATUS_TAXONOMY );

		// Act.
		enqueue_product_collection_script();

		// Assert.
		$scripts        = wp_scripts();
		$localized_data = $scripts->get_data( 'wc-clearance-product-collection', 'data' );
		$this->assertStringContainsString( (string) $canonical_term->term_id, $localized_data );
	}
}
