<?php
/**
 * Tests for inject_button_interactivity_attributes_hook().
 *
 * @package WC_Outlet
 */

use function WC_Outlet\init_button_interactivity;

class Test_Button_Interactivity_Hook extends WP_UnitTestCase {

	public function test_filter_is_registered_by_init_button_interactivity(): void {
		// Arrange.
		remove_all_filters( 'render_block' );

		// Act.
		init_button_interactivity();

		// Assert.
		$this->assertSame( 10, has_filter( 'render_block', 'WC_Outlet\inject_button_interactivity_attributes_hook' ) );
	}

	public function test_non_button_blocks_are_unchanged(): void {
		// Arrange.
		remove_all_filters( 'render_block' );
		init_button_interactivity();
		$markup = '<p class="wp-block-paragraph">Paragraph</p>';
		$block  = array(
			'blockName' => 'core/paragraph',
		);

		// Act.
		$result = apply_filters( 'render_block', $markup, $block );

		// Assert.
		$this->assertSame( $markup, $result );
	}

	public function test_button_blocks_receive_interactivity_attributes(): void {
		// Arrange.
		remove_all_filters( 'render_block' );
		init_button_interactivity();
		$markup = '<div class="wp-block-button"><a class="wp-block-button__link wp-element-button">Buy now</a></div>';
		$block  = array(
			'blockName' => 'core/button',
		);

		// Act.
		$result = apply_filters( 'render_block', $markup, $block );

		// Assert.
		$this->assertStringContainsString( 'data-wp-interactive="wc-outlet/button-interactivity"', $result );
		$this->assertStringContainsString( 'data-wp-on--click="actions.logHello"', $result );
		$this->assertStringContainsString( 'wp-block-button', $result );
	}
}
