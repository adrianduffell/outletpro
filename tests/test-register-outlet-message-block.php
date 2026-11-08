<?php
/**
 * Tests for register_outlet_message_block().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_blocks;
use function OutletPro\register_outlet_message_block;

class Test_Register_Outlet_Message_Block extends WP_UnitTestCase {

	public function test_registers_bold_style_for_outlet_message_block(): void {
		// Arrange.
		deinit_blocks();

		// Act.
		register_outlet_message_block();
		$style = WP_Block_Styles_Registry::get_instance()->get_registered( 'outletpro/outlet-message', 'bold' );

		// Assert.
		$this->assertSame(
			'var:preset|font-size|small',
			$style['style_data']['typography']['fontSize']
		);
		$this->assertSame( '600', $style['style_data']['typography']['fontWeight'] );
	}

	public function test_registers_notice_as_an_optional_message_style(): void {
		// Arrange.
		deinit_blocks();

		// Act.
		register_outlet_message_block();
		$style = WP_Block_Styles_Registry::get_instance()->get_registered( 'outletpro/outlet-message', 'notice' );

		// Assert.
		$this->assertIsArray( $style );
		$this->assertSame( 'Notice', $style['label'] );
		$this->assertFalse( $style['is_default'] ?? false );
	}

	public function test_generates_notice_styles_with_padding_and_background_without_font_weight(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_message_block();
		WP_Theme_JSON_Resolver::clean_cached_data();

		// Act.
		$css = WP_Theme_JSON_Resolver::get_merged_data()->get_stylesheet(
			array( 'styles' ),
			array( 'custom' ),
			array( 'include_block_style_variations' => true )
		);

		// Assert.
		$this->assertSame(
			1,
			preg_match( '/\.wp-block-outletpro-outlet-message[^{}]*\.is-style-notice[^{}]*\{([^}]+)\}/', $css, $matches )
		);
		$this->assertMatchesRegularExpression( '/background-color:\s*#00000005;/', $matches[1] );
		$this->assertStringNotContainsString( 'font-weight:', $matches[1] );
		$this->assertMatchesRegularExpression( '/padding-top:\s*1em;/', $matches[1] );
		$this->assertMatchesRegularExpression( '/padding-right:\s*1em;/', $matches[1] );
		$this->assertMatchesRegularExpression( '/padding-bottom:\s*1em;/', $matches[1] );
		$this->assertMatchesRegularExpression( '/padding-left:\s*1em;/', $matches[1] );
	}
}
