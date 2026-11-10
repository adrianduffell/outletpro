<?php
/**
 * Outlet page section integration tests.
 *
 * @package OutletPro
 */

use function OutletPro\create_outlet_template_part;
use function OutletPro\deinit_blocks;
use function OutletPro\get_outlet_page_section_content;
use function OutletPro\get_pattern_content;
use function OutletPro\get_shop_catalog_template;
use function OutletPro\init_blocks;
use function OutletPro\init_catalog;
use function OutletPro\init_patterns;
use function OutletPro\init_taxonomies;
use function OutletPro\seed_theme_mods;
use const OutletPro\OUTLET_PAGE_BLOCK_HOOK_THEME_MOD;
use const OutletPro\OUTLET_PAGE_OPTION;

class Test_Outlet_Page_Block_Hook extends WP_UnitTestCase {

	public function test_seeds_the_default_anchor_without_overwriting_a_disabled_hook(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		remove_theme_mod( OUTLET_PAGE_BLOCK_HOOK_THEME_MOD );

		// Act.
		seed_theme_mods();
		$default = get_theme_mod( OUTLET_PAGE_BLOCK_HOOK_THEME_MOD );
		set_theme_mod( OUTLET_PAGE_BLOCK_HOOK_THEME_MOD, array() );
		seed_theme_mods();

		// Assert.
		$this->assertSame( array( 'core/term-description', 'after' ), $default );
		$this->assertSame( array(), get_theme_mod( OUTLET_PAGE_BLOCK_HOOK_THEME_MOD ) );
	}

	public function test_preserves_a_custom_anchor_when_seeding_theme_mods(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		set_theme_mod( OUTLET_PAGE_BLOCK_HOOK_THEME_MOD, array( 'core/query-title', 'before' ) );

		// Act.
		seed_theme_mods();

		// Assert.
		$this->assertSame( array( 'core/query-title', 'before' ), get_theme_mod( OUTLET_PAGE_BLOCK_HOOK_THEME_MOD ) );
	}

