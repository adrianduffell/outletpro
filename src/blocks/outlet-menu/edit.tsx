/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import { useEffect, useMemo } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { decodeEntities } from '@wordpress/html-entities';
import { store as outletStore } from '../../data';
import { __ } from '@wordpress/i18n';
import type { CSSProperties } from 'react';

type MenuItem = { id: number; name: string };

type EditProps = {
	attributes: {
		items?: number[];
		showAll?: boolean;
		style?: {
			typography?: { textDecoration?: CSSProperties[ 'textDecoration' ] };
		};
	};
	setAttributes: ( attributes: {
		items?: number[];
		showAll?: boolean;
	} ) => void;
	__unstableLayoutClassNames?: string;
};

export function Edit( {
	attributes,
	setAttributes,
	__unstableLayoutClassNames,
}: EditProps ): JSX.Element {
	const { categories, prices, discounts } = useSelect(
		( select ) => ( {
			categories: select( outletStore ).getProductCategories(),
			prices: select( outletStore ).getPriceFacets(),
			discounts: select( outletStore ).getDiscountFacets(),
		} ),
		[]
	);
	const categoryItems = useMemo(
		() =>
			categories.map( ( { id, name } ) => ( {
				id,
				name: decodeEntities( name ),
			} ) ),
		[ categories ]
	);
	const availableItems = useMemo(
		() => [ ...categoryItems, ...prices, ...discounts ],
		[ categoryItems, prices, discounts ]
	);
	const items = attributes.items ?? availableItems.map( ( item ) => item.id );
	const showAll =
		attributes.items === undefined
			? availableItems.length > 0
			: ( attributes.showAll ?? false );
	const selectedItems = items
		.map( ( id ) => availableItems.find( ( item ) => item.id === id ) )
		.filter( ( item ): item is MenuItem => item !== undefined );
	const linkStyle = {
		textDecoration: attributes.style?.typography?.textDecoration,
	};
	const blockProps = useBlockProps( {
		className: __unstableLayoutClassNames,
		style: { listStyleType: 'none' },
		onClick: ( event: React.MouseEvent ) => event.preventDefault(),
	} );

	useEffect( () => {
		const orderedItems = availableItems
			.filter( ( item ) => items.includes( item.id ) )
			.map( ( item ) => item.id );
		if (
			JSON.stringify( attributes.items ) ===
			JSON.stringify( orderedItems )
		) {
			return;
		}
		setAttributes( {
			items: orderedItems,
			...( attributes.items === undefined ? { showAll: true } : {} ),
		} );
	}, [ attributes.items, availableItems, setAttributes, items ] );

	return (
		<>
			<InspectorControls group="content">
				{ [
					{
						title: __( 'Categories', 'outletpro' ),
						showAll: true,
						items: categoryItems,
					},
					{
						title: __( 'Price tiers', 'outletpro' ),
						items: prices,
					},
					{
						title: __( 'Discount tiers', 'outletpro' ),
						items: discounts,
					},
				].map( ( group ) => (
					<PanelBody
						key={ group.title }
						title={ group.title }
						initialOpen
					>
						<div style={ { display: 'grid', gap: '12px' } }>
							{ group.showAll && (
								<ToggleControl
									label={ __( 'All', 'outletpro' ) }
									checked={ showAll }
									onChange={ ( checked ) =>
										setAttributes( { showAll: checked } )
									}
									__nextHasNoMarginBottom
								/>
							) }
							{ group.items.map( ( item ) => (
								<ToggleControl
									key={ item.id }
									label={ item.name }
									checked={ items.includes( item.id ) }
									onChange={ ( checked ) =>
										setAttributes( {
											items: checked
												? [ ...items, item.id ]
												: items.filter(
														( id ) => id !== item.id
													),
										} )
									}
									__nextHasNoMarginBottom
								/>
							) ) }
						</div>
					</PanelBody>
				) ) }
			</InspectorControls>
			<menu { ...blockProps }>
				{ showAll && (
					<li>
						<a style={ linkStyle } href={ window.location.href }>
							{ __( 'All', 'outletpro' ) }
						</a>
					</li>
				) }
				{ selectedItems.map( ( { id, name } ) => (
					<li key={ id }>
						<a href={ window.location.href } style={ linkStyle }>
							{ name }
						</a>
					</li>
				) ) }
			</menu>
		</>
	);
}
