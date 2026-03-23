<?php
/**
 * Test the clearance_section_empty function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_to_clearance;
use function WC_Clearance\clearance_section_empty;
use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\seed_clearance_status_taxonomy;
use const WC_Clearance\CLEARANCE_STATUS_TAXONOMY;

class Test_Clearance_Section_Empty extends WP_UnitTestCase {

	public function test_throws_exception_when_taxonomy_not_registered(): void {
		// Arrange.
		unregister_taxonomy( CLEARANCE_STATUS_TAXONOMY );

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		clearance_section_empty();
	}

	public function test_returns_true_when_canonical_term_does_not_exist(): void {
		// Arrange.
		register_clearance_status_taxonomy();

		// Act.
		$result = clearance_section_empty();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_returns_true_when_no_products_in_clearance(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();

		// Act.
		$result = clearance_section_empty();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_returns_false_when_products_in_clearance(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();

		$product = \WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );

		// Act.
		$result = clearance_section_empty();

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_ignores_draft_products(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();

		$draft_product = \WC_Helper_Product::create_simple_product();
		wp_update_post(
			array(
				'ID'          => $draft_product->get_id(),
				'post_status' => 'draft',
			)
		);
		add_to_clearance( $draft_product );

		// Act.
		$result = clearance_section_empty();

		// Assert.
		$this->assertTrue( $result );
	}
}
