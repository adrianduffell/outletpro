/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl } from '@wordpress/components';
import { useEntityProp, useEntityRecords } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { decodeEntities } from '@wordpress/html-entities';
import { __ } from '@wordpress/i18n';
import { store as outletStore } from '../../data';

type Attributes = {
	category: number;
	displayOutletBadgeLabel: boolean;
};

type EditProps = {
	attributes: Attributes;
	setAttributes: ( attributes: Partial< Attributes > ) => void;
	__unstableLayoutClassNames?: string;
};

type TileItem = { id: number; name: string };

const EMPTY_ITEMS: TileItem[] = [];

export function Edit( {
	attributes: { category, displayOutletBadgeLabel },
	setAttributes,
	__unstableLayoutClassNames,
}: EditProps ): JSX.Element {
	const { records: categories } = useEntityRecords< TileItem >(
		'taxonomy',
		'product_cat',
		{ per_page: -1, parent: 0, orderby: 'name', order: 'asc' }
	);
	const facets = useSelect(
		( select ) => ( {
			discounts: select( outletStore ).getDiscountFacets(),
			prices: select( outletStore ).getPriceFacets(),
		} ),
		[]
	);
	const groups = [
		{
			label: __( 'Product categories', 'outletpro' ),
			items: categories ?? EMPTY_ITEMS,
		},
		{ label: __( 'Discount tiers', 'outletpro' ), items: facets.discounts },
		{ label: __( 'Price tiers', 'outletpro' ), items: facets.prices },
	];
	const items = groups.flatMap( ( group ) => group.items );
	const placeholder = __( 'Select category', 'outletpro' );
	const [ badgeLabel ] = useEntityProp(
		'root',
		'site',
		'outletpro_badge_label'
	);
	const blockProps = useBlockProps( {
		className: __unstableLayoutClassNames,
		href: window.location.href,
		onClick: ( event: React.MouseEvent< HTMLAnchorElement > ) =>
			event.preventDefault(),
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody>
					<SelectControl
						label={ __( 'Link to', 'outletpro' ) }
						value={ category.toString() }
						onChange={ ( value ) => {
							const selectedItem = items.find(
								( option ) => option.id.toString() === value
							);
							if ( selectedItem === undefined ) {
								throw new Error(
									'Unknown outlet tile item selected.'
								);
							}
							setAttributes( { category: selectedItem.id } );
						} }
						__nextHasNoMarginBottom
					>
						<option value="0" disabled>
							{ placeholder }
						</option>
						{ groups.map( ( group ) => (
							<optgroup key={ group.label } label={ group.label }>
								{ group.items.map( ( { id, name } ) => (
									<option key={ id } value={ id.toString() }>
										{ decodeEntities( name ) }
									</option>
								) ) }
							</optgroup>
						) ) }
					</SelectControl>
					<ToggleControl
						label={ __(
							'Display outlet badge label',
							'outletpro'
						) }
						checked={ displayOutletBadgeLabel }
						onChange={ ( value: boolean ) =>
							setAttributes( { displayOutletBadgeLabel: value } )
						}
						__nextHasNoMarginBottom
					/>
				</PanelBody>
			</InspectorControls>
			<a { ...blockProps }>
				<div className="outletpro-tile-text">
					{ decodeEntities(
						items.find( ( option ) => option.id === category )
							?.name ?? placeholder
					) }
				</div>
				{ displayOutletBadgeLabel &&
				typeof badgeLabel === 'string' &&
				badgeLabel ? (
					<div
						className="outletpro-tile-tagline"
						style={ { fontSize: '0.75em' } }
					>
						{ decodeEntities( badgeLabel ) }
					</div>
				) : null }
			</a>
		</>
	);
}
