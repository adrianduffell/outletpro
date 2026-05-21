<?php
/**
 * Tests for the outlet filter tiles block pattern.
 *
 * @package WC_Outlet
 */

use function WC_Outlet\get_outlet_filter_price_tiers;
use function WC_Outlet\get_outlet_filter_tiles_content;
use function WC_Outlet\register_outlet_filter_tiles_pattern;

class Test_Register_Outlet_Filter_Tiles_Pattern extends WP_UnitTestCase {

	public function test_pattern_is_registered_after_register_outlet_filter_tiles_pattern(): void {
		// Act.
		register_outlet_filter_tiles_pattern();

		// Assert.
		$this->assertTrue( \WP_Block_Patterns_Registry::get_instance()->is_registered( 'wc-outlet/outlet-filter-tiles' ) );
	}

	public function test_pattern_title_is_outlet_filter_tiles(): void {
		// Act.
		register_outlet_filter_tiles_pattern();

		// Assert.
		$pattern = \WP_Block_Patterns_Registry::get_instance()->get_registered( 'wc-outlet/outlet-filter-tiles' );
		$this->assertSame( 'Outlet filter tiles', $pattern['title'] );
	}

	public function test_get_outlet_filter_price_tiers_returns_usd_tiers_for_usd(): void {
		// Arrange / Act.
		$tiers = get_outlet_filter_price_tiers( 'USD' );

		// Assert.
		$this->assertSame( array( 10, 25, 50 ), $tiers );
	}

	public function test_get_outlet_filter_price_tiers_returns_eur_tiers_for_eur(): void {
		// Arrange / Act.
		$tiers = get_outlet_filter_price_tiers( 'EUR' );

		// Assert.
		$this->assertSame( array( 10, 25, 50 ), $tiers );
	}

	public function test_get_outlet_filter_price_tiers_returns_gbp_tiers_for_gbp(): void {
		// Arrange / Act.
		$tiers = get_outlet_filter_price_tiers( 'GBP' );

		// Assert.
		$this->assertSame( array( 10, 20, 40 ), $tiers );
	}

	public function test_get_outlet_filter_price_tiers_returns_jpy_tiers_for_jpy(): void {
		// Arrange / Act.
		$tiers = get_outlet_filter_price_tiers( 'JPY' );

		// Assert.
		$this->assertSame( array( 1000, 3000, 5000 ), $tiers );
	}

	public function test_get_outlet_filter_price_tiers_defaults_to_usd_for_unknown_currency(): void {
		// Arrange / Act.
		$tiers = get_outlet_filter_price_tiers( 'XYZ' );

		// Assert.
		$this->assertSame( array( 10, 25, 50 ), $tiers );
	}

	public function test_get_outlet_filter_tiles_content_contains_usd_max_prices_by_default(): void {
		// Arrange.
		update_option( 'woocommerce_currency', 'USD' );

		// Act.
		$content = get_outlet_filter_tiles_content();

		// Assert.
		$this->assertStringContainsString( 'max_price=10', $content );
		$this->assertStringContainsString( 'max_price=25', $content );
		$this->assertStringContainsString( 'max_price=50', $content );
	}

	public function test_get_outlet_filter_tiles_content_contains_currency_specific_max_prices(): void {
		// Arrange.
		update_option( 'woocommerce_currency', 'JPY' );

		// Act.
		$content = get_outlet_filter_tiles_content();

		// Assert.
		$this->assertStringContainsString( 'max_price=1000', $content );
		$this->assertStringContainsString( 'max_price=3000', $content );
		$this->assertStringContainsString( 'max_price=5000', $content );
	}

	public function test_get_outlet_filter_tiles_content_defaults_to_usd_for_unknown_currency(): void {
		// Arrange.
		update_option( 'woocommerce_currency', 'XYZ' );

		// Act.
		$content = get_outlet_filter_tiles_content();

		// Assert.
		$this->assertStringContainsString( 'max_price=10', $content );
		$this->assertStringContainsString( 'max_price=25', $content );
		$this->assertStringContainsString( 'max_price=50', $content );
	}

	public function test_get_outlet_filter_tiles_content_contains_all_outlet_link(): void {
		// Arrange.
		update_option( 'woocommerce_currency', 'USD' );

		// Act.
		$content = get_outlet_filter_tiles_content();

		// Assert.
		$this->assertStringContainsString( 'href="./"', $content );
		$this->assertStringContainsString( 'All outlet', $content );
	}

	public function test_get_outlet_filter_tiles_content_contains_buttons_block_markup(): void {
		// Arrange.
		update_option( 'woocommerce_currency', 'USD' );

		// Act.
		$content = get_outlet_filter_tiles_content();

		// Assert.
		$this->assertStringContainsString( '<!-- wp:buttons', $content );
		$this->assertStringContainsString( '<!-- wp:button', $content );
		$this->assertStringContainsString( '<!-- /wp:buttons -->', $content );
	}

	public function test_get_outlet_filter_tiles_content_first_button_has_base_background(): void {
		// Arrange.
		update_option( 'woocommerce_currency', 'USD' );

		// Act.
		$content = get_outlet_filter_tiles_content();

		// Assert.
		$this->assertStringContainsString( '"backgroundColor":"base"', $content );
		$this->assertStringContainsString( 'has-base-background-color', $content );
	}
}
