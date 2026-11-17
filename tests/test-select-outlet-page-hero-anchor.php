<?php
/**
 * Tests for select_outlet_page_hero_anchor().
 *
 * @package OutletPro
 */

use function OutletPro\deinit_blocks;
use function OutletPro\get_catalog_anchor_order;
use function OutletPro\init_blocks;
use function OutletPro\seed_theme_mods;
use function OutletPro\select_outlet_page_hero_anchor;
use const OutletPro\PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD;

class Test_Select_Outlet_Page_Hero_Anchor extends WP_UnitTestCase {

	public function test_landmarks_are_read_in_order_across_nested_groups_and_columns(): void {
		// Arrange.
		$blocks = parse_blocks( '<!-- wp:group --><div class="wp-block-group"><!-- wp:woocommerce/store-notices /--><!-- wp:columns --><div class="wp-block-columns"><!-- wp:column --><div class="wp-block-column"><!-- wp:query-title /--><!-- wp:term-description /--></div><!-- /wp:column --></div><!-- /wp:columns --></div><!-- /wp:group --><!-- wp:woocommerce/store-notices /-->' );

		// Act.
		$anchors = get_catalog_anchor_order( $blocks );

		// Assert.
		$this->assertSame( array( 'woocommerce/store-notices', 'core/query-title', 'core/term-description', 'woocommerce/store-notices' ), $anchors );
	}

