<?php
/**
 * Tests for deinit_page().
 *
 * @package WC_Outlet
 */

use function WC_Outlet\deinit_page;
use function WC_Outlet\init_page;

class Test_Deinit_Page extends WP_UnitTestCase {

	public function test_template_is_unregistered_after_deinit_page(): void {
		// Arrange.
		deinit_page();
		init_page();
		$template = get_block_template( 'outletpro//outlet-page', 'wp_template' );
		$this->assertNotNull( $template );

		// Act.
		deinit_page();

		// Assert.
		$this->assertNull( get_block_template( 'outletpro//outlet-page', 'wp_template' ) );
	}

	public function test_unregistered_all_templates_in_outletpro_namespace(): void {
		// Arrange.
		deinit_page();
		unregister_block_template( 'other-plugin//keep-template' );

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

		unregister_block_template( 'other-plugin//keep-template' );
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
