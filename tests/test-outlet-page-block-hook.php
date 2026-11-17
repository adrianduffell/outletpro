<?php
/**
 * Outlet page section integration tests.
 *
 * @package OutletPro
 */

use function OutletPro\deinit_blocks;
use function OutletPro\init_blocks;
use function OutletPro\init_taxonomies;
use function OutletPro\seed_outlet_status_taxonomy;
use const OutletPro\OUTLET_PAGE_OPTION;
use const OutletPro\PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD;

class Test_Outlet_Page_Block_Hook extends WP_UnitTestCase {

	public function test_catalog_loading_inserts_the_section_after_an_empty_description_without_saving(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		deinit_blocks();
		init_blocks();
		remove_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD );
		$content = '<!-- wp:query-title /--><!-- wp:term-description /-->';
		$id      = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'archive-product',
				'post_content' => $content,
			)
		);
		wp_set_object_terms( $id, get_stylesheet(), 'wp_theme' );

		// Act.
		$template = get_block_template( get_stylesheet() . '//archive-product' );
		$blocks   = parse_blocks( $template->content );

		// Assert.
		$this->assertCount( 3, $blocks );
		$this->assertSame( 'core/term-description', $blocks[1]['blockName'] );
		$this->assertSame( 'outletpro/outlet-conditional', $blocks[2]['blockName'] );
		$this->assertSame( 'page', $blocks[2]['attrs']['conditionType'] );
		$this->assertSame( 'wide', $blocks[2]['attrs']['align'] );
		$this->assertSame( array( 'type' => 'default' ), $blocks[2]['attrs']['layout'] );
		$this->assertSame( 'core/template-part', $blocks[2]['innerBlocks'][0]['blockName'] );
		$this->assertSame( 'outlet-hero', $blocks[2]['innerBlocks'][0]['attrs']['slug'] );
		$this->assertSame( 'wide', $blocks[2]['innerBlocks'][0]['attrs']['align'] );
		$this->assertSame( get_stylesheet(), $blocks[2]['innerBlocks'][0]['attrs']['theme'] );
		$this->assertSame( $content, get_post_field( 'post_content', $id ) );
	}

	public function test_catalog_patterns_respect_custom_and_disabled_anchors(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		deinit_blocks();
		init_blocks();
		set_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD, array( 'core/query-title', 'before' ) );
		register_block_pattern(
			'example/archive-product',
			array(
				'title'   => 'Catalog',
				'content' => '<!-- wp:query-title /-->',
			)
		);

		// Act.
		$content = WP_Block_Patterns_Registry::get_instance()->get_registered( 'example/archive-product' )['content'];
		set_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD, array() );
		$disabled = WP_Block_Patterns_Registry::get_instance()->get_registered( 'example/archive-product' )['content'];
		unregister_block_pattern( 'example/archive-product' );

		// Assert.
		$this->assertSame( 'outletpro/outlet-conditional', parse_blocks( $content )[0]['blockName'] );
		$this->assertSame( '<!-- wp:query-title /-->', $disabled );
	}

	public function test_patterns_declaring_the_catalog_template_type_receive_the_section(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		deinit_blocks();
		init_blocks();
		set_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/store-notices', 'after' ) );
		register_block_pattern(
			'example/product-archive',
			array(
				'title'         => 'Catalog',
				'templateTypes' => array( 'archive-product' ),
				'content'       => '<!-- wp:woocommerce/store-notices /-->',
			)
		);

		// Act.
		$content = WP_Block_Patterns_Registry::get_instance()->get_registered( 'example/product-archive' )['content'];
		unregister_block_pattern( 'example/product-archive' );

		// Assert.
		$this->assertSame( 'outletpro/outlet-conditional', parse_blocks( $content )[1]['blockName'] );
	}

	public function test_unrelated_contexts_and_missing_anchors_are_unchanged(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		deinit_blocks();
		init_blocks();
		set_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD, array( 'core/term-description', 'after' ) );
		$template       = new WP_Block_Template();
		$template->slug = 'page';
		$content        = '<!-- wp:term-description /-->';

		// Act.
		$page           = apply_block_hooks_to_content( $content, $template );
		$pattern        = apply_block_hooks_to_content(
			$content,
			array(
				'name'          => 'example/page',
				'templateTypes' => array( 'page', 'single-product' ),
			)
		);
		$unknown        = apply_block_hooks_to_content( $content );
		$template->slug = 'archive-product';
		$missing        = apply_block_hooks_to_content( '<!-- wp:query-title /-->', $template );

		// Assert.
		$this->assertSame( $content, $page );
		$this->assertSame( $content, $pattern );
		$this->assertSame( $content, $unknown );
		$this->assertSame( '<!-- wp:query-title /-->', $missing );
	}

	public function test_existing_hooked_blocks_are_preserved(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		deinit_blocks();
		init_blocks();
		set_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD, array( 'core/term-description', 'after' ) );
		register_block_type( 'example/peer', array( 'block_hooks' => array( 'core/term-description' => 'after' ) ) );

		// Act.
		$content = apply_block_hooks_to_content( '<!-- wp:term-description /-->', array( 'name' => 'example/archive-product' ) );
		unregister_block_type( 'example/peer' );

		// Assert.
		$this->assertTrue( has_block( 'example/peer', $content ) );
		$this->assertTrue( has_block( 'outletpro/outlet-conditional', $content ) );
	}

	public function test_earlier_suppression_is_preserved(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Restore the suppression filter even when template processing fails.
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		deinit_blocks();
		init_blocks();
		set_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD, array( 'core/term-description', 'after' ) );
		add_filter( 'hooked_block', '__return_null' );

		// Act.
		try {
			$content = apply_block_hooks_to_content( '<!-- wp:term-description /-->', array( 'name' => 'example/archive-product' ) );
		} finally {
			remove_filter( 'hooked_block', '__return_null' );
		}

		// Assert.
		$this->assertSame( '<!-- wp:term-description /-->', $content );
	}

	public function test_rest_move_remove_and_reload_preserve_native_hook_metadata(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		deinit_blocks();
		init_blocks();
		set_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD, array( 'core/term-description', 'after' ) );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$id = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'archive-product',
				'post_content' => '<!-- wp:term-description /-->',
			)
		);
		wp_set_object_terms( $id, get_stylesheet(), 'wp_theme' );
		$template_id = get_stylesheet() . '//archive-product';
		$load        = new WP_REST_Request( 'GET', '/wp/v2/templates/' . $template_id );
		$load->set_param( 'context', 'edit' );
		$blocks = parse_blocks( rest_do_request( $load )->get_data()['content']['raw'] );
		$save   = new WP_REST_Request( 'PUT', '/wp/v2/templates/' . $template_id );
		$save->set_body_params( array( 'content' => serialize_blocks( array_reverse( $blocks ) ) ) );

		// Act.
		$moved_response = rest_do_request( $save );
		$moved          = parse_blocks( get_block_template( $template_id )->content );
		$save->set_body_params( array( 'content' => serialize_block( $blocks[0] ) ) );
		$removed_response = rest_do_request( $save );
		$reloaded         = get_block_template( $template_id )->content;
		$stored           = parse_blocks( get_post_field( 'post_content', $id ) );

		// Assert.
		$this->assertSame( 200, $moved_response->get_status() );
		$this->assertCount( 2, $moved );
		$this->assertSame( 'outletpro/outlet-conditional', $moved[0]['blockName'] );
		$this->assertSame( 200, $removed_response->get_status() );
		$this->assertFalse( has_block( 'outletpro/outlet-conditional', $reloaded ) );
		$this->assertContains( 'outletpro/outlet-conditional', $stored[0]['attrs']['metadata']['ignoredHookedBlocks'] );
	}

	public function test_hero_renders_on_the_outlet_but_not_the_shop(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		deinit_blocks();
		init_blocks();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		set_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD, array( 'core/term-description', 'after' ) );
		update_option( OUTLET_PAGE_OPTION, self::factory()->post->create( array( 'post_type' => 'page' ) ) );
		update_option( 'woocommerce_shop_page_id', self::factory()->post->create( array( 'post_type' => 'page' ) ) );
		$id = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template_part',
				'post_name'    => 'outlet-hero',
				'post_content' => '<!-- wp:paragraph --><p>Saved outlet hero</p><!-- /wp:paragraph -->',
			)
		);
		wp_set_object_terms( $id, get_stylesheet(), 'wp_theme' );
		$content = apply_block_hooks_to_content( '<!-- wp:term-description /-->', array( 'name' => 'example/archive-product' ) );

		// Act.
		$this->go_to( get_permalink( get_option( OUTLET_PAGE_OPTION ) ) );
		$outlet = do_blocks( $content );
		$this->go_to( get_permalink( get_option( 'woocommerce_shop_page_id' ) ) );
		$shop = do_blocks( $content );

		// Assert.
		$this->assertStringContainsString( 'Saved outlet hero', $outlet );
		$this->assertStringNotContainsString( 'Saved outlet hero', $shop );
	}
}
