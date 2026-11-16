<?php
/**
 * Template-part deinitialization tests.
 *
 * @package OutletPro
 */

use function OutletPro\deinit_patterns;
use function OutletPro\deinit_template_parts;
use function OutletPro\init_patterns;
use function OutletPro\init_template_parts;

class Test_Deinit_Template_Parts extends WP_UnitTestCase {

	public function test_removes_default_discovery_and_lookup(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		deinit_patterns();
		init_patterns();
		init_template_parts();
		$this->assertInstanceOf( WP_Block_Template::class, get_block_file_template( get_stylesheet() . '//outlet-hero', 'wp_template_part' ) );

		// Act.
		deinit_template_parts();

		// Assert.
		$this->assertNull( get_block_file_template( get_stylesheet() . '//outlet-hero', 'wp_template_part' ) );
		$this->assertEmpty( get_block_templates( array( 'slug__in' => array( 'outlet-hero' ) ), 'wp_template_part' ) );
	}
}
