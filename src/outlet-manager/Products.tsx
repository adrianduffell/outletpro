/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import apiFetch from '@wordpress/api-fetch';
import {
	Button,
	Notice,
	Panel,
	PanelBody,
	ToggleControl,
} from '@wordpress/components';
import { DataViews } from '@wordpress/dataviews/wp';
import type { View } from '@wordpress/dataviews/wp';
import { useEffect, useMemo, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { getProductFields } from './fields';
import { OutletControlsContext } from './OutletToggle';
import {
	getErrorMessage,
	getNextView,
	getProductsPath,
	INITIAL_VIEW,
} from './query';
import type { ManagerConfig, ProductsResponse } from './types';
import type useProductChanges from './useProductChanges';

export default function Products( {
	categories,
	changes,
	disabled = false,
}: Pick< ManagerConfig, 'categories' > & {
	changes: ReturnType< typeof useProductChanges >;
	disabled?: boolean;
} ) {
	const [ membership, setMembership ] = useState( 'all' );
	const [ view, setView ] = useState< View >( INITIAL_VIEW );
	const [ response, setResponse ] = useState< ProductsResponse | null >(
		null
	);
	const [ error, setError ] = useState( '' );
	const [ revision, setRevision ] = useState( 0 );
	const [ responseRevision, setResponseRevision ] = useState( -1 );
	const requestedPath = useRef( '' );
	const path = getProductsPath( view, membership );
	const fields = useMemo(
		() => getProductFields( categories ),
		[ categories ]
	);

	useEffect( () => {
		const controller = new AbortController();
		if ( requestedPath.current !== path ) {
			setResponse( null );
			requestedPath.current = path;
		}
		setError( '' );
		apiFetch< ProductsResponse >( { path, signal: controller.signal } )
			.then( ( result ) => {
				if ( controller.signal.aborted ) {
					return;
				}
				if ( ( view.page ?? 1 ) > Math.max( 1, result.totalPages ) ) {
					setView( ( current ) => ( {
						...current,
						page: Math.max( 1, result.totalPages ),
					} ) );
					return;
				}
				setResponse( result );
				setResponseRevision( changes.revision );
			} )
			.catch( ( failure: unknown ) => {
				if ( controller.signal.aborted ) {
					return;
				}
				// eslint-disable-next-line no-console -- Log system failures at the API boundary.
				console.error(
					'Outlet products could not be loaded.',
					failure
				);
				setError(
					getErrorMessage(
						failure,
						__( 'Products could not be loaded.', 'outletpro' )
					)
				);
			} );
		return () => controller.abort();
	}, [ path, revision, changes.revision, view.page ] );

	const items = useMemo( () => {
		const pending = new Map(
			[
				...( responseRevision === changes.revision
					? []
					: changes.committed ),
				...changes.changes,
			].map( ( change ) => [ change.id, change.outlet ] )
		);
		return ( response?.items ?? [] ).map( ( item ) => ( {
			...item,
			outlet: pending.get( item.id ) ?? item.outlet,
			canEdit: item.canEdit && ! disabled,
		} ) );
	}, [
		response,
		responseRevision,
		changes.changes,
		changes.committed,
		changes.revision,
		disabled,
	] );

	return (
		<section
			aria-label={ __( 'Outlet products', 'outletpro' ) }
			className="outletpro-manager-products"
		>
			<Panel className="outletpro-manager-products-panel">
				<PanelBody
					title={ __( 'Manage outlet products', 'outletpro' ) }
					initialOpen={ true }
					scrollAfterOpen={ false }
				>
					{ ( { opened } ) => (
						<div
							className="outletpro-manager-products-content"
							hidden={ ! opened }
						>
							<p className="outletpro-manager-help">
								{ __(
									'Select the products to display on the outlet page.',
									'outletpro'
								) }
							</p>
							{ error && (
								<Notice status="error" isDismissible={ false }>
									{ error }
								</Notice>
							) }
							<div className="outletpro-manager-product-browser">
								<DataViews
									data={ items }
									fields={ fields }
									getItemId={ ( item ) => item.id.toString() }
									view={ view }
									onChangeView={ ( next ) => {
										setView( getNextView( view, next ) );
									} }
									isLoading={ response === null && ! error }
									paginationInfo={ {
										totalItems: response?.totalItems ?? 0,
										totalPages: response?.totalPages ?? 0,
									} }
									defaultLayouts={ { table: {} } }
									config={ {
										perPageSizes: [ 10, 25, 50, 100 ],
									} }
									empty={
										error ? (
											<Button
												variant="secondary"
												onClick={ () =>
													setRevision(
														( current ) =>
															current + 1
													)
												}
											>
												{ __( 'Retry', 'outletpro' ) }
											</Button>
										) : (
											__(
												'No products found',
												'outletpro'
											)
										)
									}
								>
									<div
										className="outletpro-manager-product-controls"
										role="group"
										aria-label={ __(
											'Product controls',
											'outletpro'
										) }
									>
										<div className="outletpro-manager-product-search">
											<DataViews.Search
												label={ __(
													'Search products by name or SKU',
													'outletpro'
												) }
											/>
										</div>
										<div className="outletpro-manager-membership-filter">
											<ToggleControl
												label={ __(
													'Show outlet products only',
													'outletpro'
												) }
												checked={
													membership === 'outlet'
												}
												onChange={ ( enabled ) => {
													setMembership(
														enabled
															? 'outlet'
															: 'all'
													);
													setView( ( current ) => ( {
														...current,
														page: 1,
													} ) );
												} }
												__nextHasNoMarginBottom
											/>
										</div>
										<div className="outletpro-manager-view-options">
											<DataViews.ViewConfig />
										</div>
									</div>
									<OutletControlsContext.Provider
										value={ changes.stage }
									>
										<DataViews.Layout />
									</OutletControlsContext.Provider>
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
