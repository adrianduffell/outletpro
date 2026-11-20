<?php
/**
 * Outlet manager REST endpoints.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Register authenticated product management endpoints.
 *
 * Fired by `rest_api_init`.
 *
 * @internal WordPress action hook
 */
function register_outlet_manager_routes_hook(): void {
	register_rest_route(
		'outletpro/v1',
		'/products',
		array(
			'methods'             => 'GET',
			'permission_callback' => 'OutletPro\\can_manage_outlet_products',
			'callback'            => 'OutletPro\\get_manager_products',
			'args'                => array(
				'page'       => array(
					'type'    => 'integer',
					'minimum' => 1,
					'default' => 1,
				),
				'per_page'   => array(
					'type'    => 'integer',
					'minimum' => 1,
					'maximum' => 100,
					'default' => 25,
				),
				'search'     => array(
					'type'              => 'string',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'category'   => array(
					'type'    => 'integer',
					'minimum' => 1,
				),
				'membership' => array(
					'type'    => 'string',
					'enum'    => array( 'outlet', 'all' ),
					'default' => 'outlet',
				),
				'order'      => array(
					'type'    => 'string',
					'enum'    => array( 'asc', 'desc' ),
					'default' => 'asc',
				),
			),
		)
	);
	register_rest_route(
		'outletpro/v1',
		'/products/batch',
		array(
			'methods'             => 'POST',
			'permission_callback' => 'OutletPro\\can_manage_outlet_products',
			'callback'            => 'OutletPro\\update_manager_products',
			'args'                => array(
				'ids'    => array(
					'type'        => 'array',
					'required'    => true,
					'minItems'    => 1,
					'maxItems'    => 100,
					'uniqueItems' => true,
					'items'       => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
				),
				'outlet' => array(
					'type'     => 'boolean',
					'required' => true,
				),
			),
		)
	);
}

/**
 * Check product management access independently of page editing access.
 *
 * @internal REST permission callback
 */
function can_manage_outlet_products(): bool {
	return current_user_can( 'edit_products' );
}

/**
 * Retrieve a page of products using WooCommerce's catalog search.
 *
 * @param \WP_REST_Request $request Validated table query.
 * @throws \RuntimeException If a catalog product disappears while preparing the response; handled at this request boundary.
 * @internal REST callback
 */
function get_manager_products( \WP_REST_Request $request ): \WP_REST_Response {
	try {
		$query = new \WP_REST_Request( 'GET', '/wc/v3/products' );
		$query->set_query_params(
			array(
				'context'            => 'edit',
				'page'               => $request['page'],
				'per_page'           => $request['per_page'],
				'search_name_or_sku' => $request['search'],
				'orderby'            => 'title',
				'order'              => $request['order'],
				'outletpro'          => 'outlet' === $request['membership'],
				'_fields'            => 'id,name,sku,price_html,stock_status,manage_stock,stock_quantity,total_sales,status,categories',
			)
		);
		if ( isset( $request['category'] ) ) {
			// WooCommerce requires a CSV string even for a single validated integer category ID.
			$query->set_param( 'category', sprintf( '%d', $request['category'] ) );
		}
		$response = rest_do_request( $query );
		if ( $response->is_error() ) {
			if ( $response->get_status() >= 500 ) {
				wc_get_logger()->error( 'Outlet manager products could not be retrieved.' );
			}
			return $response;
		}
		$count_query = new \WP_REST_Request( 'GET', '/wc/v3/products' );
		$count_query->set_query_params(
			array(
				'context'   => 'edit',
				'page'      => 1,
				'per_page'  => 1,
				'outletpro' => true,
				'_fields'   => 'id',
			)
		);
		$count_response = rest_do_request( $count_query );
		if ( $count_response->is_error() ) {
			wc_get_logger()->error( 'Outlet product count could not be retrieved.' );
			return $count_response;
		}
		$items = array();
		$now   = time();
		foreach ( $response->get_data() as $item ) {
			$product = wc_get_product( $item['id'] );
			if ( ! $product instanceof \WC_Product ) {
				throw new \RuntimeException( 'Outlet manager product could not be retrieved.' );
			}
			$image            = wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_thumbnail' );
			$item['image']    = false === $image ? wc_placeholder_img_src( 'woocommerce_thumbnail' ) : $image;
			$created          = $product->get_date_created();
			$item['age_days'] = null === $created ? null : intdiv( max( 0, $now - $created->getTimestamp() ), DAY_IN_SECONDS );
			$item['price']    = wp_kses_post( $item['price_html'] );
			$item['outlet']   = is_outlet( $product );
			$item['canEdit']  = current_user_can( 'edit_post', $item['id'] );
			$item['editUrl']  = $item['canEdit'] ? get_edit_post_link( $item['id'], 'raw' ) : null;
			unset( $item['price_html'] );
			$items[] = $item;
		}
		$headers = $response->get_headers();
		return new \WP_REST_Response(
			array(
				'items'            => $items,
				'totalItems'       => $headers['X-WP-Total'],
				'totalPages'       => $headers['X-WP-TotalPages'],
				'totalOutletItems' => $count_response->get_headers()['X-WP-Total'],
			)
		);
	} catch ( \Throwable $e ) {
		wc_get_logger()->error( 'Outlet manager products could not be retrieved.' );
		return rest_convert_error_to_response( new \WP_Error( 'outletpro_products_failed', __( 'Products could not be loaded. Please try again.', 'outletpro' ), array( 'status' => 500 ) ) );
	}
}

/**
 * Apply membership changes, reporting failures per product.
 *
 * @param \WP_REST_Request $request Validated IDs and membership.
 * @internal REST callback
 */
function update_manager_products( \WP_REST_Request $request ): \WP_REST_Response {
	$results = array();
	foreach ( $request['ids'] as $id ) {
		$results[] = update_manager_product( $id, $request['outlet'] );
	}
	return new \WP_REST_Response( array( 'results' => $results ) );
}

/**
 * Update one editable parent product through the existing membership API.
 *
 * @param int  $id Product ID.
 * @param bool $outlet Desired membership.
 * @return array<string, mixed> Product result.
 * @internal
 */
function update_manager_product( int $id, bool $outlet ): array {
	if ( ! current_user_can( 'edit_post', $id ) ) {
		return array(
			'id'    => $id,
			'error' => __( 'You cannot edit this product.', 'outletpro' ),
		);
	}
	try {
		$product = wc_get_product( $id );
		if ( ! $product instanceof \WC_Product ) {
			return array(
				'id'    => $id,
				'error' => __( 'Product no longer exists.', 'outletpro' ),
			);
		}
		if ( $product->is_type( 'variation' ) ) {
			return array(
				'id'    => $id,
				'error' => __( 'Select the parent product to change outlet membership.', 'outletpro' ),
			);
		}
		if ( 'trash' === $product->get_status() ) {
			return array(
				'id'    => $id,
				'error' => __( 'Restore this product before changing outlet membership.', 'outletpro' ),
			);
		}
		set_outlet( $product, $outlet );
		return array(
			'id'     => $id,
			'outlet' => is_outlet( $product ),
		);
	} catch ( \Throwable $e ) {
		wc_get_logger()->error( 'Outlet product membership could not be updated.', array( 'product_id' => $id ) );
		return array(
			'id'    => $id,
			'error' => __( 'Outlet membership could not be updated. Please refresh and try again.', 'outletpro' ),
		);
	}
}
