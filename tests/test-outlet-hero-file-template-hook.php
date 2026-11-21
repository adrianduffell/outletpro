<?php
/**
 * Outlet hero fallback tests.
 *
 * @package OutletPro
 */

use function OutletPro\deinit_patterns;
use function OutletPro\init_patterns;
use function OutletPro\init_taxonomies;
use function OutletPro\init_template_parts;

class Test_Outlet_Hero_File_Template_Hook extends WP_UnitTestCase {

	public function test_supplies_the_three_category_tiles_pattern_for_the_active_themes_hero(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		deinit_patterns();
		init_patterns();
		init_template_parts();
		self::factory()->term->create_many( 3, array( 'taxonomy' => 'product_cat' ) );

		// Act.
		$part   = get_block_file_template( get_stylesheet() . '//outlet-hero', 'wp_template_part' );
		$blocks = parse_blocks( $part->content );

		// Assert.
		$this->assertSame( get_stylesheet() . '//outlet-hero', $part->id );
		$this->assertSame( 'Outlet hero', $part->title );
		$this->assertSame( 'plugin', $part->source );
		$this->assertCount( 1, $blocks );
		$this->assertSame( 'core/group', $blocks[0]['blockName'] );
		$this->assertSame( 'outletpro/outlet-navigation-3', $blocks[0]['attrs']['metadata']['patternName'] );
		$this->assertSame( 'Outlet navigation with three category tiles', $blocks[0]['attrs']['metadata']['name'] );
		$this->assertStringNotContainsString( '<!-- wp:pattern ', $part->content );
	}

	public function test_supplies_four_tiles_for_four_top_level_categories(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		deinit_patterns();
		init_patterns();
		init_template_parts();
		self::factory()->term->create_many( 4, array( 'taxonomy' => 'product_cat' ) );

		// Act.
		$part   = get_block_file_template( get_stylesheet() . '//outlet-hero', 'wp_template_part' );
		$blocks = parse_blocks( $part->content );

		// Assert.
		$this->assertSame( 'outletpro/outlet-navigation-4', $blocks[0]['attrs']['metadata']['patternName'] );
	}

	public function test_supplies_price_tiers_for_two_top_level_categories(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		init_taxonomies();
		deinit_patterns();
		init_patterns();
		init_template_parts();
		self::factory()->term->create_many( 2, array( 'taxonomy' => 'product_cat' ) );

		// Act.
		$part   = get_block_file_template( get_stylesheet() . '//outlet-hero', 'wp_template_part' );
		$blocks = parse_blocks( $part->content );

		// Assert.
		$this->assertSame( 'outletpro/outlet-navigation-price-tiers', $blocks[0]['attrs']['metadata']['patternName'] );
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

	public function test_empty_navigation_returns_no_default(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		self::factory()->term->create_many( 3, array( 'taxonomy' => 'product_cat' ) );
		deinit_patterns();
		register_block_pattern(
			'outletpro/outlet-navigation-3',
			array(
				'title'   => 'Empty navigation',
				'content' => '',
			)
		);
		init_template_parts();

		// Act.
		$part = get_block_file_template( get_stylesheet() . '//outlet-hero', 'wp_template_part' );

		// Assert.
		$this->assertNull( $part );
	}

	public function test_category_lookup_failure_returns_no_default(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		init_template_parts();
		$failure = static fn( array $terms, array $taxonomies ) => in_array( 'product_cat', $taxonomies, true ) ? new WP_Error( 'lookup_failed' ) : $terms;
		add_filter( 'get_terms', $failure, 10, 2 );

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
