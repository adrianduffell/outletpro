<?php
/**
 * Tests for append_block_editor_settings_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\init_block_editor;
use function OutletPro\register_outlet_status_taxonomy;
use function OutletPro\seed_outlet_status_taxonomy;
use const OutletPro\OUTLET_STATUS_CANONICAL_TERM;
use const OutletPro\OUTLET_STATUS_TAXONOMY;

class Test_Append_Block_Editor_Settings_Hook extends WP_UnitTestCase {

	public function test_settings_contain_outlet_term_id(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		init_block_editor();
		$canonical_term = get_term_by( 'name', OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY );

		// Act.
		$settings = apply_filters( 'block_editor_settings_all', array(), new WP_Block_Editor_Context() );

		// Assert.
		$this->assertArrayHasKey( 'wcOutletCanonicalTermId', $settings );
		$this->assertSame( $canonical_term->term_id, $settings['wcOutletCanonicalTermId'] );
	}

	public function test_canonical_term_setting_is_omitted_when_canonical_term_missing(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		init_block_editor();
		$initial_settings = array( 'foo' => 'bar' );

		// Act.
		$settings = apply_filters( 'block_editor_settings_all', $initial_settings, new WP_Block_Editor_Context() );

		// Assert.
		$this->assertArrayNotHasKey( 'wcOutletCanonicalTermId', $settings );
		$this->assertArrayHasKey( 'foo', $settings );
		$this->assertSame( 'bar', $settings['foo'] );
	}

	public function test_existing_settings_are_preserved(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		init_block_editor();
		$initial_settings = array( 'foo' => 'bar' );

		// Act.
		$settings = apply_filters( 'block_editor_settings_all', $initial_settings, new WP_Block_Editor_Context() );

		// Assert.
		$this->assertArrayHasKey( 'foo', $settings );
		$this->assertSame( 'bar', $settings['foo'] );
	}

	public function test_settings_identify_block_theme(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		init_block_editor();

		// Act.
		$settings = apply_filters( 'block_editor_settings_all', array(), new WP_Block_Editor_Context() );

		// Assert.
		$this->assertTrue( $settings['outletproIsBlockTheme'] );
	}

	public function test_settings_identify_classic_theme(): void {
		// Arrange.
		switch_theme( 'storefront' );
		init_block_editor();

		// Act.
		$settings = apply_filters( 'block_editor_settings_all', array(), new WP_Block_Editor_Context() );

		// Assert.
		$this->assertFalse( $settings['outletproIsBlockTheme'] );
	}

	public function test_settings_contain_cart_url(): void {
		// Arrange.
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( 'woocommerce_cart_page_id', $page_id );
		init_block_editor();

		// Act.
		$settings = apply_filters( 'block_editor_settings_all', array(), new WP_Block_Editor_Context() );

		// Assert.
		$this->assertSame( get_permalink( $page_id ), $settings['outletproCartUrl'] );
	}
}
