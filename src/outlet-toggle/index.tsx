import type { ComponentType } from 'react';
import { Fragment } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

type ProductCollectionAttributes = {
	collection?: string;
	query?: Record< string, unknown >;
};

type ProductCollectionEditProps = {
	name: string;
	attributes: ProductCollectionAttributes;
	setAttributes: (
		attributes: Partial< ProductCollectionAttributes >
	) => void;
};

const PRODUCT_COLLECTION_BLOCK = 'woocommerce/product-collection';
const OUTLET_PRODUCT_COLLECTION = 'wc-outlet/product-collection/outlet';

function updateOutletQueryAttributes(
	attributes: ProductCollectionAttributes,
	isChecked: boolean
): Partial< ProductCollectionAttributes > {
	const nextQuery = { ...( attributes.query ?? {} ) };

	if ( isChecked ) {
		nextQuery.wc_outlet = true;
	} else {
		delete nextQuery.wc_outlet;
	}

	return {
		query: nextQuery,
	};
}

export const withOutletQueryInspector = (
	BlockEdit: ComponentType< ProductCollectionEditProps >
) =>
	function OutletQueryInspector( props: ProductCollectionEditProps ) {
		if ( PRODUCT_COLLECTION_BLOCK !== props.name ) {
			return <BlockEdit { ...props } />;
		}

		if ( OUTLET_PRODUCT_COLLECTION === props.attributes.collection ) {
			return <BlockEdit { ...props } />;
		}

		return (
			<Fragment>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody title={ __( 'Outlet', 'wc-outlet' ) }>
						<ToggleControl
							label={ __(
								'Show only outlet products',
								'wc-outlet'
							) }
							checked={
								true === props.attributes.query?.wc_outlet
							}
							onChange={ ( isChecked ) =>
								props.setAttributes(
									updateOutletQueryAttributes(
										props.attributes,
										isChecked
									)
								)
							}
						/>
					</PanelBody>
				</InspectorControls>
			</Fragment>
		);
	};

addFilter(
	'editor.BlockEdit',
	'wc-outlet/product-collection/outlet-query-inspector',
	withOutletQueryInspector
);
