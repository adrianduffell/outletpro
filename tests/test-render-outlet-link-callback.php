<?php
/**
 * Tests for the outlet link block.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_blocks;
use function OutletPro\register_outlet_link_block;
use const OutletPro\OUTLET_PAGE_OPTION;

class Test_Render_Outlet_Link_Callback extends WP_UnitTestCase {

	public function test_default_label(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_link_block();
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'Outlet',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );

		// Act.
		$result = do_blocks( '<!-- wp:outletpro/outlet-link {"text":"   "} /-->' );

		// Assert.
		$this->assertStringContainsString( '>Shop outlet</a>', $result );
	}

	public function test_renders_nothing_when_outlet_page_is_non_public(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_link_block();
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'draft',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );

		// Act.
		$result = do_blocks( '<!-- wp:outletpro/outlet-link {"text":"Browse outlet"} /-->' );

		// Assert.
		$this->assertSame( '', $result );
		wp_set_current_user( 0 );
	}

	public function test_deleted_outlet_page_renders_nothing(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_link_block();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		wp_delete_post( $page_id, true );

		// Act.
		$result = do_blocks( '<!-- wp:outletpro/outlet-link /-->' );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_lowercases_the_page_title(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_link_block();
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'Last Chance &#201;COULEMENT',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );

		// Act.
		$result = do_blocks( '<!-- wp:outletpro/outlet-link /-->' );

		// Assert.
		$this->assertStringContainsString( '>Shop last chance écoulement</a>', $result );
	}

	public function test_escapes_the_page_title(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_link_block();
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'last chance & final sale',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );

		// Act.
		$result = do_blocks( '<!-- wp:outletpro/outlet-link /-->' );

		// Assert.
		$this->assertStringContainsString( '>Shop last chance &amp; final sale</a>', $result );
	}

	public function test_registers_the_block(): void {
		// Arrange.
		deinit_blocks();

		// Act.
		register_outlet_link_block();

		// Assert.
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'outletpro/outlet-link' );
		$this->assertNotNull( $block_type );
	}

	public function test_renders_style_attribute(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_link_block();
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );

		// Act.
		$result = do_blocks( '<!-- wp:outletpro/outlet-link {"style":{"typography":{"fontWeight":"300"},"color":{"text":"#123456"},"spacing":{"margin":{"left":"5px","right":"6px"},"padding":"7px"}}} /-->' );

		// Assert.
		$this->assertStringContainsString( 'font-weight:300', $result );
		$this->assertStringContainsString( 'color:#123456', $result );
		$this->assertStringContainsString( 'margin-left:5px', $result );
		$this->assertStringContainsString( 'margin-right:6px', $result );
		$this->assertStringContainsString( 'padding:7px', $result );
		$this->assertMatchesRegularExpression( '/^<a\s[^>]*style="[^"]+"[^>]*>[^<]*<\/a>$/', $result );
	}

	public function test_renders_nothing_without_an_outlet_page(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_link_block();
		delete_option( OUTLET_PAGE_OPTION );

		// Act.
		$result = do_blocks( '<!-- wp:outletpro/outlet-link /-->' );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_renders_nothing_when_outlet_page_option_is_invalid(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_link_block();
		update_option( OUTLET_PAGE_OPTION, 0 );

		// Act.
		$result = do_blocks( '<!-- wp:outletpro/outlet-link /-->' );

		// Assert.
		$this->assertSame( '', $result );
	}
}
