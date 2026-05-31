<?php
/**
 * Tests for the outlet sort filter block pattern.
 *
 * @package WC_Outlet
 */

use function WC_Outlet\deinit_patterns;
use function WC_Outlet\get_outlet_sort_filter_pattern_content;
use function WC_Outlet\register_outlet_sort_filter_pattern;

class Test_Register_Outlet_Sort_Filter_Pattern extends WP_UnitTestCase {

	public function test_pattern_is_registered_after_register_outlet_sort_filter_pattern(): void {
		// Arrange.
		deinit_patterns();

		// Act.
		register_outlet_sort_filter_pattern();

		// Assert.
		$this->assertTrue( \WP_Block_Patterns_Registry::get_instance()->is_registered( 'wc-outlet/outlet-sort-filter' ) );
	}

	public function test_pattern_has_expected_title_and_description(): void {
		// Arrange.
		deinit_patterns();

		// Act.
		register_outlet_sort_filter_pattern();

		// Assert.
		$pattern = \WP_Block_Patterns_Registry::get_instance()->get_registered( 'wc-outlet/outlet-sort-filter' );
		$this->assertSame( 'Outlet sort filter', $pattern['title'] );
		$this->assertSame( 'Dropdown sort filter for the outlet page.', $pattern['description'] );
	}

	public function test_get_outlet_sort_filter_pattern_content_contains_html_block_markup(): void {
		// Arrange.
		deinit_patterns();

		// Act.
		$content = get_outlet_sort_filter_pattern_content();

		// Assert.
		$this->assertStringContainsString( '<!-- wp:html -->', $content );
		$this->assertStringContainsString( 'data-wp-block-html="js"', $content );
	}
}
