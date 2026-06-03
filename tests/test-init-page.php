<?php
/**
 * Test the init_page function.
 *
 * @package WC_Outlet
 */

use function WC_Outlet\init_page;
use function WC_Outlet\patch_wp_62407_get_block_templates_hook;

class Test_Init_Page extends WP_UnitTestCase {
	/**
	 * Tracks calls to wc_outlet_62407_patch_enabled during a test.
	 *
	 * @var int
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	private $patch_enabled_filter_calls = 0;

	public function test_registers_outlet_page_template_when_template_api_is_available(): void {
		// Arrange.
		WP_Block_Templates_Registry::get_instance()->unregister( 'outletpro//outlet-page' );
		$this->assertNull( get_block_template( 'outletpro//outlet-page', 'wp_template' ) );

		// Act.
		init_page();

		// Assert.
		$template = get_block_template( 'outletpro//outlet-page', 'wp_template' );
		$this->assertMatchesRegularExpression( '/outlet-page/', $template->id ); // The namespace differs in tests (default//outlet-page).
		$this->assertSame( 'outletpro', $template->plugin );
	}

	public function test_registers_get_block_templates_patch_filter(): void {
		// Act.
		init_page();

		// Assert.
		$this->assertSame( 999, has_filter( 'get_block_templates', 'WC_Outlet\patch_wp_62407_get_block_templates_hook' ) );
	}

	public function test_patch_normalizes_template_keys_for_outlet_page_query_when_enabled(): void {
		// Arrange.
		$templates = array(
			2 => (object) array( 'slug' => 'outlet-page' ),
			5 => (object) array( 'slug' => 'other-template' ),
		);

		// Act.
		$result = patch_wp_62407_get_block_templates_hook(
			$templates,
			array(
				'slug__in' => array( 'outlet-page' ),
			)
		);

		// Assert.
		$this->assertSame( array_values( $templates ), $result );
	}

	public function test_patch_returns_unchanged_templates_when_feature_flag_disabled(): void {
		// Arrange.
		$templates = array(
			2 => (object) array( 'slug' => 'outlet-page' ),
			5 => (object) array( 'slug' => 'other-template' ),
		);
		add_filter( 'wc_outlet_62407_patch_enabled', '__return_false' );

		// Act.
		$result = patch_wp_62407_get_block_templates_hook(
			$templates,
			array(
				'slug__in' => array( 'outlet-page' ),
			)
		);

		// Assert.
		remove_filter( 'wc_outlet_62407_patch_enabled', '__return_false' );
		$this->assertSame( $templates, $result );
	}

	public function test_patch_skips_feature_flag_evaluation_for_non_target_queries(): void {
		// Arrange.
		$templates = array(
			2 => (object) array( 'slug' => 'outlet-page' ),
			5 => (object) array( 'slug' => 'other-template' ),
		);

		$this->patch_enabled_filter_calls = 0;
		add_filter( 'wc_outlet_62407_patch_enabled', array( $this, 'count_patch_enabled_filter_calls' ) );

		// Act.
		$result = patch_wp_62407_get_block_templates_hook(
			$templates,
			array(
				'slug__in' => array( 'non-outlet-page' ),
			)
		);

		// Assert.
		remove_filter( 'wc_outlet_62407_patch_enabled', array( $this, 'count_patch_enabled_filter_calls' ) );
		$this->assertSame( $templates, $result );
		$this->assertSame( 0, $this->patch_enabled_filter_calls );
	}

	/**
	 * Count wc_outlet_62407_patch_enabled calls.
	 *
	 * @param bool $enabled Current enabled value.
	 */
	public function count_patch_enabled_filter_calls( bool $enabled ): bool {
		++$this->patch_enabled_filter_calls;

		return $enabled;
	}
}
