<?php
/**
 * Tests for add_core_button_active_class_hook().
 *
 * @package WC_Outlet
 */

use function WC_Outlet\deinit_blocks;
use function WC_Outlet\deinit_enqueue;
use function WC_Outlet\enqueue_init;
use function WC_Outlet\init_blocks;

class Test_Add_Core_Button_Active_Class_Hook extends WP_UnitTestCase {

	public function test_adds_is_active_class_and_enqueues_style_when_href_matches_current_url(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		deinit_enqueue();
		enqueue_init();
		do_action( 'wp_enqueue_scripts' );
		$_SERVER['REQUEST_URI'] = '/outlet?max_price=50';
		$current_url            = home_url( '/outlet?max_price=50' );
		$block_content          = '<div class="wc-outlet-filter-tiles"><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="' . esc_url( $current_url ) . '">Shop</a></div>';
		$block                  = array(
			'blockName' => 'core/buttons',
			'attrs'     => array(
				'className' => 'wc-outlet-filter-tiles',
			),
		);

		// Act.
		$result = apply_filters( 'render_block', $block_content, $block, new WP_Block( $block ) );

		// Assert.
		$this->assertStringContainsString( 'is-active', $result );
		$this->assertTrue( wp_style_is( 'wc-outlet-core-button-active', 'enqueued' ) );
	}

	public function test_does_not_add_is_active_class_when_href_does_not_match_current_url(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		deinit_enqueue();
		enqueue_init();
		do_action( 'wp_enqueue_scripts' );
		$block_content = '<div class="wc-outlet-filter-tiles"><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="https://example.com/other">Shop</a></div></div>';
		$block         = array(
			'blockName' => 'core/button',
			'attrs'     => array(
				'href'      => 'https://example.com/other',
				'className' => 'foo',
			),
		);

		// Act.
		$result = apply_filters( 'render_block', $block_content, $block, new WP_Block( $block ) );

		// Assert.
		$this->assertSame( $block_content, $result );
		$this->assertFalse( wp_style_is( 'wc-outlet-core-button-active', 'enqueued' ) );
	}

	public function test_does_not_add_is_active_class_when_class_name_is_missing(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		deinit_enqueue();
		enqueue_init();
		do_action( 'wp_enqueue_scripts' );
		$_SERVER['REQUEST_URI'] = '/outlet?max_price=50';
		$current_url            = home_url( '/outlet?max_price=50' );
		$block_content          = '<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="' . esc_url( $current_url ) . '">Shop</a></div>';
		$block                  = array(
			'blockName' => 'core/button',
			'attrs'     => array(
				'href' => $current_url,
			),
		);

		// Act.
		$result = apply_filters( 'render_block', $block_content, $block, new WP_Block( $block ) );

		// Assert.
		$this->assertSame( $block_content, $result );
		$this->assertFalse( wp_style_is( 'wc-outlet-core-button-active', 'enqueued' ) );
	}
}
