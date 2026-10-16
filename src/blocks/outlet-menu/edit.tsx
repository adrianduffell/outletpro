/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import { useEffect, useMemo } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { useEntityRecords } from '@wordpress/core-data';
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

const EMPTY_ITEMS: MenuItem[] = [];

export function Edit( {
	attributes,
	setAttributes,
	__unstableLayoutClassNames,
}: EditProps ): JSX.Element {
	const { records: categories, hasResolved } = useEntityRecords< MenuItem >(
		'taxonomy',
		'product_cat',
		{
			parent: 0,
			hide_empty: false,
			per_page: -1,
			orderby: 'name',
			order: 'asc',
		}
	);
	const { prices, discounts } = useSelect(
		( select ) => ( {
			prices: select( outletStore ).getPriceFacets(),
			discounts: select( outletStore ).getDiscountFacets(),
		} ),
		[]
	);
	const categoryItems = useMemo(
		() =>
			categories?.map( ( { id, name } ) => ( {
				id,
				name: decodeEntities( name ),
			} ) ) ?? EMPTY_ITEMS,
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
		if ( attributes.items !== undefined ) {
			return;
		}
		if ( ! hasResolved ) {
			return;
		}
		if ( categories === null ) {
			return;
		}
		setAttributes( {
			items: availableItems.map( ( item ) => item.id ),
			showAll: true,
		} );
	}, [
		attributes.items,
		availableItems,
		categories,
		hasResolved,
		setAttributes,
	] );

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
