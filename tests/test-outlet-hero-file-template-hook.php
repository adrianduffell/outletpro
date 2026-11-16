<?php
/**
 * Outlet hero fallback tests.
 *
 * @package OutletPro
 */

use function OutletPro\deinit_patterns;
use function OutletPro\init_patterns;
use function OutletPro\init_template_parts;

class Test_Outlet_Hero_File_Template_Hook extends WP_UnitTestCase {

	public function test_supplies_the_three_category_tiles_pattern_for_the_active_themes_hero(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		deinit_patterns();
		init_patterns();
		init_template_parts();

		// Act.
		$part   = get_block_file_template( get_stylesheet() . '//outlet-hero', 'wp_template_part' );
		$blocks = parse_blocks( $part->content );

		// Assert.
		$this->assertSame( get_stylesheet() . '//outlet-hero', $part->id );
		$this->assertSame( 'Outlet page hero', $part->title );
		$this->assertSame( 'plugin', $part->source );
		$this->assertCount( 1, $blocks );
		$this->assertSame( 'core/pattern', $blocks[0]['blockName'] );
		$this->assertSame( 'outletpro/outlet-navigation-3', $blocks[0]['attrs']['slug'] );
	}

	public function test_does_not_supply_a_whole_template(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		init_template_parts();

		// Act.
		$part = get_block_file_template( get_stylesheet() . '//outlet-hero', 'wp_template' );

		// Assert.
		$this->assertNull( $part );
	}

	public function test_does_not_supply_another_themes_hero(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		init_template_parts();

		// Act.
		$part = get_block_file_template( 'other-theme//outlet-hero', 'wp_template_part' );

		// Assert.
		$this->assertNull( $part );
	}

	public function test_does_not_supply_another_template_part(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		init_template_parts();

		// Act.
		$part = get_block_file_template( get_stylesheet() . '//other-part', 'wp_template_part' );

		// Assert.
		$this->assertNull( $part );
	}

	public function test_preserves_a_theme_provided_part(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		init_template_parts();
		$part         = new WP_Block_Template();
		$part->id     = get_stylesheet() . '//outlet-hero';
		$part->source = 'theme';
		$provider     = static fn() => $part;
		add_filter( 'get_block_file_template', $provider, 5 );

		// Act.
		$result = get_block_file_template( $part->id, 'wp_template_part' );

		// Assert.
		$this->assertSame( $part, $result );
	}

	public function test_missing_navigation_returns_no_default(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		deinit_patterns();
		init_template_parts();

		// Act.
		$part = get_block_file_template( get_stylesheet() . '//outlet-hero', 'wp_template_part' );

		// Assert.
		$this->assertNull( $part );
	}

	public function test_classic_themes_do_not_receive_a_default(): void {
		// Arrange.
		switch_theme( 'storefront' );
		init_template_parts();

		// Act.
		$part = get_block_file_template( get_stylesheet() . '//outlet-hero', 'wp_template_part' );

		// Assert.
		$this->assertNull( $part );
	}
}
