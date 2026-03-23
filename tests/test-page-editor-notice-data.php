<?php
/**
 * Test the get_page_editor_notice_data function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_to_clearance;
use function WC_Clearance\get_page_editor_notice_data;
use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\seed_clearance_status_taxonomy;
use const WC_Clearance\CLEARANCE_PAGE_OPTION;
use const WC_Clearance\CLEARANCE_STATUS_TAXONOMY;

class Test_Get_Page_Editor_Notice_Data extends WP_UnitTestCase {

	public function test_returns_no_notice_when_clearance_page_option_is_not_set(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );

		// Act.
		$result = get_page_editor_notice_data();

		// Assert.
		$this->assertFalse( $result['noProductsNotice'] );
	}

	public function test_returns_no_notice_when_current_post_is_not_clearance_page(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$clearance_page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( CLEARANCE_PAGE_OPTION, $clearance_page_id );
		$other_page_id   = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$GLOBALS['post'] = get_post( $other_page_id );

		// Act.
		$result = get_page_editor_notice_data();

		// Assert.
		$this->assertFalse( $result['noProductsNotice'] );
	}

	public function test_returns_no_notice_when_no_current_post(): void {
		// Arrange.
		$clearance_page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( CLEARANCE_PAGE_OPTION, $clearance_page_id );
		unset( $GLOBALS['post'] );

		// Act.
		$result = get_page_editor_notice_data();

		// Assert.
		$this->assertFalse( $result['noProductsNotice'] );
	}

	public function test_returns_notice_when_on_clearance_page_with_no_products(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$clearance_page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( CLEARANCE_PAGE_OPTION, $clearance_page_id );
		$GLOBALS['post'] = get_post( $clearance_page_id );

		// Act.
		$result = get_page_editor_notice_data();

		// Assert.
		$this->assertTrue( $result['noProductsNotice'] );
	}

	public function test_returns_no_notice_when_on_clearance_page_with_products(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$clearance_page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( CLEARANCE_PAGE_OPTION, $clearance_page_id );
		$GLOBALS['post'] = get_post( $clearance_page_id );
		$product         = \WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );

		// Act.
		$result = get_page_editor_notice_data();

		// Assert.
		$this->assertFalse( $result['noProductsNotice'] );
	}

	public function test_returns_no_notice_when_clearance_page_option_throws(): void {
		// Arrange.
		update_option( CLEARANCE_PAGE_OPTION, 'not-a-valid-id' );

		// Act.
		$result = get_page_editor_notice_data();

		// Assert.
		$this->assertFalse( $result['noProductsNotice'] );
	}

	public function test_returns_no_notice_when_taxonomy_does_not_exist(): void {
		// Arrange.
		$clearance_page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( CLEARANCE_PAGE_OPTION, $clearance_page_id );
		$GLOBALS['post'] = get_post( $clearance_page_id );
		unregister_taxonomy( CLEARANCE_STATUS_TAXONOMY );

		// Act.
		$result = get_page_editor_notice_data();

		// Assert.
		$this->assertFalse( $result['noProductsNotice'] );
	}

	public function test_products_url_points_to_product_list_screen(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );

		// Act.
		$result = get_page_editor_notice_data();

		// Assert.
		$this->assertStringContainsString( 'edit.php', $result['productsUrl'] );
		$this->assertStringContainsString( 'post_type=product', $result['productsUrl'] );
	}
}
