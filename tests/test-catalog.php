<?php
/**
 * Outlet catalog integration tests.
 *
 * @package OutletPro
 */

use function OutletPro\add_to_outlet;
use function OutletPro\compose_outlet_catalog_content;
use function OutletPro\create_outlet_catalog_template;
use function OutletPro\deinit_patterns;
use function OutletPro\init_catalog;
use function OutletPro\init_patterns;
use function OutletPro\init_taxonomies;
use function OutletPro\render_outlet_page_content;
use const OutletPro\OUTLET_PAGE_OPTION;

class Test_Catalog extends WP_UnitTestCase {

	public function test_password_protected_outlet_uses_the_normal_page_template(): void {
		// Arrange.
		init_catalog();
		switch_theme( 'twentytwentyfive' );
		add_theme_support( 'block-templates' );
		$page_id = self::factory()->post->create(
			array(
				'post_type'     => 'page',
				'post_password' => 'secret',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$template_id = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'alternate-outlet',
				'post_content' => '<!-- wp:paragraph --><p>Protected layout</p><!-- /wp:paragraph -->',
			)
		);
		wp_set_object_terms( $template_id, get_stylesheet(), 'wp_theme' );
		update_post_meta( $page_id, '_wp_page_template', 'alternate-outlet' );
		$this->go_to( get_permalink( $page_id ) );
		$GLOBALS['post'] = get_post( $page_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Arrange the native Page template context.

		// Act.
		get_page_template();

		// Assert.
		$this->assertSame( get_stylesheet() . '//page', $GLOBALS['_wp_current_template_id'] );
		$this->assertStringContainsString( 'wp:post-content', $GLOBALS['_wp_current_template_content'] );
	}

	public function test_outlet_uses_a_template_chosen_through_the_native_page_api(): void {
		// Arrange.
		init_catalog();
		switch_theme( 'twentytwentyfive' );
		add_theme_support( 'block-templates' );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$template_id = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'alternate-outlet',
				'post_content' => '<!-- wp:paragraph --><p>Chosen layout</p><!-- /wp:paragraph -->',
			)
		);
		wp_set_object_terms( $template_id, get_stylesheet(), 'wp_theme' );
		$request = new WP_REST_Request( 'PUT', '/wp/v2/pages/' . $page_id );
		$request->set_body_params( array( 'template' => 'alternate-outlet' ) );

		// Act.
		$response = rest_do_request( $request );
		$this->go_to( get_permalink( $page_id ) );
		get_archive_template();
		$settings = get_block_editor_settings( array( 'supportsTemplateMode' => true ), new WP_Block_Editor_Context( array( 'post' => get_post( $page_id ) ) ) );

