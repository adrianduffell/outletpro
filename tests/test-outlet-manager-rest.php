<?php
/**
 * Outlet manager endpoint tests.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\deinit_taxonomies;
use function OutletPro\init_taxonomies;
use function OutletPro\is_outlet;
use function OutletPro\seed_outlet_status_taxonomy;

class Test_Outlet_Manager_Rest extends WP_UnitTestCase {
	public function test_rejects_anonymous_product_queries(): void {
		// Arrange.
		wp_set_current_user( 0 );

		// Act.
		$response = rest_do_request( new WP_REST_Request( 'GET', '/outletpro/v1/products' ) );

		// Assert.
		$this->assertSame( 401, $response->get_status() );
	}

	public function test_page_editing_does_not_grant_product_management(): void {
		// Arrange.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'editor' ) ) );

		// Act.
		$response = rest_do_request( new WP_REST_Request( 'GET', '/outletpro/v1/products' ) );

		// Assert.
		$this->assertSame( 403, $response->get_status() );
	}

	public function test_defaults_to_outlet_products_and_returns_membership(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$included = WC_Helper_Product::create_simple_product();
		$excluded = WC_Helper_Product::create_simple_product();
		add_to_outlet( $included );

		// Act.
		$response = rest_do_request( new WP_REST_Request( 'GET', '/outletpro/v1/products' ) );
		$data     = $response->get_data();

		// Assert.
		$this->assertSame( 200, $response->get_status(), wp_json_encode( $data ) );
		$this->assertContains( $included->get_id(), wp_list_pluck( $data['items'], 'id' ) );
		$this->assertNotContains( $excluded->get_id(), wp_list_pluck( $data['items'], 'id' ) );
		$this->assertTrue( $data['items'][0]['outlet'] );
		$this->assertTrue( $data['items'][0]['canEdit'] );
		$this->assertStringContainsString( 'post.php', $data['items'][0]['editUrl'] );
		$this->assertArrayHasKey( 'price', $data['items'][0] );
	}

	public function test_returns_total_sales_for_sold_and_unsold_products(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$sold = WC_Helper_Product::create_simple_product();
		$sold->set_name( 'Manager sales total A' );
		$sold->set_total_sales( 1234 );
		$sold->save();
		$unsold = WC_Helper_Product::create_simple_product();
		$unsold->set_name( 'Manager sales total B' );
		$unsold->save();
		$request = new WP_REST_Request( 'GET', '/outletpro/v1/products' );
		$request->set_query_params(
			array(
				'membership' => 'all',
				'search'     => 'Manager sales total',
			)
		);

		// Act.
		$response = rest_do_request( $request );
		$data     = $response->get_data();

		// Assert.
		$this->assertSame( 200, $response->get_status() );
		$this->assertSame(
			array(
				$sold->get_id()   => 1234,
				$unsold->get_id() => 0,
			),
			array_column( $data['items'], 'total_sales', 'id' )
		);
	}

	public function test_returns_age_in_days_from_product_creation(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		update_option( 'timezone_string', 'Australia/Perth' );
		$old = WC_Helper_Product::create_simple_product();
		$old->set_name( 'Manager age A' );
		$old->set_date_created( time() - 38 * DAY_IN_SECONDS + HOUR_IN_SECONDS );
		$old->save();
		$recent = WC_Helper_Product::create_simple_product();
		$recent->set_name( 'Manager age B' );
		$recent->set_date_created( time() - HOUR_IN_SECONDS );
		$recent->save();
		$future = WC_Helper_Product::create_simple_product();
		$future->set_name( 'Manager age C' );
		$future->set_date_created( time() + DAY_IN_SECONDS );
		$future->save();
		$request = new WP_REST_Request( 'GET', '/outletpro/v1/products' );
		$request->set_query_params(
			array(
				'membership' => 'all',
				'search'     => 'Manager age',
			)
		);

		// Act.
		$response = rest_do_request( $request );

		// Assert.
		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( array( 37, 0, 0 ), array_column( $response->get_data()['items'], 'age_days' ) );
	}

	public function test_returns_null_age_when_creation_date_is_unavailable(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Restore the date filter even if the endpoint fails.
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$product = WC_Helper_Product::create_simple_product();
		$filter  = static function ( $date, $queried_product ) use ( $product ) {
			return $queried_product->get_id() === $product->get_id() ? null : $date;
		};
		$request = new WP_REST_Request( 'GET', '/outletpro/v1/products' );
		$request->set_query_params(
			array(
				'membership' => 'all',
				'search'     => $product->get_name(),
			)
		);
		add_filter( 'woocommerce_product_get_date_created', $filter, 10, 2 );

		try {
			// Act.
			$response = rest_do_request( $request );

			// Assert.
			$this->assertSame( 200, $response->get_status() );
			$this->assertNull( array_column( $response->get_data()['items'], 'age_days', 'id' )[ $product->get_id() ] );
		} finally {
			remove_filter( 'woocommerce_product_get_date_created', $filter, 10 );
		}
	}

	public function test_returns_stock_management_and_available_quantities(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$managed = WC_Helper_Product::create_simple_product();
		$managed->set_name( 'Manager stock count A' );
		$managed->set_manage_stock( true );
		$managed->set_stock_quantity( 8 );
		$managed->save();
		$unmanaged = WC_Helper_Product::create_simple_product();
		$unmanaged->set_name( 'Manager stock count B' );
		$unmanaged->set_manage_stock( false );
		$unmanaged->save();
		$request = new WP_REST_Request( 'GET', '/outletpro/v1/products' );
		$request->set_query_params(
			array(
				'membership' => 'all',
				'search'     => 'Manager stock count',
			)
		);

		// Act.
		$response = rest_do_request( $request );
		$data     = $response->get_data();

		// Assert.
		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( array( true, false ), array_column( $data['items'], 'manage_stock' ) );
		$this->assertSame( array( 8, null ), array_column( $data['items'], 'stock_quantity' ) );
	}

	public function test_returns_product_thumbnails_and_placeholders(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$attachment = $this->factory->attachment->create_upload_object( DIR_TESTDATA . '/images/canola.jpg' );
		$pictured   = WC_Helper_Product::create_simple_product();
		$pictured->set_name( 'Manager thumbnail A' );
		$pictured->set_image_id( $attachment );
		$pictured->save();
		$unpictured = WC_Helper_Product::create_simple_product();
		$unpictured->set_name( 'Manager thumbnail B' );
		$unpictured->save();
		$request = new WP_REST_Request( 'GET', '/outletpro/v1/products' );
		$request->set_query_params(
			array(
				'membership' => 'all',
				'search'     => 'Manager thumbnail',
			)
		);

		// Act.
		$response = rest_do_request( $request );
		$data     = $response->get_data();

		// Assert.
		$this->assertSame( 200, $response->get_status() );
		$this->assertStringContainsString( 'canola', $data['items'][0]['image'] );
		$this->assertSame( wc_placeholder_img_src( 'woocommerce_thumbnail' ), $data['items'][1]['image'] );
	}

	public function test_counts_all_outlet_products_independently_of_the_table_query(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$first    = WC_Helper_Product::create_simple_product();
		$second   = WC_Helper_Product::create_simple_product();
		$excluded = WC_Helper_Product::create_simple_product();
		$excluded->set_name( 'Manager count excluded' );
		$excluded->save();
		add_to_outlet( $first );
		add_to_outlet( $second );
		$request = new WP_REST_Request( 'GET', '/outletpro/v1/products' );
		$request->set_query_params(
			array(
				'membership' => 'all',
				'search'     => 'Manager count excluded',
				'per_page'   => 1,
			)
		);

		// Act.
		$response = rest_do_request( $request );
		$data     = $response->get_data();

		// Assert.
		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( array( $excluded->get_id() ), wp_list_pluck( $data['items'], 'id' ) );
		$this->assertSame( 1, $data['totalItems'] );
		$this->assertSame( 2, $data['totalOutletItems'] );
	}

	public function test_returns_an_error_when_the_outlet_count_query_fails(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Restore the REST filter even if the request fails.
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$count_filter = static function ( $result, $server, $request ) {
			if ( '/wc/v3/products' !== $request->get_route() ) {
				return $result;
			}
			if ( 1 !== $request['per_page'] ) {
				return $result;
			}
			if ( true !== $request['outletpro'] ) {
				return $result;
			}
			return new WP_Error( 'count_unavailable', 'Outlet product count unavailable.', array( 'status' => 500 ) );
		};
		add_filter( 'rest_pre_dispatch', $count_filter, 10, 3 );

		// Act.
		try {
			$response = rest_do_request( new WP_REST_Request( 'GET', '/outletpro/v1/products' ) );
		} finally {
			remove_filter( 'rest_pre_dispatch', $count_filter, 10 );
		}

		// Assert.
		$this->assertSame( 500, $response->get_status() );
		$this->assertSame( 'count_unavailable', $response->get_data()['code'] );
	}

	public function test_preserves_sale_price_formatting_and_accessible_descriptions(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$product = WC_Helper_Product::create_simple_product();
		$product->set_name( 'Manager sale price' );
		$product->set_regular_price( '18' );
		$product->set_sale_price( '12' );
		$product->set_price( '12' );
		$product->save();
		$request = new WP_REST_Request( 'GET', '/outletpro/v1/products' );
		$request->set_query_params(
			array(
				'membership' => 'all',
				'search'     => 'Manager sale price',
			)
		);

		// Act.
		$response = rest_do_request( $request );
		$data     = $response->get_data();

		// Assert.
		$this->assertSame( 200, $response->get_status() );
		$this->assertStringContainsString( '<del aria-hidden="true">', $data['items'][0]['price'] );
		$this->assertStringContainsString( '<ins aria-hidden="true">', $data['items'][0]['price'] );
		$this->assertStringContainsString( 'class="screen-reader-text"', $data['items'][0]['price'] );
	}

	public function test_sanitizes_price_markup_from_woocommerce_extensions(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Restore the price filter even if the endpoint fails.
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$product = WC_Helper_Product::create_simple_product();
		$product->set_name( 'Manager extension price' );
		$product->save();
		$request      = new WP_REST_Request( 'GET', '/outletpro/v1/products' );
		$price_filter = static fn(): string => '<span class="amount" onclick="alert(1)">&pound;12.00</span>';
		$request->set_query_params(
			array(
				'membership' => 'all',
				'search'     => 'Manager extension price',
			)
		);
		add_filter( 'woocommerce_get_price_html', $price_filter );

		// Act.
		try {
			$data = rest_do_request( $request )->get_data();
		} finally {
			remove_filter( 'woocommerce_get_price_html', $price_filter );
		}

		// Assert.
		$this->assertSame( '<span class="amount">&pound;12.00</span>', $data['items'][0]['price'] );
	}

	public function test_searches_skus_and_filters_categories_with_server_pagination(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$category = wp_insert_term( 'Manager category', 'product_cat' );
		$first    = WC_Helper_Product::create_simple_product();
		$first->set_name( 'Alpha' );
		$first->set_sku( 'manager-unique-1' );
		$first->set_category_ids( array( $category['term_id'] ) );
		$first->save();
		$second = WC_Helper_Product::create_simple_product();
		$second->set_name( 'Beta' );
		$second->set_sku( 'manager-unique-2' );
		$second->set_category_ids( array( $category['term_id'] ) );
		$second->save();
		$request = new WP_REST_Request( 'GET', '/outletpro/v1/products' );
		$request->set_query_params(
			array(
				'membership' => 'all',
				'search'     => 'manager-unique',
				'category'   => $category['term_id'],
				'per_page'   => 1,
				'page'       => 2,
			)
		);

		// Act.
		$response = rest_do_request( $request );
		$data     = $response->get_data();

		// Assert.
		$this->assertSame( 200, $response->get_status(), wp_json_encode( $data ) );
		$this->assertSame( array( $second->get_id() ), wp_list_pluck( $data['items'], 'id' ) );
		$this->assertSame( 2, $data['totalItems'] );
		$this->assertSame( 2, $data['totalPages'] );
		$this->assertFalse( $data['items'][0]['outlet'] );
	}

	public function test_searches_product_names_and_returns_drafts(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'shop_manager' ) ) );
		$product = WC_Helper_Product::create_simple_product();
		$product->set_name( 'Manager name search' );
		$product->set_status( 'draft' );
		$product->save();
		$request = new WP_REST_Request( 'GET', '/outletpro/v1/products' );
		$request->set_query_params(
			array(
				'membership' => 'all',
				'search'     => 'Manager name search',
			)
		);

		// Act.
		$data = rest_do_request( $request )->get_data();

		// Assert.
		$this->assertSame( array( $product->get_id() ), wp_list_pluck( $data['items'], 'id' ) );
		$this->assertSame( 'draft', $data['items'][0]['status'] );
	}

	public function test_bulk_include_is_idempotent_and_fires_existing_hook(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$product = WC_Helper_Product::create_simple_product();
		$request = new WP_REST_Request( 'POST', '/outletpro/v1/products/batch' );
		$request->set_body_params(
			array(
				'ids'    => array( $product->get_id() ),
				'outlet' => true,
			)
		);
		$before = did_action( 'outletpro_status_changed' );

		// Act.
		$first  = rest_do_request( $request );
		$second = rest_do_request( $request );

		// Assert.
		$this->assertSame( 200, $first->get_status() );
		$this->assertSame( $first->get_data(), $second->get_data() );
		$this->assertTrue( is_outlet( $product ) );
		$this->assertSame( $before + 1, did_action( 'outletpro_status_changed' ) );
	}

	public function test_bulk_remove_clears_membership(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$request = new WP_REST_Request( 'POST', '/outletpro/v1/products/batch' );
		$request->set_body_params(
			array(
				'ids'    => array( $product->get_id() ),
				'outlet' => false,
			)
		);

		// Act.
		$response = rest_do_request( $request );

		// Assert.
		$this->assertSame( 200, $response->get_status() );
		$this->assertFalse( is_outlet( $product ) );
	}

	public function test_reports_variation_failure_without_losing_successful_changes(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$parent  = WC_Helper_Product::create_variation_product();
		$request = new WP_REST_Request( 'POST', '/outletpro/v1/products/batch' );
		$request->set_body_params(
			array(
				'ids'    => array( $parent->get_id(), $parent->get_children()[0] ),
				'outlet' => true,
			)
		);

		// Act.
		$data = rest_do_request( $request )->get_data();

		// Assert.
		$this->assertTrue( $data['results'][0]['outlet'] );
		$this->assertArrayHasKey( 'error', $data['results'][1] );
		$this->assertTrue( is_outlet( $parent ) );
	}

	public function test_checks_product_editing_permission_per_item(): void {
		// Arrange.
		init_taxonomies();
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$user    = new WP_User( $user_id );
		$user->add_cap( 'edit_products' );
		wp_set_current_user( $user_id );
		$product = WC_Helper_Product::create_simple_product();
		wp_update_post(
			array(
				'ID'          => $product->get_id(),
				'post_author' => $this->factory->user->create(),
			)
		);
		$request = new WP_REST_Request( 'POST', '/outletpro/v1/products/batch' );
		$request->set_body_params(
			array(
				'ids'    => array( $product->get_id() ),
				'outlet' => true,
			)
		);

		// Act.
		$data = rest_do_request( $request )->get_data();

		// Assert.
		$this->assertSame( 'You cannot edit this product.', $data['results'][0]['error'] );
		$this->assertFalse( is_outlet( $product ) );
	}

	public function test_rejects_invalid_batch_before_mutating_products(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$product = WC_Helper_Product::create_simple_product();
		$request = new WP_REST_Request( 'POST', '/outletpro/v1/products/batch' );
		$request->set_body_params(
			array(
				'ids'    => array( $product->get_id(), -1 ),
				'outlet' => true,
			)
		);

		// Act.
		$response = rest_do_request( $request );

		// Assert.
		$this->assertSame( 400, $response->get_status() );
		$this->assertFalse( is_outlet( $product ) );
	}

	public function test_rejects_oversized_batches(): void {
		// Arrange.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$request = new WP_REST_Request( 'POST', '/outletpro/v1/products/batch' );
		$request->set_body_params(
			array(
				'ids'    => range( 1, 101 ),
				'outlet' => true,
			)
		);

		// Act.
		$response = rest_do_request( $request );

		// Assert.
		$this->assertSame( 400, $response->get_status() );
	}

	public function test_returns_per_item_errors_when_membership_storage_fails(): void {
		// Arrange.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$product = WC_Helper_Product::create_simple_product();
		deinit_taxonomies();
		$request = new WP_REST_Request( 'POST', '/outletpro/v1/products/batch' );
		$request->set_body_params(
			array(
				'ids'    => array( $product->get_id() ),
				'outlet' => true,
			)
		);

		// Act.
		$response = rest_do_request( $request );
		init_taxonomies();

		// Assert.
		$this->assertArrayHasKey( 'error', $response->get_data()['results'][0] );
	}
}
