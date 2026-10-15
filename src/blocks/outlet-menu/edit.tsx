/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import { useEffect, useMemo } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';

type MenuItem = { id: number; name: string; query: string };

type EditProps = {
	attributes: { items?: number[]; includeAll?: boolean };
	setAttributes: ( attributes: {
		items?: number[];
		includeAll?: boolean;
	} ) => void;
	__unstableLayoutClassNames?: string;
};

type MenuItemGroups = {
	categories: MenuItem[];
	prices: MenuItem[];
	discounts: MenuItem[];
};

const EMPTY_ITEMS: MenuItemGroups = {
	categories: [],
	prices: [],
	discounts: [],
};

export function Edit( {
	attributes,
	setAttributes,
	__unstableLayoutClassNames,
}: EditProps ): JSX.Element {
	const menuItems: MenuItemGroups = useSelect( ( select ) => {
		const settings = select( editorStore ).getEditorSettings();
		if ( ! ( 'outletproMenuItems' in settings ) ) {
			return EMPTY_ITEMS;
		}
		const groups = settings.outletproMenuItems;
		if ( typeof groups !== 'object' ) {
			return EMPTY_ITEMS;
		}
		if ( groups === null ) {
			return EMPTY_ITEMS;
		}
		return {
			categories:
				'categories' in groups && Array.isArray( groups.categories )
					? groups.categories
					: EMPTY_ITEMS.categories, // Keep original array reference to prevent re-render.
			prices:
				'prices' in groups && Array.isArray( groups.prices )
					? groups.prices
					: EMPTY_ITEMS.prices, // Keep original array reference to prevent re-render.
			discounts:
				'discounts' in groups && Array.isArray( groups.discounts )
					? groups.discounts
					: EMPTY_ITEMS.discounts, // Keep original array reference to prevent re-render.
		};
	}, [] );
	const permalink = useSelect( ( select ) => {
		const settings = select( editorStore ).getEditorSettings();
		return 'outletproMenuPermalink' in settings &&
			typeof settings.outletproMenuPermalink === 'string'
			? settings.outletproMenuPermalink
			: '';
	}, [] );
	const availableItems = useMemo(
		() => Object.values( menuItems ).flat(),
		[ menuItems ]
	);
	const items = attributes.items ?? availableItems.map( ( item ) => item.id );
	const includeAll =
		attributes.items === undefined
			? availableItems.length > 0
			: ( attributes.includeAll ?? false );
	const selectedItems = items
		.map( ( id ) => availableItems.find( ( item ) => item.id === id ) )
		.filter( ( item ): item is MenuItem => item !== undefined );
	const blockProps = useBlockProps( {
		className: __unstableLayoutClassNames,
		onClick: ( event: React.MouseEvent ) => event.preventDefault(),
	} );

	useEffect( () => {
		if ( attributes.items === undefined ) {
			if ( availableItems.length > 0 ) {
				setAttributes( {
					items: availableItems.map( ( item ) => item.id ),
					includeAll: true,
				} );
			}
		}
	}, [ attributes.items, availableItems, setAttributes ] );

	return (
		<>
			<InspectorControls group="content">
				{ [
					{
						title: __( 'Categories', 'outletpro' ),
						includeAll: true,
						items: menuItems.categories,
					},
					{
						title: __( 'Price tiers', 'outletpro' ),
						items: menuItems.prices,
					},
					{
						title: __( 'Discount tiers', 'outletpro' ),
						items: menuItems.discounts,
					},
				].map( ( group ) => (
					<PanelBody
						key={ group.title }
						title={ group.title }
						initialOpen
					>
						<div style={ { display: 'grid', gap: '12px' } }>
							{ group.includeAll && permalink !== '' && (
								<ToggleControl
									label={ __( 'All', 'outletpro' ) }
									checked={ includeAll }
									onChange={ ( checked ) =>
										setAttributes( { includeAll: checked } )
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
				{ includeAll && (
					<li>
						<a
							href={
								new URL( permalink, window.location.href ).href
							}
						>
							{ __( 'All', 'outletpro' ) }
						</a>
					</li>
				) }
				{ selectedItems.map( ( { id, name, query } ) => {
					const url = new URL( permalink, window.location.href );
					new URLSearchParams( query ).forEach( ( value, key ) =>
						url.searchParams.set( key, value )
					);

					return (
						<li key={ id }>
							<a href={ url.href }>{ name }</a>
						</li>
					);
				} ) }
			</menu>
		</>
	);
}
