<?php
/**
 * Product catalog Block Hooks context tests.
 *
 * @package OutletPro
 */

use function OutletPro\is_product_catalog_context;

class Test_Is_Product_Catalog_Context extends WP_UnitTestCase {

	public function test_recognizes_catalog_template_slug_variations(): void {
		// Arrange.
		$template       = new WP_Block_Template();
		$template->slug = 'archive-product-custom';

		// Act.
		$result = is_product_catalog_context( $template );

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_rejects_unrelated_templates(): void {
		// Arrange.
		$template       = new WP_Block_Template();
		$template->slug = 'page';

		// Act.
		$result = is_product_catalog_context( $template );

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_catalog_pattern_names_match_even_with_invalid_template_types(): void {
		// Arrange.
		$context = array(
			'name'          => 'example/hidden-archive-product',
			'templateTypes' => 'invalid',
		);

		// Act.
		$result = is_product_catalog_context( $context );

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_catalog_template_types_match_without_a_matching_pattern_name(): void {
		// Arrange.
		$context = array( 'templateTypes' => array( 'page', 'archive-product' ) );

		// Act.
		$missing_name = is_product_catalog_context( $context );
		$invalid_name = is_product_catalog_context( array_merge( $context, array( 'name' => 123 ) ) );
		$other_name   = is_product_catalog_context( array_merge( $context, array( 'name' => 'example/product-archive' ) ) );

		// Assert.
		$this->assertTrue( $missing_name );
		$this->assertTrue( $invalid_name );
		$this->assertTrue( $other_name );
	}

	public function test_rejects_unsupported_contexts_and_invalid_pattern_metadata(): void {
		// Arrange.
		$contexts = array(
			null,
			new stdClass(),
			array(),
			array(
				'name'          => 123,
				'templateTypes' => 'archive-product',
			),
			array(
				'name'          => 'example/home',
				'templateTypes' => array( 'page', 123 ),
			),
		);

		// Act.
		$results = array_map( 'OutletPro\is_product_catalog_context', $contexts );

		// Assert.
		$this->assertSame( array( false, false, false, false, false ), $results );
	}
}
