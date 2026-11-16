<?php
/**
 * Outlet hero discovery tests.
 *
 * @package OutletPro
 */

use function OutletPro\deinit_patterns;
use function OutletPro\init_patterns;
use function OutletPro\init_template_parts;
use const OutletPro\OUTLET_PAGE_OPTION;

class Test_Filter_Block_Templates extends WP_UnitTestCase {

	public function test_lists_the_default_without_creating_a_post_or_requiring_a_page(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		deinit_patterns();
		init_patterns();
		init_template_parts();
		delete_option( OUTLET_PAGE_OPTION );

		// Act.
		$parts = get_block_templates( array( 'slug__in' => array( 'outlet-hero' ) ), 'wp_template_part' );

		// Assert.
		$this->assertCount( 1, $parts );
		$this->assertSame( 'plugin', $parts[0]->source );
		$this->assertEmpty(
			get_posts(
				array(
					'post_type' => 'wp_template_part',
					'name'      => 'outlet-hero',
				)
			)
		);
	}

	public function test_other_template_queries_do_not_receive_the_default(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Exercise independent native query constraints.
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		init_template_parts();
		$queries = array(
			array( 'area' => 'header' ),
			array( 'slug__in' => array( 'other-part' ) ),
			array( 'wp_id' => PHP_INT_MAX ),
		);

		// Act.
		foreach ( $queries as $query ) {
			$slugs = wp_list_pluck( get_block_templates( $query, 'wp_template_part' ), 'slug' );

			// Assert.
			$this->assertNotContains( 'outlet-hero', $slugs );
		}
		$this->assertEmpty( get_block_templates( array( 'slug__in' => array( 'outlet-hero' ) ), 'wp_template' ) );
	}

	public function test_preserves_existing_hero_content_and_area_without_duplicates(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		init_template_parts();
		$post_id = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template_part',
				'post_status'  => 'publish',
				'post_name'    => 'outlet-hero',
				'post_content' => 'Custom layout',
			)
		);
		wp_set_object_terms( $post_id, get_stylesheet(), 'wp_theme' );
		wp_set_object_terms( $post_id, 'header', 'wp_template_part_area' );

		// Act.
		$parts   = get_block_templates( array( 'slug__in' => array( 'outlet-hero' ) ), 'wp_template_part' );
		$general = get_block_templates( array( 'area' => WP_TEMPLATE_PART_AREA_UNCATEGORIZED ), 'wp_template_part' );

		// Assert.
		$this->assertCount( 1, $parts );
		$this->assertSame( $post_id, $parts[0]->wp_id );
		$this->assertSame( 'Custom layout', $parts[0]->content );
		$this->assertNotContains( 'outlet-hero', wp_list_pluck( $general, 'slug' ) );
	}

	public function test_classic_theme_queries_do_not_receive_the_default(): void {
		// Arrange.
		switch_theme( 'storefront' );
		init_template_parts();

		// Act.
		$parts = get_block_templates( array( 'slug__in' => array( 'outlet-hero' ) ), 'wp_template_part' );

		// Assert.
		$this->assertEmpty( $parts );
	}
}
