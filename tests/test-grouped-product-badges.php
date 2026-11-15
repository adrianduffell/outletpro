<?php
/**
 * Tests for grouped product badges in classic and block product forms.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use Automattic\WooCommerce\Blocks\Assets\Api as AssetApi;
use Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry;
use Automattic\WooCommerce\Blocks\BlockTypes\AddToCartWithOptions\GroupedProductItem;
use Automattic\WooCommerce\Blocks\BlockTypes\AddToCartWithOptions\GroupedProductItemLabel;
use Automattic\WooCommerce\Blocks\Integrations\IntegrationRegistry;
use Automattic\WooCommerce\Blocks\Package;
use function OutletPro\add_to_outlet;
use function OutletPro\deinit_blocks;
use function OutletPro\init_blocks;
use function OutletPro\init_taxonomies;
use function OutletPro\init_woocommerce_template_hooks;
use function OutletPro\seed_outlet_status_taxonomy;
use const OutletPro\OUTLET_BADGE_LABEL_OPTION;
use const OutletPro\OUTLET_STATUS_TAXONOMY;

class Test_Grouped_Product_Badges extends WP_UnitTestCase {

	public function test_classic_form_badges_only_outlet_children(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		init_woocommerce_template_hooks();
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Clearance' );
		$parent   = WC_Helper_Product::create_grouped_product();
		$children = array_map( 'wc_get_product', $parent->get_children() );
		add_to_outlet( $children[0] );
		$GLOBALS['post']    = get_post( $parent->get_id() );
		$GLOBALS['product'] = $parent;
		wp_register_style( 'outletpro-classic-badge', false, array(), 'test' );
		wp_dequeue_style( 'outletpro-classic-badge' );

		// Act.
		$result = wc_get_template_html( 'single-product/add-to-cart/grouped.php', array( 'grouped_products' => $children ) );

		// Assert.
		$this->assertSame( 1, substr_count( $result, 'class="outletpro-badge"' ) );
		$this->assertStringContainsString( '</label><span class="outletpro-badge">Clearance</span>', $result );
		$this->assertMatchesRegularExpression( '/<tr id="product-' . $children[0]->get_id() . '"(?:(?!<\/tr>).)*outletpro-badge/s', $result );
		$this->assertDoesNotMatchRegularExpression( '/<tr id="product-' . $children[1]->get_id() . '"(?:(?!<\/tr>).)*outletpro-badge/s', $result );
		$this->assertTrue( wp_style_is( 'outletpro-classic-badge', 'enqueued' ) );
	}

	public function test_block_form_badges_only_outlet_children(): void {
		// Arrange.
		new GroupedProductItem( Package::container()->get( AssetApi::class ), Package::container()->get( AssetDataRegistry::class ), new IntegrationRegistry() );
		new GroupedProductItemLabel( Package::container()->get( AssetApi::class ), Package::container()->get( AssetDataRegistry::class ), new IntegrationRegistry() );
		deinit_blocks();
		init_blocks();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Clearance' );
		$parent   = WC_Helper_Product::create_grouped_product();
		$children = array_map( 'wc_get_product', $parent->get_children() );
		add_to_outlet( $parent );
		add_to_outlet( $children[0] );
		$children[0]->set_name( 'Outlet child' );
		$children[0]->save();
		$children[1]->set_name( 'Regular child' );
		$children[1]->save();
		$GLOBALS['post']    = get_post( $parent->get_id() );
		$GLOBALS['product'] = $parent;
		$template           = new WP_Block_Template();
		$template->slug     = 'grouped-product-add-to-cart-with-options';
		$content            = '<!-- wp:woocommerce/add-to-cart-with-options-grouped-product-item --><div><!-- wp:woocommerce/add-to-cart-with-options-grouped-product-item-label /--></div><!-- /wp:woocommerce/add-to-cart-with-options-grouped-product-item -->';

		// Act.
		$result = do_blocks( apply_block_hooks_to_content( $content, $template ) );

		// Assert.
		$this->assertSame( 1, substr_count( $result, 'class="outletpro-badge"' ) );
		$this->assertStringContainsString( 'wp-block-outletpro-outlet-badge', $result );
		$this->assertMatchesRegularExpression( '/<label[^>]*>Outlet child<\/label><div[^>]*><div class="outletpro-badge">Clearance<\/div><\/div>/', $result );
		$this->assertStringContainsString( 'Regular child</label>', $result );
	}

	public function test_non_outlet_child_label_is_unchanged(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		init_woocommerce_template_hooks();
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Clearance' );
		$product = WC_Helper_Product::create_simple_product();
		$label   = '<label><a href="/product/child">Child</a></label>';
		wp_register_style( 'outletpro-classic-badge', false, array(), 'test' );
		wp_dequeue_style( 'outletpro-classic-badge' );

		// Act.
		$result = apply_filters( 'woocommerce_grouped_product_list_column_label', $label, $product );

		// Assert.
		$this->assertSame( $label, $result );
		$this->assertFalse( wp_style_is( 'outletpro-classic-badge', 'enqueued' ) );
	}

	public function test_child_badge_escapes_the_configured_label(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		init_woocommerce_template_hooks();
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Clearance & more' );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );

		// Act.
		$result = apply_filters( 'woocommerce_grouped_product_list_column_label', '<label>Child</label>', $product );

		// Assert.
		$this->assertSame( '<label>Child</label><span class="outletpro-badge">Clearance &amp; more</span>', $result );
	}

	public function test_child_badge_is_hidden_when_the_label_is_empty(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		init_woocommerce_template_hooks();
		update_option( OUTLET_BADGE_LABEL_OPTION, '' );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );

		// Act.
		$result = apply_filters( 'woocommerce_grouped_product_list_column_label', '<label>Child</label>', $product );

		// Assert.
		$this->assertSame( '<label>Child</label>', $result );
	}

	public function test_child_badge_is_hidden_when_the_label_is_invalid(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		init_woocommerce_template_hooks();
		update_option( OUTLET_BADGE_LABEL_OPTION, array( 'invalid' ) );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );

		// Act.
		$result = apply_filters( 'woocommerce_grouped_product_list_column_label', '<label>Child</label>', $product );

		// Assert.
		$this->assertSame( '<label>Child</label>', $result );
	}

	public function test_child_badge_is_hidden_when_the_taxonomy_is_missing(): void {
		// Arrange.
		init_woocommerce_template_hooks();
		unregister_taxonomy( OUTLET_STATUS_TAXONOMY );
		$product = WC_Helper_Product::create_simple_product();

		// Act.
		$result = apply_filters( 'woocommerce_grouped_product_list_column_label', '<label>Child</label>', $product );

		// Assert.
		$this->assertSame( '<label>Child</label>', $result );
	}
}