	public function test_catalog_loading_inserts_the_page_section_after_an_empty_description(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		deinit_blocks();
		init_blocks();
		remove_theme_mod( OUTLET_PAGE_BLOCK_HOOK_THEME_MOD );
		$content = '<!-- wp:query-title {"type":"archive"} /--><!-- wp:term-description /--><!-- wp:woocommerce/product-collection {"query":{"inherit":true}} /-->';
		$id      = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'archive-product',
				'post_content' => $content,
			)
		);
		wp_set_object_terms( $id, get_stylesheet(), 'wp_theme' );

		// Act.
		$template = get_shop_catalog_template();
		$blocks   = parse_blocks( $template->content );

		// Assert.
		$this->assertCount( 4, $blocks );
		$this->assertSame( 'core/term-description', $blocks[1]['blockName'] );
		$this->assertSame( 'outletpro/outlet-conditional', $blocks[2]['blockName'] );
		$this->assertSame( 'page', $blocks[2]['attrs']['conditionType'] );
		$this->assertSame( 'outletpro-outlet-page', $blocks[2]['innerBlocks'][0]['attrs']['slug'] );
		$this->assertSame( get_stylesheet(), $blocks[2]['innerBlocks'][0]['attrs']['theme'] );
		$this->assertSame( $content, get_post_field( 'post_content', $id ) );
	}

	public function test_configured_anchor_and_disabled_hook_are_applied_to_catalog_patterns(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		deinit_blocks();
		init_blocks();
		set_theme_mod( OUTLET_PAGE_BLOCK_HOOK_THEME_MOD, array( 'core/query-title', 'before' ) );
		register_block_pattern(
			'example/archive-product',
			array(
				'title'   => 'Catalog',
				'content' => '<!-- wp:query-title /-->',
			)
		);

		// Act.
		$content = WP_Block_Patterns_Registry::get_instance()->get_registered( 'example/archive-product' )['content'];
		set_theme_mod( OUTLET_PAGE_BLOCK_HOOK_THEME_MOD, array() );
		$disabled = WP_Block_Patterns_Registry::get_instance()->get_registered( 'example/archive-product' )['content'];
		unregister_block_pattern( 'example/archive-product' );

		// Assert.
		$this->assertSame( 'outletpro/outlet-conditional', parse_blocks( $content )[0]['blockName'] );
		$this->assertFalse( has_block( 'outletpro/outlet-conditional', $disabled ) );
	}

	public function test_unrelated_templates_patterns_and_missing_anchors_are_unchanged(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		deinit_blocks();
		init_blocks();
		set_theme_mod( OUTLET_PAGE_BLOCK_HOOK_THEME_MOD, array( 'core/term-description', 'after' ) );
		register_block_pattern(
			'example/home',
			array(
				'title'   => 'Home',
				'content' => '<!-- wp:term-description /-->',
			)
		);
		$id = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'archive-product',
				'post_content' => '<!-- wp:query-title /-->',
			)
		);
		wp_set_object_terms( $id, get_stylesheet(), 'wp_theme' );
		$other = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'page',
				'post_content' => '<!-- wp:term-description /-->',
			)
		);
		wp_set_object_terms( $other, get_stylesheet(), 'wp_theme' );

		// Act.
		$missing = get_shop_catalog_template();
		$page    = get_block_template( get_stylesheet() . '//page' );
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'example/home' );
		unregister_block_pattern( 'example/home' );

		// Assert.
		$this->assertFalse( has_block( 'outletpro/outlet-conditional', $missing->content ) );
		$this->assertFalse( has_block( 'outletpro/outlet-conditional', $page->content ) );
		$this->assertFalse( has_block( 'outletpro/outlet-conditional', $pattern['content'] ) );
	}

	public function test_rest_save_move_remove_and_reload_use_native_hook_metadata(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		deinit_blocks();
		init_blocks();
		set_theme_mod( OUTLET_PAGE_BLOCK_HOOK_THEME_MOD, array( 'core/term-description', 'after' ) );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$id = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'archive-product',
				'post_content' => '<!-- wp:term-description /-->',
			)
		);
		wp_set_object_terms( $id, get_stylesheet(), 'wp_theme' );
		$request        = new WP_REST_Request( 'PUT', '/wp/v2/templates/' . get_stylesheet() . '//archive-product' );
		$editor_request = new WP_REST_Request( 'GET', '/wp/v2/templates/' . get_stylesheet() . '//archive-product' );
		$editor_request->set_param( 'context', 'edit' );
		$blocks = parse_blocks( rest_do_request( $editor_request )->get_data()['content']['raw'] );
		$request->set_body_params( array( 'content' => serialize_blocks( array_reverse( $blocks ) ) ) );

		// Act.
		$saved = rest_do_request( $request );
		$moved = get_shop_catalog_template()->content;
		$request->set_body_params( array( 'content' => serialize_block( $blocks[0] ) ) );
		$removed  = rest_do_request( $request );
		$reloaded = get_shop_catalog_template()->content;

		// Assert.
		$this->assertSame( 200, $saved->get_status() );
		$this->assertSame( 1, substr_count( $moved, '<!-- wp:outletpro/outlet-conditional ' ) );
		$this->assertSame( 'outletpro/outlet-conditional', parse_blocks( $moved )[0]['blockName'] );
		$this->assertSame( 200, $removed->get_status() );
		$this->assertFalse( has_block( 'outletpro/outlet-conditional', $reloaded ) );
	}

	public function test_template_part_is_wide_and_preserves_existing_edits(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		create_outlet_template_part( '<!-- wp:paragraph --><p>Navigation</p><!-- /wp:paragraph -->' );
		$part = get_block_template( get_stylesheet() . '//outletpro-outlet-page', 'wp_template_part' );
		wp_update_post(
			array(
				'ID'           => $part->wp_id,
				'post_content' => '<!-- wp:paragraph --><p>My edits</p><!-- /wp:paragraph -->',
			)
		);

		// Act.
		create_outlet_template_part( 'Replacement' );
		$edited = get_block_template( get_stylesheet() . '//outletpro-outlet-page', 'wp_template_part' );

		// Assert.
		$root = parse_blocks( $part->content )[0];
		$this->assertSame( 'wide', $root['attrs']['align'] );
		$this->assertSame( 'constrained', $root['attrs']['layout']['type'] );
		$this->assertArrayNotHasKey( 'style', $root['attrs'] );
		$this->assertStringNotContainsString( 'While supplies last', $part->content );
		$this->assertStringContainsString( 'My edits', $edited->content );
		$this->assertStringNotContainsString( 'Replacement', $edited->content );
	}

	public function test_switching_themes_seeds_only_the_active_block_theme_part(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Mock WooCommerce's external theme-switch loopback request.
		// Arrange.
		init_catalog();
		init_taxonomies();
		init_patterns();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$http_response = static fn() => array(
			'headers'  => array(),
			'body'     => '',
			'response' => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'cookies'  => array(),
		);
		add_filter( 'pre_http_request', $http_response );
		switch_theme( 'storefront' );
		try {
			check_theme_switched();

			// Act.
			switch_theme( 'twentytwentyfive' );
			check_theme_switched();
			$part = get_block_template( get_stylesheet() . '//outletpro-outlet-page', 'wp_template_part' );
		} finally {
			remove_filter( 'pre_http_request', $http_response );
		}

		// Assert.
		$this->assertInstanceOf( WP_Block_Template::class, $part );
		$this->assertStringContainsString( 'wp:outletpro/outlet-menu', $part->content );
		$this->assertNull( get_block_template( 'storefront//outletpro-outlet-page', 'wp_template_part' ) );
	}

	public function test_manual_pattern_uses_the_same_page_section_as_the_hook(): void {
		// Arrange.
		init_patterns();

		// Act.
		$content = get_pattern_content( 'outletpro/outlet-page-section' );
		$section = parse_blocks( get_outlet_page_section_content() )[0];

		// Assert.
		$this->assertSame( 'page', parse_blocks( $content )[0]['attrs']['conditionType'] );
		$this->assertSame( 'core/template-part', $section['innerBlocks'][0]['blockName'] );
		$this->assertStringContainsString( 'outletpro-outlet-page', $content );
	}

	public function test_deinitialization_removes_both_page_block_hooks(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();

		// Act.
		deinit_blocks();

		// Assert.
		$this->assertFalse( has_filter( 'hooked_block_types', 'OutletPro\auto_insert_outlet_page_hook' ) );
		$this->assertFalse( has_filter( 'hooked_block_outletpro/outlet-conditional', 'OutletPro\populate_outlet_page_section_hook' ) );
	}
}
