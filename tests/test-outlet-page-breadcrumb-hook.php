<?php
/**
 * Tests for outlet_page_breadcrumb_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\init_taxonomies;
use function OutletPro\seed_outlet_status_taxonomy;
use const OutletPro\OUTLET_PAGE_OPTION;

class Test_Outlet_Page_Breadcrumb_Hook extends WP_UnitTestCase {

	// phpcs:ignore Generic.Commenting.DocComment.MissingShort -- PHPUnit data provider metadata.
	/**
	 * @dataProvider permalink_styles
	 */
	public function test_outlet_breadcrumbs_follow_shop_instead_of_the_internal_taxonomy_label( string $permalink_structure, string $product_base ): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Clearance',
				'post_name'  => 'clearance',
			)
		);
		$shop_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Shop',
				'post_name'  => 'shop',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );
		update_option( 'woocommerce_shop_page_id', $shop_id );
		update_option( 'woocommerce_permalinks', array( 'product_base' => $product_base ) );
		update_option( 'show_on_front', 'posts' );
		update_option( 'page_on_front', 0 );
		$this->set_permalink_structure( $permalink_structure );
		$this->go_to( get_permalink( $page_id ) );
		$breadcrumbs = new WC_Breadcrumb();
		$breadcrumbs->add_crumb( 'Home', home_url() );

		// Act.
		$trail = $breadcrumbs->generate();

		// Assert.
		$this->assertSame( array( array( 'Home', home_url() ), array( 'Shop', get_permalink( $shop_id ) ), array( 'Clearance', get_permalink( $page_id ) ) ), $trail );
	}

	// phpcs:ignore Generic.Commenting.DocComment.MissingShort -- PHPUnit data provider metadata.
	/**
	 * @dataProvider permalink_styles
	 */
	public function test_outlet_breadcrumbs_render_when_shop_is_the_homepage( string $permalink_structure, string $product_base ): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Clearance',
				'post_name'  => 'clearance',
			)
		);
		$shop_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Shop',
				'post_name'  => 'shop',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );
		update_option( 'woocommerce_shop_page_id', $shop_id );
		update_option( 'woocommerce_permalinks', array( 'product_base' => $product_base ) );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $shop_id );
		$this->set_permalink_structure( $permalink_structure );
		$this->go_to( get_permalink( $page_id ) );

		// Act.
		$output = do_blocks( '<!-- wp:woocommerce/breadcrumbs /-->' );

		// Assert.
		$this->assertStringContainsString( 'Clearance', $output );
		$this->assertStringNotContainsString( 'Shop', $output );
		$this->assertStringNotContainsString( 'Outlet Status', $output );
	}

	// phpcs:ignore Generic.Commenting.DocComment.MissingShort -- PHPUnit data provider metadata.
	/**
	 * @dataProvider permalink_styles
	 */
	public function test_outlet_breadcrumbs_keep_native_pagination( string $permalink_structure, string $product_base ): void {
		// Arrange.
		init_taxonomies();
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Clearance',
				'post_name'  => 'clearance',
			)
		);
		$shop_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		update_option( 'woocommerce_shop_page_id', $shop_id );
		update_option( 'woocommerce_permalinks', array( 'product_base' => $product_base ) );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $shop_id );
		update_option( 'woocommerce_catalog_rows', 1 );
		update_option( 'woocommerce_catalog_columns', 1 );
		add_to_outlet( WC_Helper_Product::create_simple_product() );
		add_to_outlet( WC_Helper_Product::create_simple_product() );
		$this->set_permalink_structure( $permalink_structure );
		$this->go_to( add_query_arg( 'paged', 2, get_permalink( $page_id ) ) );
		$breadcrumbs = new WC_Breadcrumb();
		$breadcrumbs->add_crumb( 'Home', home_url() );

		// Act.
		$trail = $breadcrumbs->generate();

		// Assert.
		$this->assertSame( array( array( 'Home', home_url() ), array( 'Clearance', get_permalink( $page_id ) ), array( 'Page 2', '' ) ), $trail );
	}

	/**
	 * Provides WooCommerce product permalink styles with plain and pretty Page URLs.
	 *
	 * @return array<string, array{string,string}>
	 */
	public static function permalink_styles(): array {
		return array(
			'default with plain URLs'  => array( '', '' ),
			'default with pretty URLs' => array( '/%postname%/', '' ),
			'shop base'                => array( '/%postname%/', '/shop' ),
			'shop base with category'  => array( '/%postname%/', '/shop/%product_cat%' ),
			'custom base'              => array( '/%postname%/', '/store' ),
		);
	}
}
