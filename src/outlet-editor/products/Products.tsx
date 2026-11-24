/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { Notice, Panel, PanelBody, ToggleControl } from '@wordpress/components';
import { DataViews } from '@wordpress/dataviews/wp';
import type { View } from '@wordpress/dataviews/wp';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { getProductFields } from './fields';
import type { ProductQuery } from '../../data/products/product-entity';
import useProducts from '../../data/products/useProducts';
import './style.css';
import { getProductRow } from './presentation';

export default function Products() {
	const [ outletOnly, setOutletOnly ] = useState( false );
	const [ view, setView ] = useState< View >( {
		type: 'table',
		page: 1,
		perPage: 25,
		search: '',
		fields: [
			'image',
			'name',
			'sku',
			'total_sales',
			'age_days',
			'price',
			'stock_status',
			'outlet',
		],
		layout: {
			styles: {
				image: {
					width: 48,
					minWidth: 48,
					maxWidth: 48,
					align: 'center',
				},
				name: { align: 'start' },
				sku: { align: 'center' },
				total_sales: { align: 'center' },
				age_days: { align: 'center' },
				price: { align: 'center' },
				stock_status: { align: 'center' },
				outlet: { align: 'center' },
			},
		},
		sort: { field: 'name', direction: 'asc' },
	} );
	const query = useMemo( () => {
		const params: ProductQuery = {
			page: view.page ?? 1,
			per_page: view.perPage ?? 25,
			search_name_or_sku: view.search ?? '',
			orderby: 'title',
			order: view.sort?.direction ?? 'asc',
		};
		if ( outletOnly ) {
			params.outletpro = true;
		}
		return params;
	}, [ view, outletOnly ] );
	const { records, permissions, totalItems, loading, error } =
		useProducts( query );
	const totalPages = Math.ceil(
		( totalItems ?? 0 ) / ( view.perPage ?? 25 )
	);
	const fields = useMemo( getProductFields, [] );

	useEffect( () => {
		if ( totalItems === null ) {
			return;
		}
		if ( ( view.page ?? 1 ) > Math.max( 1, totalPages ) ) {
			setView( ( current ) => ( {
				...current,
				page: Math.max( 1, totalPages ),
			} ) );
		}
	}, [ totalItems, totalPages, view.page ] );

	if ( error ) {
		return (
			<Notice status="error" isDismissible={ false }>
				{ __( 'Products could not be loaded.', 'outletpro' ) }
			</Notice>
		);
	}

	return (
		<section
			aria-label={ __( 'Outlet products', 'outletpro' ) }
			className="outletpro-editor-products"
		>
			<Panel className="outletpro-editor-products-panel">
				<PanelBody
					title={ __( 'Manage outlet products', 'outletpro' ) }
					initialOpen={ true }
					scrollAfterOpen={ false }
				>
					{ ( { opened } ) => (
						<div
							className="outletpro-editor-products-content"
							hidden={ ! opened }
						>
							<p className="outletpro-editor-help">
								{ __(
									'Select the products to display on the outlet page.',
									'outletpro'
								) }
							</p>

							<div className="outletpro-editor-product-browser">
								<DataViews
									data={ ( records ?? [] ).map( ( record ) =>
										getProductRow(
											record,
											permissions[ record.id ]
										)
									) }
									fields={ fields }
									getItemId={ ( item ) => item.id.toString() }
									view={ view }
									onChangeView={ setView }
									isLoading={ loading }
									paginationInfo={ {
										totalItems: totalItems ?? 0,
										totalPages,
									} }
									defaultLayouts={ { table: {} } }
									config={ {
										perPageSizes: [ 10, 25, 50, 100 ],
									} }
									empty={ __(
										'No products found',
										'outletpro'
									) }
								>
									<div
										className="outletpro-editor-product-controls"
										role="group"
										aria-label={ __(
											'Product controls',
											'outletpro'
										) }
									>
										<div className="outletpro-editor-product-search">
											<DataViews.Search
												label={ __(
													'Search products by name or SKU',
													'outletpro'
												) }
											/>
										</div>
										<div className="outletpro-editor-membership-filter">
											<ToggleControl
												label={ __(
													'Show outlet products only',
													'outletpro'
												) }
												checked={ outletOnly }
												onChange={ ( enabled ) => {
													setOutletOnly( enabled );
													setView( ( current ) => ( {
														...current,
														page: 1,
													} ) );
												} }
												__nextHasNoMarginBottom
											/>
										</div>
										<div className="outletpro-editor-view-options">
											<DataViews.ViewConfig />
										</div>
									</div>
									<DataViews.Layout />
									<DataViews.Footer />
								</DataViews>
							</div>
						</div>
					) }
				</PanelBody>
			</Panel>
		</section>
	);
}
