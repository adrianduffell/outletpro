import type { ComponentType } from 'react';
import { Fragment } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
import { ToggleControl } from '@wordpress/components';
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

		return (
			<Fragment>
				<BlockEdit { ...props } />
				<InspectorControls group="advanced">
					<ToggleControl
						label={ __( 'Show outlet products only', 'outletpro' ) }
						help={ __(
							'Restrict this collection to products in the store’s outlet.',
							'outletpro'
						) }
						checked={ true === props.attributes.query?.wc_outlet }
						onChange={ ( isChecked ) =>
							props.setAttributes(
								updateOutletQueryAttributes(
									props.attributes,
									isChecked
								)
							)
						}
					/>
				</InspectorControls>
			</Fragment>
		);
	};

addFilter(
	'editor.BlockEdit',
	'wc-outlet/product-collection/outlet-query-inspector',
	withOutletQueryInspector
);
