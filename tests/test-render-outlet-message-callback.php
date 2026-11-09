<?php
/**
 * Tests for render_outlet_message_callback().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\deinit_blocks;
use function OutletPro\init_blocks;
use function OutletPro\init_taxonomies;
use function OutletPro\register_outlet_message_block;
use function OutletPro\render_outlet_message_callback;
use function OutletPro\seed_outlet_status_taxonomy;
use const OutletPro\OUTLET_MESSAGE_OPTION;
use const OutletPro\OUTLET_PAGE_OPTION;

class Test_Render_Outlet_Message_Callback extends WP_UnitTestCase {

	public function test_wraps_an_emphasized_message_in_strong_inside_the_paragraph(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_MESSAGE_OPTION, 'Final sale.' );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$block = new WP_Block(
			parse_blocks( '<!-- wp:outletpro/outlet-message {"strong":true} /-->' )[0],
			array( 'postId' => $product->get_id() )
		);

		// Act.
		$result = $block->render();

		// Assert.
		$this->assertMatchesRegularExpression( '/^<p[^>]*><strong class="outletpro-strong">Final sale\.<\/strong><\/p>$/', $result );
		$this->assertStringNotContainsString( '<span', $result );
	}

	// phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Version-specific skip guard.
	public function test_replaces_the_span_with_strong_beside_an_icon(): void {
		// Arrange.
		if ( ! function_exists( 'wp_get_icon' ) ) {
			$this->markTestSkipped( 'The WordPress icon API is unavailable.' );
		}

		deinit_blocks();
		init_blocks();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_MESSAGE_OPTION, 'Final sale.' );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$block = new WP_Block(
			parse_blocks( '<!-- wp:outletpro/outlet-message {"strong":true,"showIcon":true} /-->' )[0],
			array( 'postId' => $product->get_id() )
		);

		// Act.
		$result = $block->render();

		// Assert.
		$this->assertMatchesRegularExpression( '/^<p[^>]*><svg\b.*<\/svg>\s*<strong class="outletpro-strong">Final sale\.<\/strong><\/p>$/s', $result );
		$this->assertStringNotContainsString( '<span', $result );
	}

	public function test_emphasizes_only_the_message_beside_a_link(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_MESSAGE_OPTION, 'Final sale.' );
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$block = new WP_Block(
			parse_blocks( '<!-- wp:outletpro/outlet-message {"strong":true} --><!-- wp:outletpro/outlet-link {"text":"Browse deals"} /--><!-- /wp:outletpro/outlet-message -->' )[0],
			array( 'postId' => $product->get_id() )
		);

		// Act.
		$result = $block->render();

		// Assert.
		$this->assertMatchesRegularExpression( '/^<p[^>]*><strong class="outletpro-strong">Final sale\.<\/strong> <a [^>]*>Browse deals<\/a><\/p>$/', $result );
		$this->assertStringNotContainsString( '<span', $result );
	}

	// phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Version-specific skip guard.
	public function test_emphasizes_only_the_message_beside_both_an_icon_and_link(): void {
		// Arrange.
		if ( ! function_exists( 'wp_get_icon' ) ) {
			$this->markTestSkipped( 'The WordPress icon API is unavailable.' );
		}

		deinit_blocks();
		init_blocks();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_MESSAGE_OPTION, 'Final sale.' );
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$block = new WP_Block(
			parse_blocks( '<!-- wp:outletpro/outlet-message {"strong":true,"showIcon":true} --><!-- wp:outletpro/outlet-link {"text":"Browse deals"} /--><!-- /wp:outletpro/outlet-message -->' )[0],
			array( 'postId' => $product->get_id() )
		);

		// Act.
		$result = $block->render();

		// Assert.
		$this->assertMatchesRegularExpression( '/^<p[^>]*><svg\b.*<\/svg>\s*<strong class="outletpro-strong">Final sale\.<\/strong> <a [^>]*>Browse deals<\/a><\/p>$/s', $result );
		$this->assertStringNotContainsString( '<span', $result );
	}

	public function test_renders_an_inserted_link_inline_with_the_message_and_icon(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_MESSAGE_OPTION, 'Final sale.' );
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'Last Chance',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$block = new WP_Block(
			parse_blocks( '<!-- wp:outletpro/outlet-message {"showIcon":true} --><!-- wp:outletpro/outlet-link {"text":"Browse deals","style":{"typography":{"fontWeight":"300"}}} /--><!-- /wp:outletpro/outlet-message -->' )[0],
			array( 'postId' => $product->get_id() )
		);

		// Act.
		$result = $block->render();

		// Assert.
		$this->assertMatchesRegularExpression( '/^<p[^>]*>.*<span>Final sale\.<\/span> <a [^>]*>Browse deals<\/a><\/p>$/s', $result );
		$this->assertStringContainsString( 'outletpro-has-link', $result );
		$this->assertStringContainsString( 'href="' . esc_url( get_permalink( $page_id ) ) . '"', $result );
		$this->assertStringContainsString( 'font-weight:300', $result );
	}

	public function test_omits_an_unavailable_link_without_leaving_extra_whitespace(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		delete_option( OUTLET_PAGE_OPTION );
		update_option( OUTLET_MESSAGE_OPTION, 'Final sale.' );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$block = new WP_Block(
			parse_blocks( '<!-- wp:outletpro/outlet-message --><!-- wp:outletpro/outlet-link /--><!-- /wp:outletpro/outlet-message -->' )[0],
			array( 'postId' => $product->get_id() )
		);

		// Act.
		$result = $block->render();

		// Assert.
		$this->assertMatchesRegularExpression( '/^<p[^>]*>Final sale\.<\/p>$/', $result );
	}

	public function test_returns_empty_string_when_product_not_in_outlet(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_message_block();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		$block   = new WP_Block(
			array(
				'blockName'    => 'outletpro/outlet-message',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			),
			array( 'postId' => $product->get_id() )
		);

		// Act.
		$result = render_outlet_message_callback( array(), '', $block );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_returns_message_html_when_product_is_in_outlet(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_message_block();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_MESSAGE_OPTION, 'Not eligible for change of mind returns' );
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$block = new WP_Block(
			array(
				'blockName'    => 'outletpro/outlet-message',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			),
			array( 'postId' => $product->get_id() )
		);

		// Act.
		$result = $block->render();

		// Assert.
		$this->assertStringContainsString( 'outletpro-message', $result );
		$this->assertStringContainsString( 'Not eligible for change of mind returns', $result );
		$this->assertStringNotContainsString( '<span', $result );
		$processor = new WP_HTML_Tag_Processor( $result );
		$this->assertTrue( $processor->next_tag( 'p' ) );
		$this->assertFalse( $processor->has_class( 'outletpro-has-icon' ) );
		$this->assertFalse( $processor->next_tag( 'svg' ) );
		$this->assertStringNotContainsString( 'wp-block-outletpro-outlet-message__icon', $result );

		// Cleanup.
		delete_option( OUTLET_MESSAGE_OPTION );
	}

	// phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Version-specific skip guard.
	public function test_enabling_the_icon_preserves_the_global_message(): void {
		// Arrange.
		if ( ! function_exists( 'wp_get_icon' ) ) {
			$this->markTestSkipped( 'The WordPress icon API is unavailable.' );
		}

		deinit_blocks();
		register_outlet_message_block();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_MESSAGE_OPTION, 'Final sale — no returns.' );
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$block = new WP_Block(
			array(
				'blockName'    => 'outletpro/outlet-message',
				'attrs'        => array(
					'showIcon'  => true,
					'className' => 'is-style-bold',
				),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			),
			array( 'postId' => $product->get_id() )
		);

		// Act.
		$result = $block->render();

		// Assert.
		$this->assertStringContainsString( 'Final sale — no returns.', $result );
		$this->assertStringNotContainsString( 'Not eligible for change of mind returns', $result );
		$this->assertStringContainsString( wp_get_icon( 'core/info' ), $result );
		$this->assertStringContainsString( 'outletpro-has-icon', $result );
		$this->assertStringContainsString( '<span>Final sale — no returns.</span>', $result );
		$this->assertMatchesRegularExpression( '/<p\b[^>]*><svg\b[\s\S]*<\/svg>\s*<span>Final sale — no returns\.<\/span><\/p>/', $result );

		// Cleanup.
		delete_option( OUTLET_MESSAGE_OPTION );
	}

	// phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Version-specific skip guard.
	public function test_returns_plain_message_when_the_icon_api_is_unavailable(): void {
		// Arrange.
		if ( function_exists( 'wp_get_icon' ) ) {
			$this->markTestSkipped( 'The WordPress icon API is available.' );
		}

		deinit_blocks();
		register_outlet_message_block();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_MESSAGE_OPTION, 'Final sale — no returns.' );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$block = new WP_Block(
			parse_blocks( '<!-- wp:outletpro/outlet-message {"showIcon":true} /-->' )[0],
			array( 'postId' => $product->get_id() )
		);

		// Act.
		$result = $block->render();

		// Assert.
		$this->assertStringContainsString( 'Final sale — no returns.', $result );
		$this->assertStringNotContainsString( '<svg', $result );
		$this->assertStringNotContainsString( '<span', $result );
		$this->assertStringNotContainsString( 'outletpro-has-icon', $result );
	}

	public function test_returns_empty_string_when_post_id_is_zero(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_message_block();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		$block = new WP_Block(
			array(
				'blockName'    => 'outletpro/outlet-message',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			),
			array()
		);

		// Act.
		$result = render_outlet_message_callback( array(), '', $block );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_message_is_registered_after_init_blocks(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		deinit_blocks();

		// Act.
		init_blocks();

		// Assert.
		$this->assertTrue( \WP_Block_Type_Registry::get_instance()->is_registered( 'outletpro/outlet-message' ) );
	}

	public function test_empty_option_returns_empty_string(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_message_block();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_MESSAGE_OPTION, '' );
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$block = new WP_Block(
			array(
				'blockName'    => 'outletpro/outlet-message',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			),
			array( 'postId' => $product->get_id() )
		);

		// Act.
		$result = $block->render();

		// Assert.
		$this->assertSame( '', $result );

		// Cleanup.
		delete_option( OUTLET_MESSAGE_OPTION );
	}

	public function test_missing_option_returns_empty_string(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_message_block();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		delete_option( OUTLET_MESSAGE_OPTION );
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$block = new WP_Block(
			array(
				'blockName'    => 'outletpro/outlet-message',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			),
			array( 'postId' => $product->get_id() )
		);

		// Act.
		$result = $block->render();

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_message_is_wrapped_in_paragraph_tag(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_message_block();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_MESSAGE_OPTION, 'Not eligible for change of mind returns' );
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$block = new WP_Block(
			array(
				'blockName'    => 'outletpro/outlet-message',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			),
			array( 'postId' => $product->get_id() )
		);

		// Act.
		$result = $block->render();

		// Assert.
		$this->assertMatchesRegularExpression( '/<p\b[^>]*>.+<\/p>/', $result );

		// Cleanup.
		delete_option( OUTLET_MESSAGE_OPTION );
	}
}
