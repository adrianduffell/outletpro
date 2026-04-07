/**
 * Checkout block slot fill for clearance badge.
 *
 * Registers an ExperimentalOrderMeta slot fill for the WooCommerce Cart and
 * Checkout blocks. Renders the clearance badge in the order summary when any
 * cart item has the clearance extension flag set.
 *
 * @since 1.0.0
 */

( function () {
	const registerPlugin = window.wp?.plugins?.registerPlugin;
	const createElement = window.wp?.element?.createElement;
	const useSelect = window.wp?.data?.useSelect;
	const __ = window.wp?.i18n?.__ ?? ( ( text ) => text );

	if ( ! registerPlugin || ! createElement || ! useSelect ) {
		return;
	}

	function ClearanceBadgeForOrder() {
		const cart = useSelect( function ( select ) {
			return select( 'wc/store/cart' ).getCartData();
		} );

		const hasClearanceItems = ( cart?.items ?? [] ).some(
			function ( item ) {
				return item.extensions?.[ 'wc-clearance' ]?.is_clearance;
			}
		);

		if ( ! hasClearanceItems ) {
			return null;
		}

		return createElement(
			'p',
			{ className: 'wc-clearance-badge-container' },
			createElement(
				'span',
				{ className: 'wc-clearance-badge' },
				__( 'Clearance', 'wc-clearance' )
			)
		);
	}

	function OrderMetaFill() {
		const ExperimentalOrderMeta =
			window.wc?.blocksCheckout?.ExperimentalOrderMeta;

		if ( ! ExperimentalOrderMeta ) {
			return null;
		}

		return createElement(
			ExperimentalOrderMeta,
			null,
			createElement( ClearanceBadgeForOrder, null )
		);
	}

	registerPlugin( 'wc-clearance-order-meta', {
		render: OrderMetaFill,
		scope: 'woocommerce-checkout',
	} );
} )();
