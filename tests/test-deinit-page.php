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

class Test_Deinit_Page extends WP_UnitTestCase {

	public function test_removes_catalog_hooks(): void {
		// Arrange.
		init_page();

		// Act.
		deinit_page();

		// Assert.
		$this->assertFalse( has_filter( 'request', 'OutletPro\\outlet_catalog_request_hook' ) );
		$this->assertFalse( has_filter( 'pre_get_block_templates', 'OutletPro\\outlet_catalog_templates_hook' ) );
		$this->assertFalse( has_filter( 'get_post_metadata', 'OutletPro\\outlet_catalog_metadata_hook' ) );
		$this->assertFalse( has_filter( 'woocommerce_get_breadcrumb', 'OutletPro\\outlet_catalog_breadcrumb_hook' ) );
		$this->assertFalse( has_action( 'woocommerce_breadcrumb', 'OutletPro\\outlet_catalog_breadcrumb_fallback_hook' ) );
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
}
