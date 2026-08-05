<?php
/**
 * Tests for the outlet filter tiles block pattern.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_outlet_filter_tiles_content;
use function OutletPro\register_outlet_filter_tiles_pattern;

class Test_Register_Outlet_Filter_Tiles_Pattern extends WP_UnitTestCase {

	public function test_pattern_is_registered_after_register_outlet_filter_tiles_pattern(): void {
		// Act.
		register_outlet_filter_tiles_pattern();

		// Assert.
		$this->assertTrue( \WP_Block_Patterns_Registry::get_instance()->is_registered( 'outletpro/outlet-filter-tiles' ) );
	}

	public function test_pattern_title_is_outlet_filter_tiles(): void {
		// Act.
		register_outlet_filter_tiles_pattern();

		// Assert.
		$pattern = \WP_Block_Patterns_Registry::get_instance()->get_registered( 'outletpro/outlet-filter-tiles' );
		$this->assertSame( 'Outlet filter tiles', $pattern['title'] );
	}

	public function test_get_outlet_filter_tiles_content_contains_usd_max_prices_by_default(): void {
		// Arrange.
		update_option( 'woocommerce_currency', 'USD' );
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);
		update_option( 'outletpro_page_id', $page_id );

		// Act.
		$content = get_outlet_filter_tiles_content();

		// Assert.
		$this->assertStringContainsString( 'max_price=10', $content );
		$this->assertStringContainsString( 'max_price=25', $content );
		$this->assertStringContainsString( 'max_price=50', $content );

		// Cleanup.
		delete_option( 'outletpro_page_id' );
	}

	public function test_get_outlet_filter_tiles_content_contains_currency_specific_max_prices(): void {
		// Arrange.
		update_option( 'woocommerce_currency', 'JPY' );
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);
		update_option( 'outletpro_page_id', $page_id );

		// Act.
		$content = get_outlet_filter_tiles_content();

		// Assert.
		$this->assertStringContainsString( 'max_price=1000', $content );
		$this->assertStringContainsString( 'max_price=3000', $content );
		$this->assertStringContainsString( 'max_price=5000', $content );

		// Cleanup.
		delete_option( 'outletpro_page_id' );
	}

	public function test_get_outlet_filter_tiles_content_defaults_to_usd_for_unknown_currency(): void {
		// Arrange.
		update_option( 'woocommerce_currency', 'XYZ' );
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);
		update_option( 'outletpro_page_id', $page_id );

		// Act.
		$content = get_outlet_filter_tiles_content();

		// Assert.
		$this->assertStringContainsString( 'max_price=10', $content );
		$this->assertStringContainsString( 'max_price=25', $content );
		$this->assertStringContainsString( 'max_price=50', $content );

		// Cleanup.
		delete_option( 'outletpro_page_id' );
	}

	public function test_get_outlet_filter_tiles_content_returns_empty_when_no_outlet_page(): void {
		// Arrange.
		update_option( 'woocommerce_currency', 'USD' );
		delete_option( 'outletpro_page_id' );

		// Act.
		$content = get_outlet_filter_tiles_content();

		// Assert.
		$this->assertSame( '', $content );
	}

	public function test_get_outlet_filter_tiles_content_uses_outlet_page_permalink(): void {
		// Arrange.
		update_option( 'woocommerce_currency', 'USD' );
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);
		update_option( 'outletpro_page_id', $page_id );
		$permalink = get_permalink( $page_id );

		// Act.
		$content = get_outlet_filter_tiles_content();

		// Assert.
		$this->assertStringContainsString( $permalink, $content );

		// Cleanup.
		delete_option( 'outletpro_page_id' );
	}

	public function test_get_outlet_filter_tiles_content_contains_buttons_block_markup(): void {
		// Arrange.
		update_option( 'woocommerce_currency', 'USD' );
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);
		update_option( 'outletpro_page_id', $page_id );

		// Act.
		$content = get_outlet_filter_tiles_content();

		// Assert.
		$this->assertStringContainsString( '<!-- wp:buttons', $content );
		$this->assertStringContainsString( '<!-- wp:button', $content );
		$this->assertStringContainsString( '<!-- /wp:buttons -->', $content );

		// Cleanup.
		delete_option( 'outletpro_page_id' );
	}

	public function test_get_outlet_filter_tiles_content_does_not_include_outletpro_filter_tiles_class(): void {
		// Arrange.
		update_option( 'woocommerce_currency', 'USD' );
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);
		update_option( 'outletpro_page_id', $page_id );

		// Act.
		$content = get_outlet_filter_tiles_content();

		// Assert.
		$this->assertStringNotContainsString( 'outletpro-filter-tiles', $content );

		// Cleanup.
		delete_option( 'outletpro_page_id' );
	}

	public function test_get_outlet_filter_tiles_content_does_not_include_metadata_by_default(): void {
		// Arrange.
		update_option( 'woocommerce_currency', 'USD' );
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);
		update_option( 'outletpro_page_id', $page_id );

		// Act.
		$content = get_outlet_filter_tiles_content();

		// Assert.
		$this->assertStringNotContainsString( '"metadata"', $content );

		// Cleanup.
		delete_option( 'outletpro_page_id' );
	}

	public function test_get_outlet_filter_tiles_content_includes_metadata_when_include_metadata_is_true(): void {
		// Arrange.
		update_option( 'woocommerce_currency', 'USD' );
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);
		update_option( 'outletpro_page_id', $page_id );

		// Act.
		$content = get_outlet_filter_tiles_content( true );

		// Assert.
		$this->assertStringContainsString( '"metadata"', $content );
		$this->assertStringContainsString( '"categories":["outletpro"]', $content );
		$this->assertStringContainsString( '"patternName":"outletpro/outlet-filter-tiles"', $content );
		$this->assertStringContainsString( '"name":"Outlet filter tiles"', $content );

		// Cleanup.
		delete_option( 'outletpro_page_id' );
	}
}
