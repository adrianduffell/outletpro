<?php
/**
 * Tests for append_block_editor_settings_hook().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\init_block_editor;
use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\seed_clearance_status_taxonomy;
use const WC_Clearance\CLEARANCE_STATUS_CANONICAL_TERM;
use const WC_Clearance\CLEARANCE_STATUS_TAXONOMY;

class Test_Append_Block_Editor_Settings_Hook extends WP_UnitTestCase {

	public function test_settings_contain_clearance_term_id(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		init_block_editor();
		$canonical_term = get_term_by( 'name', CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

		// Act.
		$settings = apply_filters( 'block_editor_settings_all', array(), new WP_Block_Editor_Context() );

		// Assert.
		$this->assertArrayHasKey( 'wcClearanceCanonicalTermId', $settings );
		$this->assertSame( $canonical_term->term_id, $settings['wcClearanceCanonicalTermId'] );
	}

	public function test_settings_do_not_contain_clearance_term_id_when_canonical_term_missing(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		init_block_editor();

		// Act.
		$settings = apply_filters( 'block_editor_settings_all', array(), new WP_Block_Editor_Context() );

		// Assert.
		$this->assertArrayNotHasKey( 'wcClearanceCanonicalTermId', $settings );
	}

	public function test_settings_contain_default_message_for_non_us_store(): void {
		// Arrange.
		update_option( 'woocommerce_default_country', 'GB' );
		register_clearance_status_taxonomy();
		init_block_editor();

		// Act.
		$settings = apply_filters( 'block_editor_settings_all', array(), new WP_Block_Editor_Context() );

		// Assert.
		$this->assertArrayHasKey( 'wcClearanceDefaultMessage', $settings );
		$this->assertSame( 'Only while stocks last', $settings['wcClearanceDefaultMessage'] );
	}

	public function test_settings_contain_default_message_for_us_store(): void {
		// Arrange.
		update_option( 'woocommerce_default_country', 'US' );
		register_clearance_status_taxonomy();
		init_block_editor();

		// Act.
		$settings = apply_filters( 'block_editor_settings_all', array(), new WP_Block_Editor_Context() );

		// Assert.
		$this->assertArrayHasKey( 'wcClearanceDefaultMessage', $settings );
		$this->assertSame( 'Only while supplies last', $settings['wcClearanceDefaultMessage'] );
	}

	public function test_existing_settings_are_preserved(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		init_block_editor();
		$initial_settings = array( 'foo' => 'bar' );

		// Act.
		$settings = apply_filters( 'block_editor_settings_all', $initial_settings, new WP_Block_Editor_Context() );

		// Assert.
		$this->assertArrayHasKey( 'foo', $settings );
		$this->assertSame( 'bar', $settings['foo'] );
	}
}