		// Assert.
		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( get_stylesheet() . '//alternate-outlet', $GLOBALS['_wp_current_template_id'] );
		$this->assertStringContainsString( 'Chosen layout', $GLOBALS['_wp_current_template_content'] );
		$this->assertTrue( $settings['supportsTemplateMode'] );
	}


	public function test_outlet_uses_product_archive_and_preserves_its_page_identity(): void {
		// Arrange.
		init_taxonomies();
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Clearance',
			)
		);
		$shop_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		update_option( 'woocommerce_shop_page_id', $shop_id );
		$outlet  = WC_Helper_Product::create_simple_product();
		$regular = WC_Helper_Product::create_simple_product();
		add_to_outlet( $outlet );

		// Act.
		$this->go_to( get_permalink( $page_id ) );

		// Assert.
		$this->assertTrue( is_post_type_archive( 'product' ) );
		$this->assertSame( $page_id, get_queried_object_id() );
		$this->assertSame( array( $outlet->get_id() ), wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' ) );
		$this->assertSame( $shop_id, wc_get_page_id( 'shop' ) );
		$this->assertSame( 'Clearance', woocommerce_page_title( false ) );
	}

	public function test_outlet_breadcrumbs_render_when_shop_is_the_homepage(): void {
		// Arrange.
		init_catalog();
		$shop_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Shop',
			)
		);
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Clearance',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );
		update_option( 'woocommerce_shop_page_id', $shop_id );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $shop_id );
		$this->go_to( get_permalink( $page_id ) );

		// Expect.
		$this->expectOutputString( '<nav class="woocommerce-breadcrumb" aria-label="Breadcrumb"><a href="' . esc_url( home_url() ) . '">Home</a>&nbsp;&#47;&nbsp;Clearance</nav>' );

		// Act.
		woocommerce_breadcrumb();
	}

	public function test_outlet_breadcrumbs_use_the_page_hierarchy_instead_of_shop(): void {
		// Arrange.
		init_catalog();
		$shop_id   = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Shop',
			)
		);
		$parent_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Offers',
			)
		);
		$page_id   = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_title'  => 'Clearance',
				'post_parent' => $parent_id,
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );
		update_option( 'woocommerce_shop_page_id', $shop_id );
		update_option( 'page_on_front', 0 );
		$this->go_to( get_permalink( $page_id ) );
		$breadcrumbs = new WC_Breadcrumb();
		$breadcrumbs->add_crumb( 'Start', home_url() );

		// Act.
		$trail = $breadcrumbs->generate();

		// Assert.
		$this->assertSame( array( array( 'Start', home_url() ), array( 'Offers', get_permalink( $parent_id ) ), array( 'Clearance', get_permalink( $page_id ) ) ), $trail );
	}

	public function test_outlet_breadcrumbs_preserve_native_pagination(): void {
		// Arrange.
		init_catalog();
		init_taxonomies();
		$shop_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Clearance',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );
		update_option( 'woocommerce_shop_page_id', $shop_id );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $shop_id );
		update_option( 'posts_per_page', 1 );
		update_option( 'woocommerce_catalog_rows', 1 );
		update_option( 'woocommerce_catalog_columns', 1 );
		add_to_outlet( WC_Helper_Product::create_simple_product() );
		add_to_outlet( WC_Helper_Product::create_simple_product() );
		$this->go_to( add_query_arg( 'paged', 2, get_permalink( $page_id ) ) );
		$breadcrumbs = new WC_Breadcrumb();
		$breadcrumbs->add_crumb( 'Home', home_url() );

		// Act.
		$trail = $breadcrumbs->generate();

		// Assert.
		$this->assertSame( array( array( 'Home', home_url() ), array( 'Clearance', get_permalink( $page_id ) ), array( 'Page 2', '' ) ), $trail );
	}

	public function test_shop_homepage_does_not_gain_outlet_breadcrumbs(): void {
		// Arrange.
		init_catalog();
		$shop_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Shop',
			)
		);
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Clearance',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );
		update_option( 'woocommerce_shop_page_id', $shop_id );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $shop_id );
		$this->go_to( home_url( '/' ) );

		// Expect.
		$this->expectOutputString( '' );

		// Act.
		woocommerce_breadcrumb();
	}

	public function test_draft_outlet_does_not_expose_products_to_visitors(): void {
		// Arrange.
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'draft',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );
		wp_set_current_user( 0 );

		// Act.
		$this->go_to( add_query_arg( 'page_id', $page_id, home_url( '/' ) ) );

		// Assert.
		$this->assertFalse( is_post_type_archive( 'product' ) );
		$this->assertEmpty( get_query_var( 'outletpro_catalog' ) );
	}

	public function test_password_protected_outlet_keeps_wordpress_password_protection(): void {
		// Arrange.
		$page_id = self::factory()->post->create(
			array(
				'post_type'     => 'page',
				'post_password' => 'secret',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );

		// Act.
		$this->go_to( get_permalink( $page_id ) );

		// Assert.
		$this->assertTrue( post_password_required( $page_id ) );
		$this->assertEmpty( get_query_var( 'outletpro_catalog' ) );
	}

	public function test_ordinary_page_keeps_its_page_query(): void {
		// Arrange.
		$outlet_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$page_id   = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $outlet_id );

		// Act.
		$this->go_to( get_permalink( $page_id ) );

		// Assert.
		$this->assertTrue( is_page( $page_id ) );
		$this->assertEmpty( get_query_var( 'outletpro_catalog' ) );
	}

	public function test_category_navigation_intersects_with_outlet_products(): void {
		// Arrange.
		init_taxonomies();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$category = self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'slug'     => 'boots',
			)
		);
		$match    = WC_Helper_Product::create_simple_product();
		$other    = WC_Helper_Product::create_simple_product();
		$regular  = WC_Helper_Product::create_simple_product();
		add_to_outlet( $match );
		add_to_outlet( $other );
		wp_set_object_terms( $match->get_id(), $category, 'product_cat' );
		wp_set_object_terms( $regular->get_id(), $category, 'product_cat' );

		// Act.
		$this->go_to( add_query_arg( 'outlet_category', 'boots', get_permalink( $page_id ) ) );

		// Assert.
		$this->assertSame( array( $match->get_id() ), wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' ) );
	}

	public function test_pretty_pagination_keeps_outlet_scope(): void {
		// Arrange.
		init_taxonomies();
		$page_id = self::factory()->post->create(
			array(
				'post_type' => 'page',
				'post_name' => 'clearance',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );
		update_option( 'posts_per_page', 1 );
		update_option( 'woocommerce_catalog_rows', 1 );
		update_option( 'woocommerce_catalog_columns', 1 );
		$first  = WC_Helper_Product::create_simple_product();
		$second = WC_Helper_Product::create_simple_product();
		add_to_outlet( $first );
		add_to_outlet( $second );
		$this->set_permalink_structure( '/%postname%/' );
		init_catalog();
		flush_rewrite_rules( false );

		// Act.
		$this->go_to( trailingslashit( get_permalink( $page_id ) ) . 'page/2/' );

		// Assert.
		$this->assertSame( $page_id, get_queried_object_id() );
		$this->assertSame( 2, get_query_var( 'paged' ) );
		$this->assertSame( 2, $GLOBALS['wp_query']->found_posts );
	}

	public function test_intro_and_navigation_are_after_the_title_and_before_filters(): void {
		// Arrange.
		$template = '<!-- wp:group {"tagName":"main"} --><main><!-- wp:query-title {"type":"archive"} /--><!-- wp:columns --><div class="wp-block-columns"><!-- wp:column --><div class="wp-block-column"><!-- wp:paragraph --><p>Sidebar</p><!-- /wp:paragraph --></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:woocommerce/product-collection {"query":{"inherit":true}} /--></div><!-- /wp:column --></div><!-- /wp:columns --></main><!-- /wp:group -->';

		// Act.
		$content = compose_outlet_catalog_content( $template, '<!-- wp:paragraph --><p>Template navigation</p><!-- /wp:paragraph -->' );

		// Assert.
		$this->assertLessThan( strpos( $content, 'While supplies last' ), strpos( $content, 'wp:query-title' ) );
		$this->assertLessThan( strpos( $content, 'Template navigation' ), strpos( $content, 'While supplies last' ) );
		$this->assertLessThan( strpos( $content, 'wp:columns' ), strpos( $content, 'Template navigation' ) );
		$this->assertStringNotContainsString( 'wp:post-content', $content );
		$this->assertStringContainsString( '"inherit":true', $content );
	}

	public function test_template_without_heading_places_navigation_at_start_of_main(): void {
		// Arrange.
		$template = '<!-- wp:group {"tagName":"main"} --><main><!-- wp:paragraph --><p>Catalog</p><!-- /wp:paragraph --></main><!-- /wp:group -->';

		// Act.
		$content = compose_outlet_catalog_content( $template, '<!-- wp:paragraph --><p>Template navigation</p><!-- /wp:paragraph -->' );

		// Assert.
		$this->assertStringContainsString( '<main><!-- wp:group', $content );
		$this->assertStringContainsString( 'Template navigation', $content );
	}

	public function test_copy_preserves_inherited_and_independent_product_queries(): void {
		// Arrange.
		$template = '<!-- wp:woocommerce/product-collection {"query":{"inherit":true}} /--><!-- wp:woocommerce/product-collection {"query":{"inherit":false,"featured":true}} /-->';

		// Act.
		$content = compose_outlet_catalog_content( $template, '<!-- wp:paragraph --><p>Template navigation</p><!-- /wp:paragraph -->' );

		// Assert.
		$this->assertStringContainsString( '"inherit":true', $content );
		$this->assertStringNotContainsString( '"outletpro":true', $content );
		$this->assertStringContainsString( '"featured":true', $content );
	}

	public function test_navigation_renders_page_content_and_restores_product_context(): void {
		// Arrange.
		$page_id         = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_content' => '<!-- wp:paragraph --><p>Outlet navigation</p><!-- /wp:paragraph -->',
			)
		);
		$product         = WC_Helper_Product::create_simple_product();
		$GLOBALS['post'] = get_post( $product->get_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Arrange the product-loop context.
		setup_postdata( $GLOBALS['post'] );

		// Act.
		$content = render_outlet_page_content( $page_id );

		// Assert.
		$this->assertStringContainsString( 'Outlet navigation</p>', $content );
		$this->assertSame( $product->get_id(), get_the_ID() );
	}

	public function test_wordpress_selects_the_saved_template_from_the_archive_hierarchy(): void {
		// Arrange.
		init_catalog();
		switch_theme( 'twentytwentyfive' );
		deinit_patterns();
		init_patterns();
		add_theme_support( 'block-templates' );
		$this->assertTrue( wp_is_block_theme(), get_theme_root() );
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$catalog_id = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'archive-product',
				'post_content' => '<!-- wp:query-title {"type":"archive"} /-->',
			)
		);
		wp_set_object_terms( $catalog_id, get_stylesheet(), 'wp_theme' );
		create_outlet_catalog_template( '<!-- wp:paragraph --><p>Template navigation</p><!-- /wp:paragraph -->' );
		update_post_meta( $page_id, '_wp_page_template', 'outlet-catalog' );
		$this->go_to( get_permalink( $page_id ) );

		// Act.
		$template = get_archive_template();

		// Assert.
		$this->assertSame( ABSPATH . WPINC . '/template-canvas.php', $template );
		$this->assertSame( get_stylesheet() . '//outlet-catalog', $GLOBALS['_wp_current_template_id'] );
		$this->assertStringContainsString( 'outletpro-navigation', $GLOBALS['_wp_current_template_content'] );
	}

	public function test_saved_outlet_layout_stays_independent_of_later_shop_edits(): void {
		// Arrange.
		init_catalog();
		switch_theme( 'twentytwentyfive' );
		deinit_patterns();
		init_patterns();
		$this->assertTrue( wp_is_block_theme(), get_theme_root() );
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$template_id = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'archive-product',
				'post_content' => '<!-- wp:paragraph --><p>Shared layout first</p><!-- /wp:paragraph -->',
				'tax_input'    => array( 'wp_theme' => array( get_stylesheet() ) ),
			)
		);
		wp_set_object_terms( $template_id, get_stylesheet(), 'wp_theme' );
		create_outlet_catalog_template( '<!-- wp:paragraph --><p>Template navigation</p><!-- /wp:paragraph -->' );
		$first = get_block_template( get_stylesheet() . '//outlet-catalog' );
		wp_update_post(
			array(
				'ID'           => $template_id,
				'post_content' => '<!-- wp:paragraph --><p>Shared layout changed</p><!-- /wp:paragraph -->',
			)
		);

		// Act.
		$second = get_block_template( get_stylesheet() . '//outlet-catalog' );

		// Assert.
		$this->assertStringContainsString( 'Shared layout first', $first->content );
		$this->assertStringContainsString( 'Shared layout first', $second->content );
		$this->assertStringNotContainsString( 'Shared layout changed', $second->content );
		$this->assertGreaterThan( 0, $second->wp_id );
		$this->assertCount(
			1,
			get_posts(
				array(
					'post_type' => 'wp_template',
					'name'      => 'outlet-catalog',
				)
			)
		);
		$this->assertSame( '', get_post_meta( $page_id, '_outletpro_catalog_layout', true ) );
	}

	public function test_native_price_range_remains_outlet_scoped(): void {
		// Arrange.
		init_catalog();
		init_taxonomies();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$cheap = WC_Helper_Product::create_simple_product();
		$cheap->set_regular_price( '10' );
		$cheap->save();
		$expensive = WC_Helper_Product::create_simple_product();
		$expensive->set_regular_price( '40' );
		$expensive->save();
		$regular = WC_Helper_Product::create_simple_product();
		$regular->set_regular_price( '9999' );
		$regular->save();
		add_to_outlet( $cheap );
		add_to_outlet( $expensive );
		$this->go_to( get_permalink( $page_id ) );

		// Act.
		$html = do_blocks( '<!-- wp:woocommerce/product-filters --><div class="wp-block-woocommerce-product-filters"><!-- wp:woocommerce/product-filter-price /--></div><!-- /wp:woocommerce/product-filters -->' );
		$tags = new WP_HTML_Tag_Processor( $html );
		$tags->next_tag( array( 'class_name' => 'wp-block-woocommerce-product-filter-price' ) );
		$context = json_decode( $tags->get_attribute( 'data-wp-context' ), true );

		// Assert.
		$this->assertSame( 10, $context['minRange'] );
		$this->assertSame( 40, $context['maxRange'] );
	}

	public function test_navigation_context_is_restored_when_content_rendering_throws(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Finally assertions verify restoration while PHPUnit handles the expected exception.
		// Arrange.
		$page_id          = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$previous_post    = $GLOBALS['post'] ?? null;
		$GLOBALS['pages'] = array( 'Original page content' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Arrange an existing multipage context.
		$failure          = static function (): void {
			throw new RuntimeException( 'Rendering failed.' );
		};
		add_filter( 'the_content', $failure );

		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Rendering failed.' );

		// Act.
		try {
			render_outlet_page_content( $page_id );
		} finally {
			remove_filter( 'the_content', $failure );
			$this->assertSame( $previous_post, $GLOBALS['post'] ?? null );
			$this->assertSame( array( 'Original page content' ), $GLOBALS['pages'] );
		}
	}


	public function test_shop_and_category_archives_keep_regular_products(): void {
		// Arrange.
		init_catalog();
		init_taxonomies();
		$outlet_page = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$shop        = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $outlet_page );
		update_option( 'woocommerce_shop_page_id', $shop );
		$product  = WC_Helper_Product::create_simple_product();
		$category = self::factory()->term->create( array( 'taxonomy' => 'product_cat' ) );
		wp_set_object_terms( $product->get_id(), $category, 'product_cat' );

		// Act.
		$this->go_to( add_query_arg( 'post_type', 'product', home_url( '/' ) ) );
		$shop_products = wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' );
		$this->go_to( get_term_link( $category, 'product_cat' ) );

		// Assert.
		$this->assertContains( $product->get_id(), $shop_products );
		$this->assertContains( $product->get_id(), wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' ) );
		$this->assertEmpty( get_query_var( 'outletpro_catalog' ) );
	}

	public function test_pagination_routes_follow_page_slug_changes(): void {
		// Arrange.
		init_catalog();
		init_taxonomies();
		$page_id = self::factory()->post->create(
			array(
				'post_type' => 'page',
				'post_name' => 'first-outlet',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$this->set_permalink_structure( '/%postname%/' );
		init_catalog();
		flush_rewrite_rules( false );
		wp_update_post(
			array(
				'ID'        => $page_id,
				'post_name' => 'renamed-outlet',
			)
		);
		init_catalog();
		flush_rewrite_rules( false );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );

		// Act.
		$this->go_to( trailingslashit( get_permalink( $page_id ) ) . 'page/1/' );

		// Assert.
		$this->assertSame( $page_id, get_query_var( 'outletpro_catalog' ) );
		$this->assertSame( array( $product->get_id() ), wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' ) );
	}

	public function test_classic_price_widget_keeps_outlet_navigation_and_sorting(): void {
		// Arrange.
		init_catalog();
		init_taxonomies();
		switch_theme( 'storefront' );
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$category = self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'slug'     => 'widget-boots',
			)
		);
		$cheap    = WC_Helper_Product::create_simple_product();
		$cheap->set_regular_price( '10' );
		$cheap->save();
		$expensive = WC_Helper_Product::create_simple_product();
		$expensive->set_regular_price( '40' );
		$expensive->save();
		add_to_outlet( $cheap );
		add_to_outlet( $expensive );
		wp_set_object_terms( $cheap->get_id(), $category, 'product_cat' );
		wp_set_object_terms( $expensive->get_id(), $category, 'product_cat' );
		$this->go_to(
			add_query_arg(
				array(
					'outlet_category' => 'widget-boots',
					'orderby'         => 'price',
				),
				get_permalink( $page_id )
			)
		);

		// Expect.
		$this->expectOutputRegex( '/<form[^>]+action="' . preg_quote( get_permalink( $page_id ), '/' ) . '".*name="outlet_category" value="widget-boots".*name="orderby" value="price"/s' );

		// Act.
		the_widget( WC_Widget_Price_Filter::class );
	}

	public function test_saved_outlet_template_can_be_edited_in_the_site_editor(): void {
		// Arrange.
		init_catalog();
		switch_theme( 'twentytwentyfive' );
		deinit_patterns();
		init_patterns();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$source = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'archive-product',
				'post_content' => '<!-- wp:query-title {"type":"archive"} /-->',
			)
		);
		wp_set_object_terms( $source, get_stylesheet(), 'wp_theme' );
		create_outlet_catalog_template( '<!-- wp:paragraph --><p>Template navigation</p><!-- /wp:paragraph -->' );
		$request = new WP_REST_Request( 'PUT', '/wp/v2/templates/' . get_stylesheet() . '//outlet-catalog' );
		$request->set_body_params( array( 'content' => '<!-- wp:paragraph --><p>Independent layout</p><!-- /wp:paragraph -->' ) );

		// Act.
		$response = rest_do_request( $request );
		create_outlet_catalog_template( '<!-- wp:paragraph --><p>Template navigation</p><!-- /wp:paragraph -->' );

		// Assert.
		$this->assertSame( 200, $response->get_status() );
		$this->assertStringContainsString( 'Independent layout', get_block_template( get_stylesheet() . '//outlet-catalog' )->content );
	}
}
