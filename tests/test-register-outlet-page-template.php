<?php
/**
 * Tests for register_outlet_page_template().
 *
 * @package WC_Outlet
 */

use function WC_Outlet\get_outlet_page_template_content;

class Test_Register_Outlet_Page_Template extends WP_UnitTestCase {

	public function test_template_content_includes_expected_blocks(): void {
		// Act.
		$content = get_outlet_page_template_content();

		// Assert.
		$this->assertStringContainsString( '<!-- wp:template-part {"slug":"header","area":"header","tagName":"header"} /-->', $content );
		$this->assertStringContainsString( '<!-- wp:woocommerce/catalog-sorting /-->', $content );
		$this->assertStringContainsString( '<!-- wp:post-title {"level":1} /-->', $content );
		$this->assertStringContainsString( '<!-- wp:post-content {"align":"wide"} /-->', $content );
		$this->assertStringContainsString( '<!-- wp:template-part {"slug":"footer","area":"footer","tagName":"footer"} /-->', $content );
	}
}
