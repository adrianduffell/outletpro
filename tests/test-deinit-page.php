<?php
/**
 * Tests for deinit_page().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_page;
use function OutletPro\init_page;
use const OutletPro\LEGACY_OUTLET_PAGE_TEMPLATE_OPTION;
use const OutletPro\OUTLET_PAGE_OPTION;

class Test_Deinit_Page extends WP_UnitTestCase {

	public function test_template_is_unregistered_after_deinit_page(): void {
		// Arrange.
		deinit_page();
		update_option( LEGACY_OUTLET_PAGE_TEMPLATE_OPTION, 'yes' );
		init_page();
		$template = get_block_template( 'outletpro//outlet-page', 'wp_template' );
		$this->assertNotNull( $template );

		// Act.
		deinit_page();

		// Assert.
		$this->assertNull( get_block_template( 'outletpro//outlet-page', 'wp_template' ) );
	}

	public function test_unregisters_all_templates_in_outletpro_namespace(): void {
		// Arrange.
		register_block_template(
			'outletpro//secondary-template',
			array(
				'title'       => 'Secondary template',
				'description' => 'Secondary outlet template.',
				'post_types'  => array( 'page' ),
				'content'     => '<!-- wp:paragraph --><p>Outlet template</p><!-- /wp:paragraph -->',
				'plugin'      => 'outletpro',
			)
		);
		register_block_template(
			'other-plugin//keep-template',
			array(
				'title'       => 'Keep template',
				'description' => 'Template for another plugin.',
				'post_types'  => array( 'page' ),
				'content'     => '<!-- wp:paragraph --><p>Keep me</p><!-- /wp:paragraph -->',
				'plugin'      => 'other-plugin',
			)
		);

		$this->assertNotNull( get_block_template( 'outletpro//secondary-template', 'wp_template' ) );
		$this->assertNotNull( get_block_template( 'other-plugin//keep-template', 'wp_template' ) );

		// Act.
		deinit_page();

		// Assert.
		$this->assertNull( get_block_template( 'outletpro//secondary-template', 'wp_template' ) );
		$this->assertNotNull( get_block_template( 'other-plugin//keep-template', 'wp_template' ) );
	}

	public function test_safely_handles_template_not_registered(): void {
		// Arrange.
		deinit_page();
		$this->assertNull( get_block_template( 'outletpro//outlet-page', 'wp_template' ) );

		// Act.
		deinit_page();

		// Assert.
		$this->assertNull( get_block_template( 'outletpro//outlet-page', 'wp_template' ) );
	}

	public function test_deinitialization_restores_page_requests(): void {
		// Arrange.
		$page_id = self::factory()->post->create(
			array(
				'post_type' => 'page',
				'post_name' => 'outlet',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );

		// Act.
		deinit_page();
		$this->go_to( get_permalink( $page_id ) );

		// Assert.
		$this->assertTrue( is_page( $page_id ) );
		$this->assertEmpty( get_query_var( 'outletpro' ) );
	}
}
