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
		$current_url   = wp_get_current_url();
		$block_content = '<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="' . esc_url( $current_url ) . '">Shop</a></div>';
		$block         = array(
			'blockName' => 'core/button',
			'attrs'     => array(
				'href' => $current_url,
			),
		);

		// Act.
		$result = apply_filters( 'render_block', $block_content, $block );

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
		$block_content = '<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="https://example.com/other">Shop</a></div>';
		$block         = array(
			'blockName' => 'core/button',
			'attrs'     => array(
				'href' => 'https://example.com/other',
			),
		);

		// Act.
		$result = apply_filters( 'render_block', $block_content, $block );

		// Assert.
		$this->assertSame( $block_content, $result );
		$this->assertFalse( wp_style_is( 'wc-outlet-core-button-active', 'enqueued' ) );
	}
}
