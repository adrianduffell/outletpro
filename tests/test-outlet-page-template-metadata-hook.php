<?php
/**
 * Tests for the outlet page's native template identity.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_page;
use function OutletPro\init_page;
use const OutletPro\OUTLET_PAGE_OPTION;

class Test_Outlet_Page_Template_Metadata_Hook extends WP_UnitTestCase {

	public function test_block_theme_outlet_page_uses_product_catalog_without_changing_saved_metadata(): void {
		// Arrange.
		deinit_page();
		switch_theme( 'twentytwentyfive' );
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'draft',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );
		update_post_meta( $page_id, '_wp_page_template', 'custom-template' );
		init_page();

		// Act.
		$template = get_page_template_slug( $page_id );
		$metadata = get_post_meta( $page_id );

		// Assert.
		$this->assertSame( 'archive-product', $template );
		$this->assertSame( array( 'custom-template' ), $metadata['_wp_page_template'] );
	}

	public function test_metadata_array_reads_return_the_catalog_template(): void {
		// Arrange.
		deinit_page();
		switch_theme( 'twentytwentyfive' );
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		init_page();

		// Act.
		$templates = get_post_meta( $page_id, '_wp_page_template', false );

		// Assert.
		$this->assertSame( array( 'archive-product' ), $templates );
	}

	public function test_classic_themes_keep_the_saved_page_template(): void {
		// Arrange.
		deinit_page();
		switch_theme( 'storefront' );
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		update_post_meta( $page_id, '_wp_page_template', 'custom-template' );
		init_page();

		// Act.
		$template = get_page_template_slug( $page_id );

		// Assert.
		$this->assertSame( 'custom-template', $template );
	}

	public function test_other_pages_keep_their_saved_template(): void {
		// Arrange.
		deinit_page();
		switch_theme( 'twentytwentyfive' );
		$outlet_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$page_id   = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $outlet_id );
		update_post_meta( $page_id, '_wp_page_template', 'custom-template' );
		init_page();

		// Act.
		$template = get_page_template_slug( $page_id );

		// Assert.
		$this->assertSame( 'custom-template', $template );
	}

	public function test_legacy_outlet_page_template_is_preserved(): void {
		// Arrange.
		deinit_page();
		switch_theme( 'twentytwentyfive' );
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		update_post_meta( $page_id, '_wp_page_template', 'outlet-page' );
		init_page();

		// Act.
		$template = get_page_template_slug( $page_id );

		// Assert.
		$this->assertSame( 'outlet-page', $template );
	}

	public function test_unrelated_metadata_is_preserved(): void {
		// Arrange.
		deinit_page();
		switch_theme( 'twentytwentyfive' );
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		update_post_meta( $page_id, 'custom-key', 'custom-value' );
		init_page();

		// Act.
		$value = get_post_meta( $page_id, 'custom-key', true );

		// Assert.
		$this->assertSame( 'custom-value', $value );
	}

	public function test_invalid_outlet_option_keeps_the_saved_template(): void {
		// Arrange.
		deinit_page();
		switch_theme( 'twentytwentyfive' );
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, 'invalid' );
		update_post_meta( $page_id, '_wp_page_template', 'custom-template' );
		init_page();

		// Act.
		$template = get_page_template_slug( $page_id );

		// Assert.
		$this->assertSame( 'custom-template', $template );
	}

	public function test_deinitializing_restores_the_saved_template(): void {
		// Arrange.
		deinit_page();
		switch_theme( 'twentytwentyfive' );
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		update_post_meta( $page_id, '_wp_page_template', 'custom-template' );
		init_page();
		$this->assertSame( 'archive-product', get_page_template_slug( $page_id ) );

		// Act.
		deinit_page();
		$template = get_page_template_slug( $page_id );

		// Assert.
		$this->assertSame( 'custom-template', $template );
	}

	public function test_an_existing_metadata_override_is_preserved(): void {
		// Arrange.
		deinit_page();
		switch_theme( 'twentytwentyfive' );
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		init_page();
		$override = static fn() => 'other-template';
		add_filter( 'get_post_metadata', $override, 9 );

		// Act.
		$template = get_page_template_slug( $page_id );
		remove_filter( 'get_post_metadata', $override, 9 );

		// Assert.
		$this->assertSame( 'other-template', $template );
	}
}