	public function test_seeds_after_notices_below_a_column_description_in_a_catalog_pattern(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		deinit_blocks();
		init_blocks();
		remove_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD );
		register_block_pattern(
			'example/woo-product-archive',
			array(
				'title'         => 'Product archive',
				'templateTypes' => array( 'archive-product' ),
				'content'       => '<!-- wp:columns --><div class="wp-block-columns"><!-- wp:column --><div class="wp-block-column"><!-- wp:query-title /--></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:term-description /--></div><!-- /wp:column --></div><!-- /wp:columns --><!-- wp:group {"align":"wide"} --><div class="wp-block-group alignwide"><!-- wp:woocommerce/store-notices /--></div><!-- /wp:group --><!-- wp:woocommerce/product-results-count /-->',
			)
		);
		$id = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'archive-product',
				'post_content' => '<!-- wp:pattern {"slug":"example/woo-product-archive"} /-->',
			)
		);
		wp_set_object_terms( $id, get_stylesheet(), 'wp_theme' );

		// Act.
		seed_theme_mods();
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'example/woo-product-archive' );
		$blocks  = parse_blocks( $pattern['content'] );
		unregister_block_pattern( 'example/woo-product-archive' );

		// Assert.
		$this->assertSame( array( 'woocommerce/store-notices', 'after' ), get_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD ) );
		$this->assertCount( 1, $blocks[0]['innerBlocks'][1]['innerBlocks'] );
		$this->assertSame( 'core/term-description', $blocks[0]['innerBlocks'][1]['innerBlocks'][0]['blockName'] );
		$this->assertSame( 'wide', $blocks[1]['attrs']['align'] );
		$this->assertSame( 'woocommerce/store-notices', $blocks[1]['innerBlocks'][0]['blockName'] );
		$this->assertSame( 'outletpro/outlet-conditional', $blocks[1]['innerBlocks'][1]['blockName'] );
		$this->assertSame( 'woocommerce/product-results-count', $blocks[2]['blockName'] );
		$this->assertSame( '<!-- wp:pattern {"slug":"example/woo-product-archive"} /-->', get_post_field( 'post_content', $id ) );
	}

	public function test_keeps_description_anchor_when_notices_precede_the_description(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		remove_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD );
		$id = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'archive-product',
				'post_content' => '<!-- wp:woocommerce/store-notices /--><!-- wp:group --><div class="wp-block-group"><!-- wp:query-title /--><!-- wp:term-description /--></div><!-- /wp:group -->',
			)
		);
		wp_set_object_terms( $id, get_stylesheet(), 'wp_theme' );

		// Act.
		seed_theme_mods();

		// Assert.
		$this->assertSame( array( 'core/term-description', 'after' ), get_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD ) );
	}

	public function test_keeps_description_anchor_when_notices_are_missing(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		$id = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'archive-product',
				'post_content' => '<!-- wp:query-title {"type":"archive"} /--><!-- wp:term-description /-->',
			)
		);
		wp_set_object_terms( $id, get_stylesheet(), 'wp_theme' );

		// Act.
		$anchor = select_outlet_page_hero_anchor();

		// Assert.
		$this->assertSame( array( 'core/term-description', 'after' ), $anchor );
	}

	public function test_returns_null_when_the_catalog_template_is_missing(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Restore the template filter even if selection fails.
		// Arrange.
		add_filter( 'get_block_template', '__return_null' );

		// Act.
		try {
			$anchor = select_outlet_page_hero_anchor();
		} finally {
			remove_filter( 'get_block_template', '__return_null' );
		}

		// Assert.
		$this->assertNull( $anchor );
	}

	public function test_keeps_default_when_both_title_and_description_are_missing(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		$id = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'archive-product',
				'post_content' => '<!-- wp:woocommerce/store-notices /-->',
			)
		);
		wp_set_object_terms( $id, get_stylesheet(), 'wp_theme' );

		// Act.
		$anchor = select_outlet_page_hero_anchor();

		// Assert.
		$this->assertSame( array( 'core/term-description', 'after' ), $anchor );
	}

	public function test_notices_below_description_do_not_qualify_without_a_title(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		$id = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'archive-product',
				'post_content' => '<!-- wp:term-description /--><!-- wp:woocommerce/store-notices /-->',
			)
		);
		wp_set_object_terms( $id, get_stylesheet(), 'wp_theme' );

		// Act.
		$anchor = select_outlet_page_hero_anchor();

		// Assert.
		$this->assertSame( array( 'core/term-description', 'after' ), $anchor );
	}

	public function test_notices_above_title_do_not_qualify_when_description_is_missing(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		$id = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'archive-product',
				'post_content' => '<!-- wp:woocommerce/store-notices /--><!-- wp:query-title {"type":"archive"} /-->',
			)
		);
		wp_set_object_terms( $id, get_stylesheet(), 'wp_theme' );

		// Act.
		$anchor = select_outlet_page_hero_anchor();

		// Assert.
		$this->assertSame( array( 'core/term-description', 'after' ), $anchor );
	}

	public function test_notices_between_title_and_description_do_not_qualify(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		$id = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'archive-product',
				'post_content' => '<!-- wp:query-title {"type":"archive"} /--><!-- wp:woocommerce/store-notices /--><!-- wp:term-description /-->',
			)
		);
		wp_set_object_terms( $id, get_stylesheet(), 'wp_theme' );

		// Act.
		$anchor = select_outlet_page_hero_anchor();

		// Assert.
		$this->assertSame( array( 'core/term-description', 'after' ), $anchor );
	}

	public function test_seeds_and_inserts_after_notices_for_a_catalog_using_the_legacy_product_query(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		deinit_blocks();
		init_blocks();
		remove_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD );
		$content = '<!-- wp:group {"tagName":"main","layout":{"type":"constrained"}} --><main class="wp-block-group"><!-- wp:woocommerce/breadcrumbs /--><!-- wp:query-title {"type":"archive","showPrefix":false} /--><!-- wp:woocommerce/store-notices /--><!-- wp:group {"layout":{"type":"flex","justifyContent":"space-between"}} --><div class="wp-block-group"><!-- wp:woocommerce/product-results-count /--><!-- wp:woocommerce/catalog-sorting /--></div><!-- /wp:group --><!-- wp:query {"query":{"postType":"product","inherit":true},"namespace":"woocommerce/product-query"} --><div class="wp-block-query"><!-- wp:post-template /--></div><!-- /wp:query --></main><!-- /wp:group -->';
		$id      = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'archive-product',
				'post_content' => $content,
			)
		);
		wp_set_object_terms( $id, get_stylesheet(), 'wp_theme' );

		// Act.
		seed_theme_mods();
		$template = get_block_template( get_stylesheet() . '//archive-product' );
		$main     = parse_blocks( $template->content )[0];

		// Assert.
		$this->assertSame( array( 'woocommerce/store-notices', 'after' ), get_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD ) );
		$this->assertSame( 'woocommerce/store-notices', $main['innerBlocks'][2]['blockName'] );
		$this->assertSame( 'outletpro/outlet-conditional', $main['innerBlocks'][3]['blockName'] );
		$this->assertSame( 'core/group', $main['innerBlocks'][4]['blockName'] );
		$this->assertSame( 'core/query', $main['innerBlocks'][5]['blockName'] );
		$this->assertTrue( $main['innerBlocks'][5]['attrs']['query']['inherit'] );
		$this->assertSame( $content, get_post_field( 'post_content', $id ) );
	}

	public function test_later_catalog_edits_do_not_recompute_the_seeded_anchor(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		remove_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD );
		$id = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'archive-product',
				'post_content' => '<!-- wp:query-title {"type":"archive"} /--><!-- wp:term-description /--><!-- wp:woocommerce/store-notices /-->',
			)
		);
		wp_set_object_terms( $id, get_stylesheet(), 'wp_theme' );
		seed_theme_mods();
		wp_update_post(
			array(
				'ID'           => $id,
				'post_content' => '<!-- wp:query-title {"type":"archive"} /--><!-- wp:woocommerce/store-notices /--><!-- wp:term-description /-->',
			)
		);

		// Act.
		seed_theme_mods();

		// Assert.
		$this->assertSame( array( 'woocommerce/store-notices', 'after' ), get_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD ) );
	}

	public function test_explicit_custom_and_disabled_anchors_survive_the_heuristic(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		$id = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'archive-product',
				'post_content' => '<!-- wp:query-title {"type":"archive"} /--><!-- wp:term-description /--><!-- wp:woocommerce/store-notices /-->',
			)
		);
		wp_set_object_terms( $id, get_stylesheet(), 'wp_theme' );
		set_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD, array( 'core/query-title', 'before' ) );

		// Act.
		seed_theme_mods();
		$custom = get_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD );
		set_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD, array() );
		seed_theme_mods();

		// Assert.
		$this->assertSame( array( 'core/query-title', 'before' ), $custom );
		$this->assertSame( array(), get_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD ) );
	}
}
