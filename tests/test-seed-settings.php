<?php
/**
 * Test the seed_settings function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\seed_settings;
use const WC_Clearance\CLEARANCE_BADGE_BG_COLOR_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_LABEL_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_TEXT_COLOR_OPTION;
use const WC_Clearance\CLEARANCE_MESSAGE_OPTION;

class Test_Seed_Settings extends WP_UnitTestCase {

	public function test_sets_badge_label_default_when_option_does_not_exist(): void {
		// Arrange.
		delete_option( CLEARANCE_BADGE_LABEL_OPTION );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( 'Clearance', get_option( CLEARANCE_BADGE_LABEL_OPTION ) );
	}

	public function test_does_not_overwrite_existing_badge_label_option(): void {
		// Arrange.
		update_option( CLEARANCE_BADGE_LABEL_OPTION, 'Custom Label' );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( 'Custom Label', get_option( CLEARANCE_BADGE_LABEL_OPTION ) );
	}

	public function test_sets_message_default_when_option_does_not_exist(): void {
		// Arrange.
		delete_option( CLEARANCE_MESSAGE_OPTION );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( 'Not eligible for change of mind returns', get_option( CLEARANCE_MESSAGE_OPTION ) );
	}

	public function test_does_not_overwrite_existing_message_option(): void {
		// Arrange.
		update_option( CLEARANCE_MESSAGE_OPTION, 'Custom message.' );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( 'Custom message.', get_option( CLEARANCE_MESSAGE_OPTION ) );
	}

	public function test_sets_badge_text_color_default_when_option_does_not_exist(): void {
		// Arrange.
		delete_option( CLEARANCE_BADGE_TEXT_COLOR_OPTION );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( '#222', get_option( CLEARANCE_BADGE_TEXT_COLOR_OPTION ) );
	}

	public function test_does_not_overwrite_existing_badge_text_color_option(): void {
		// Arrange.
		update_option( CLEARANCE_BADGE_TEXT_COLOR_OPTION, '#000000' );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( '#000000', get_option( CLEARANCE_BADGE_TEXT_COLOR_OPTION ) );
	}

	public function test_sets_badge_bg_color_default_when_option_does_not_exist(): void {
		// Arrange.
		delete_option( CLEARANCE_BADGE_BG_COLOR_OPTION );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( '#FFEE85', get_option( CLEARANCE_BADGE_BG_COLOR_OPTION ) );
	}

	public function test_does_not_overwrite_existing_badge_bg_color_option(): void {
		// Arrange.
		update_option( CLEARANCE_BADGE_BG_COLOR_OPTION, '#FF0000' );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( '#FF0000', get_option( CLEARANCE_BADGE_BG_COLOR_OPTION ) );
	}
}
