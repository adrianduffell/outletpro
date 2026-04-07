import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';

declare const wc: {
	blocksCheckout?: {
		ExperimentalOrderMeta?: React.ComponentType< {
			children?: React.ReactNode;
		} >;
	};
};

interface ClearanceExtensionData {
	is_clearance: boolean;
}

interface CartItem {
	id: number;
	name: string;
	extensions: {
		'wc-clearance'?: ClearanceExtensionData;
	};
}

interface Cart {
	items: CartItem[];
}

interface ClearanceBadgeForOrderProps {
	cart?: Cart;
}

const DEFAULT_CART: Cart = { items: [] };

export function ClearanceBadgeForOrder( {
	cart = DEFAULT_CART,
}: ClearanceBadgeForOrderProps ): JSX.Element | null {
	const hasClearanceItems = cart.items.some(
		( item ) => item.extensions?.[ 'wc-clearance' ]?.is_clearance
	);

	if ( ! hasClearanceItems ) {
		return null;
	}

	return (
		<p className="wc-clearance-badge-container">
			<span className="wc-clearance-badge">
				{ __( 'Clearance', 'wc-clearance' ) }
			</span>
		</p>
	);
}

export function OrderMetaFill(): JSX.Element | null {
	if ( ! wc?.blocksCheckout?.ExperimentalOrderMeta ) {
		return null;
	}

	const { ExperimentalOrderMeta } = wc.blocksCheckout;

	return (
		<ExperimentalOrderMeta>
			<ClearanceBadgeForOrder />
		</ExperimentalOrderMeta>
	);
}

registerPlugin( 'wc-clearance-order-meta', {
	render: OrderMetaFill,
	scope: 'woocommerce-checkout',
} );
