<?php
/**
 * Tests for product category editor settings.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\init_block_editor;
use function OutletPro\init_taxonomies;

class Test_Append_Product_Category_Editor_Settings_Hook extends WP_UnitTestCase {

	public function test_includes_empty_top_level_categories_in_menu_order(): void {
		// Arrange.
		init_taxonomies();
		init_block_editor();
		$alpha = self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'name'     => 'Alpha',
			)
		);
		$zebra = self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'name'     => 'Zebra',
			)
		);
		$child = self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'parent'   => $zebra,
			)
		);
		update_term_meta( $alpha, 'order', 2 );
		update_term_meta( $zebra, 'order', 1 );
		$expected       = new stdClass();
		$expected->id   = $zebra;
		$expected->name = 'Zebra';

		// Act.
		$settings = apply_filters( 'block_editor_settings_all', array(), new WP_Block_Editor_Context() );

		// Assert.
		$ids = array_column( $settings['outletproProductCategories'], 'id' );
		$this->assertSame( array( $zebra, $alpha ), array_values( array_intersect( $ids, array( $alpha, $zebra ) ) ) );
		$this->assertNotContains( $child, $ids );
		$this->assertContainsEquals( $expected, $settings['outletproProductCategories'] );
	}

	public function test_category_query_failure_returns_empty_setting(): void {
		// Arrange.
		init_taxonomies();
		init_block_editor();
		$fail_query = static fn() => new WP_Error( 'query_failed' );
		add_filter( 'get_terms', $fail_query );

		// Act.
		$settings = apply_filters( 'block_editor_settings_all', array(), new WP_Block_Editor_Context() );
		remove_filter( 'get_terms', $fail_query );

		// Assert.
		$this->assertSame( array(), $settings['outletproProductCategories'] );
	}
}
