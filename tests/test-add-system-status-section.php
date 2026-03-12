<?php
/**
 * Test the add_system_status_section function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_system_status_section;
use function WC_Clearance\create_clearance_page;
use function WC_Clearance\register_clearance_status_taxonomy;
use const WC_Clearance\CLEARANCE_PAGE_OPTION;
use const WC_Clearance\CLEARANCE_STATUS_CANONICAL_TERM;
use const WC_Clearance\CLEARANCE_STATUS_TAXONOMY;

class Test_Add_System_Status_Section extends WP_UnitTestCase {

	public function test_shows_yes_when_taxonomy_is_registered(): void {
		// Arrange.
		register_clearance_status_taxonomy();

		// Expect.
		$this->expectOutputRegex( '/data-testid="clearance-taxonomy-registered"[^>]*>\s*Yes\s*</' );

		// Act.
		add_system_status_section();
	}

	public function test_shows_no_when_taxonomy_is_not_registered(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		unregister_taxonomy( CLEARANCE_STATUS_TAXONOMY );

		// Expect.
		$this->expectOutputRegex( '/data-testid="clearance-taxonomy-registered"[^>]*>\s*No\s*</' );

		// Act.
		add_system_status_section();
	}

	public function test_shows_term_id_when_canonical_term_exists(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$result  = wp_insert_term( CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );
		$term_id = $result['term_id'];

		// Expect.
		$this->expectOutputRegex( '/data-testid="clearance-canonical-term-id"[^>]*>\s*' . preg_quote( (string) $term_id, '/' ) . '\s*</' );

		// Act.
		add_system_status_section();
	}

	public function test_shows_warning_when_canonical_term_not_found(): void {
		// Arrange.
		register_clearance_status_taxonomy();

		// Expect.
		$this->expectOutputRegex( '/class="error"><span>Canonical term not found\./' );

		// Act.
		add_system_status_section();
	}

	public function test_shows_zero_when_no_products_in_clearance(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		wp_insert_term( CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

		// Expect.
		$this->expectOutputRegex( '/data-testid="clearance-product-count"[^>]*>\s*0\s*</' );

		// Act.
		add_system_status_section();
	}

	public function test_shows_correct_product_count(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		wp_insert_term( CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );
		$term = get_term_by( 'name', CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

		$product_one = \WC_Helper_Product::create_simple_product();
		$product_two = \WC_Helper_Product::create_simple_product();

		wp_set_object_terms( $product_one->get_id(), $term->term_id, CLEARANCE_STATUS_TAXONOMY );
		wp_set_object_terms( $product_two->get_id(), $term->term_id, CLEARANCE_STATUS_TAXONOMY );

		// Expect.
		$this->expectOutputRegex( '/data-testid="clearance-product-count"[^>]*>\s*2\s*</' );

		// Act.
		add_system_status_section();
	}

	public function test_only_counts_published_products(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		wp_insert_term( CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );
		$term = get_term_by( 'name', CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

		$published_product = \WC_Helper_Product::create_simple_product();
		$draft_product     = \WC_Helper_Product::create_simple_product();
		wp_update_post(
			array(
				'ID'          => $draft_product->get_id(),
				'post_status' => 'draft',
			)
		);

		wp_set_object_terms( $published_product->get_id(), $term->term_id, CLEARANCE_STATUS_TAXONOMY );
		wp_set_object_terms( $draft_product->get_id(), $term->term_id, CLEARANCE_STATUS_TAXONOMY );

		// Expect.
		$this->expectOutputRegex( '/data-testid="clearance-product-count"[^>]*>\s*1\s*</' );

		// Act.
		add_system_status_section();
	}

	public function test_output_contains_section_heading(): void {
		// Arrange.
		register_clearance_status_taxonomy();

		// Expect.
		$this->expectOutputRegex( '/Clearance Section/' );

		// Act.
		add_system_status_section();
	}

	public function test_shows_page_link_when_page_exists(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$existing_id = (int) get_option( CLEARANCE_PAGE_OPTION );
		if ( $existing_id > 0 ) {
			wp_delete_post( $existing_id, true );
		}
		delete_option( CLEARANCE_PAGE_OPTION );
		create_clearance_page();
		$page_id = (int) get_option( CLEARANCE_PAGE_OPTION );
		$page    = get_post( $page_id );
		$this->assertNotNull( $page );

		// Expect.
		$this->expectOutputRegex( '/data-testid="clearance-section-page"[^>]*>.*<a\s[^>]*>' . preg_quote( $page->post_title, '/' ) . '<\/a>/s' );

		// Act.
		add_system_status_section();
	}

	public function test_shows_page_status_when_page_exists(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$existing_id = (int) get_option( CLEARANCE_PAGE_OPTION );
		if ( $existing_id > 0 ) {
			wp_delete_post( $existing_id, true );
		}
		delete_option( CLEARANCE_PAGE_OPTION );
		create_clearance_page();

		// Expect.
		$this->expectOutputRegex( '/data-testid="clearance-section-page"[^>]*>.*\(draft\)/s' );

		// Act.
		add_system_status_section();
	}

	public function test_shows_published_status_when_page_is_published(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$existing_id = (int) get_option( CLEARANCE_PAGE_OPTION );
		if ( $existing_id > 0 ) {
			wp_delete_post( $existing_id, true );
		}
		delete_option( CLEARANCE_PAGE_OPTION );
		create_clearance_page();
		$page_id = (int) get_option( CLEARANCE_PAGE_OPTION );
		wp_update_post(
			array(
				'ID'          => $page_id,
				'post_status' => 'publish',
			)
		);

		// Expect.
		$this->expectOutputRegex( '/data-testid="clearance-section-page"[^>]*>.*\(publish\)/s' );

		// Act.
		add_system_status_section();
	}

	public function test_shows_error_when_page_not_found(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$existing_id = (int) get_option( CLEARANCE_PAGE_OPTION );
		if ( $existing_id > 0 ) {
			wp_delete_post( $existing_id, true );
		}
		delete_option( CLEARANCE_PAGE_OPTION );

		// Expect.
		$this->expectOutputRegex( '/data-testid="clearance-section-page"[^>]*>.*class="error"><span>Clearance section page not found\./s' );

		// Act.
		add_system_status_section();
	}
}
