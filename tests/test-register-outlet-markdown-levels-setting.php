<?php
/**
 * Tests for register_outlet_markdown_levels_setting().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_settings;
use function OutletPro\register_outlet_markdown_levels_setting;
use const OutletPro\OUTLET_MARKDOWN_LEVELS_OPTION;

class Test_Register_Outlet_Markdown_Levels_Setting extends WP_UnitTestCase {

	public function test_registers_the_rest_schema(): void {
		// Arrange.
		deinit_settings();

		// Act.
		register_outlet_markdown_levels_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertSame(
			array(
				'title'       => 'Markdown Levels',
				'type'        => 'array',
				'uniqueItems' => true,
				'items'       => array(
					'type'    => 'integer',
					'minimum' => 1,
					'maximum' => 100,
				),
			),
			$settings[ OUTLET_MARKDOWN_LEVELS_OPTION ]['show_in_rest']['schema']
		);
	}

	public function test_sanitizes_saved_levels(): void {
		// Arrange.
		deinit_settings();
		register_outlet_markdown_levels_setting();

		// Act.
		update_option( OUTLET_MARKDOWN_LEVELS_OPTION, array( 70, '50', 30 ) );

		// Assert.
		$this->assertSame( array( 70, 30 ), get_option( OUTLET_MARKDOWN_LEVELS_OPTION ) );
	}

	public function test_defaults_to_an_empty_list(): void {
		// Arrange.
		deinit_settings();
		delete_option( OUTLET_MARKDOWN_LEVELS_OPTION );

		// Act.
		register_outlet_markdown_levels_setting();

		// Assert.
		$this->assertSame( array(), get_option( OUTLET_MARKDOWN_LEVELS_OPTION ) );
	}
}
