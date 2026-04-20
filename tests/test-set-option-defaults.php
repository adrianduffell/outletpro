<?php
/**
 * Test the set_option_defaults function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\set_option_defaults;
use const WC_Clearance\CLEARANCE_BADGE_LABEL_OPTION;
use const WC_Clearance\CLEARANCE_MESSAGE_OPTION;

class Test_Set_Option_Defaults extends WP_UnitTestCase {

	public function test_sets_badge_label_default_when_option_does_not_exist(): void {
		// Arrange.
		delete_option( CLEARANCE_BADGE_LABEL_OPTION );

		// Act.
		set_option_defaults();

		// Assert.
		$this->assertSame( 'Clearance', get_option( CLEARANCE_BADGE_LABEL_OPTION ) );
	}

	public function test_does_not_overwrite_existing_badge_label_option(): void {
		// Arrange.
		update_option( CLEARANCE_BADGE_LABEL_OPTION, 'Custom Label' );

		// Act.
		set_option_defaults();

		// Assert.
		$this->assertSame( 'Custom Label', get_option( CLEARANCE_BADGE_LABEL_OPTION ) );
	}

	public function test_sets_message_default_when_option_does_not_exist(): void {
		// Arrange.
		delete_option( CLEARANCE_MESSAGE_OPTION );

		// Act.
		set_option_defaults();

		// Assert.
		$this->assertSame( 'Not eligible for change of mind returns', get_option( CLEARANCE_MESSAGE_OPTION ) );
	}

	public function test_does_not_overwrite_existing_message_option(): void {
		// Arrange.
		update_option( CLEARANCE_MESSAGE_OPTION, 'Custom message.' );

		// Act.
		set_option_defaults();

		// Assert.
		$this->assertSame( 'Custom message.', get_option( CLEARANCE_MESSAGE_OPTION ) );
	}
}
