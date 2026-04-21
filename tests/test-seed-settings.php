<?php
/**
 * Test the seed_settings function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\seed_settings;
use const WC_Clearance\CLEARANCE_BADGE_LABEL_OPTION;

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
}
