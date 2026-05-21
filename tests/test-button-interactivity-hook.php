<?php
/**
 * Tests for inject_button_interactivity_attributes_hook().
 *
 * @package WC_Outlet
 */

use function WC_Outlet\init_button_interactivity;

class Test_Button_Interactivity_Hook extends WP_UnitTestCase {

	public function test_filter_is_registered(): void {
		// Arrange.
		remove_all_filters( 'render_block' );

		// Act.
		init_button_interactivity();

		// Assert.
		$this->assertSame( 10, has_filter( 'render_block', 'WC_Outlet\inject_button_interactivity_attributes_hook' ) );
		$this->assertSame( 10, has_action( 'wp_enqueue_scripts', 'WC_Outlet\enqueue_button_interactivity_module_hook' ) );
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

		$processor = new WP_HTML_Tag_Processor( $result );

		// Assert.
		$this->assertTrue( $processor->next_tag() );
		$this->assertSame( 'wc-outlet/button-interactivity', $processor->get_attribute( 'data-wp-interactive' ) );
		$this->assertSame( 'actions.logHello', $processor->get_attribute( 'data-wp-on--click' ) );
		$this->assertStringContainsString( 'wp-block-button', $result );
	}

	public function test_empty_button_block_markup_is_unchanged(): void {
		// Arrange.
		remove_all_filters( 'render_block' );
		init_button_interactivity();
		$markup = '';
		$block  = array(
			'blockName' => 'core/button',
		);

		// Act.
		$result = apply_filters( 'render_block', $markup, $block );

		// Assert.
		$this->assertSame( '', $result );
	}
}
