<?php
/**
 * Outlet Page block insertion restrictions.
 *
 * @package OutletPro
 */

use function OutletPro\init_catalog_templates;
use const OutletPro\OUTLET_PAGE_OPTION;

class Test_Lock_Outlet_Page_Editor_Hook extends WP_UnitTestCase {

	public function test_configured_outlet_page_locks_blocks_and_preserves_other_settings(): void {
		// Arrange.
		init_catalog_templates();
		switch_theme( 'twentytwentyfive' );
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$context = new WP_Block_Editor_Context( array( 'post' => get_post( $page_id ) ) );

		// Act.
		$settings = get_block_editor_settings( array( 'supportsTemplateMode' => true ), $context );

		// Assert.
		$this->assertSame( 'all', $settings['templateLock'] );
		$this->assertTrue( $settings['supportsTemplateMode'] );
	}

	public function test_other_pages_keep_their_existing_block_lock(): void {
		// Arrange.
		init_catalog_templates();
		switch_theme( 'twentytwentyfive' );
		$page_id  = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$other_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$context = new WP_Block_Editor_Context( array( 'post' => get_post( $other_id ) ) );

		// Act.
		$settings = get_block_editor_settings( array( 'templateLock' => 'insert' ), $context );

		// Assert.
		$this->assertSame( 'insert', $settings['templateLock'] );
	}

	public function test_site_editor_is_not_locked_when_it_has_the_outlet_page_context(): void {
		// Arrange.
		init_catalog_templates();
		switch_theme( 'twentytwentyfive' );
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$context = new WP_Block_Editor_Context(
			array(
				'name' => 'core/edit-site',
				'post' => get_post( $page_id ),
			)
		);

		// Act.
		$settings = get_block_editor_settings( array( 'templateLock' => false ), $context );

		// Assert.
		$this->assertFalse( $settings['templateLock'] );
	}

	public function test_template_posts_are_not_locked_even_if_selected_as_the_outlet_page(): void {
		// Arrange.
		init_catalog_templates();
		switch_theme( 'twentytwentyfive' );
		$template_id = self::factory()->post->create( array( 'post_type' => 'wp_template' ) );
		update_option( OUTLET_PAGE_OPTION, $template_id );
		$context = new WP_Block_Editor_Context( array( 'post' => get_post( $template_id ) ) );

		// Act.
		$settings = get_block_editor_settings( array( 'templateLock' => false ), $context );

		// Assert.
		$this->assertFalse( $settings['templateLock'] );
	}

	public function test_classic_theme_outlet_page_content_stays_editable(): void {
		// Arrange.
		init_catalog_templates();
		switch_theme( 'storefront' );
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$context = new WP_Block_Editor_Context( array( 'post' => get_post( $page_id ) ) );

		// Act.
		$settings = get_block_editor_settings( array( 'templateLock' => false ), $context );

		// Assert.
		$this->assertFalse( $settings['templateLock'] );
	}

	public function test_editors_without_a_post_context_stay_unlocked(): void {
		// Arrange.
		init_catalog_templates();
		switch_theme( 'twentytwentyfive' );
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );

		// Act.
		$settings = get_block_editor_settings( array( 'templateLock' => false ), new WP_Block_Editor_Context() );

		// Assert.
		$this->assertFalse( $settings['templateLock'] );
	}
}
