/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { Button, CheckboxControl } from '@wordpress/components';
import { useEffect, useRef, useState } from '@wordpress/element';
import { decodeEntities } from '@wordpress/html-entities';
import { __, _n, sprintf } from '@wordpress/i18n';
import type useOutletPage from './useOutletPage';
import type { ProductChange } from './useProductChanges';

export default function ReviewChanges( {
	details,
	changes,
	status,
	busy,
	onSave,
	onClose,
}: {
	details: ReturnType< typeof useOutletPage >;
	changes: ProductChange[];
	status?: 'publish' | 'draft';
	busy: boolean;
	onSave: ( productIds: number[], includePage: boolean ) => void;
	onClose: () => void;
} ) {
	const sidebar = useRef< HTMLElement >( null );
	const cancelButton = useRef< HTMLButtonElement >( null );
	const [ excludedProducts, setExcludedProducts ] = useState< number[] >(
		[]
	);
	const [ includePage, setIncludePage ] = useState( true );
	const selectedProducts = changes.filter(
		( change ) => ! excludedProducts.includes( change.id )
	);
	const pageChanged = details.dirty || status !== undefined;
	const changeCount = changes.length + ( pageChanged ? 1 : 0 );
	const selectedCount =
		selectedProducts.length + ( pageChanged && includePage ? 1 : 0 );
	useEffect( () => cancelButton.current?.focus(), [] );
	useEffect( () => {
		function closeOnEscape( event: KeyboardEvent ) {
			if ( event.key !== 'Escape' ) {
				return;
			}
			if ( busy ) {
				return;
			}
			if ( ! ( event.target instanceof Node ) ) {
				return;
			}
			if ( ! sidebar.current?.contains( event.target ) ) {
				return;
			}
			onClose();
		}
		window.addEventListener( 'keydown', closeOnEscape );
		return () => window.removeEventListener( 'keydown', closeOnEscape );
	}, [ busy, onClose ] );
	return (
		<aside
			ref={ sidebar }
			className="outletpro-manager-details outletpro-manager-review"
			aria-label={ __( 'Review changes', 'outletpro' ) }
		>
			<div className="outletpro-manager-review-actions">
				<Button
					ref={ cancelButton }
					variant="secondary"
					size="compact"
					disabled={ busy }
					onClick={ onClose }
				>
					{ __( 'Cancel', 'outletpro' ) }
				</Button>
				<Button
					variant="primary"
					size="compact"
					disabled={ busy || selectedCount === 0 }
					isBusy={ busy }
					onClick={ () =>
						onSave(
							selectedProducts.map( ( change ) => change.id ),
							includePage
						)
					}
				>
					{ status === 'publish' && includePage
						? __( 'Publish', 'outletpro' )
						: __( 'Save', 'outletpro' ) }
				</Button>
			</div>
			<div className="outletpro-manager-sidebar-panel">
				<div className="outletpro-manager-review-prompt">
					<strong>
						{ __( 'Are you ready to save?', 'outletpro' ) }
					</strong>
					<p>
						{ changeCount === 0
							? __( 'There are no changes to save.', 'outletpro' )
							: sprintf(
									// translators: %d: number of pending product and page changes.
									_n(
										'There is %d change waiting to be saved.',
										'There are %d changes waiting to be saved.',
										changeCount,
										'outletpro'
									),
									changeCount
								) }
					</p>
				</div>
				{ pageChanged && (
					<section
						className="outletpro-manager-review-page"
						aria-label={ __( 'Page changes', 'outletpro' ) }
					>
						<CheckboxControl
							label={ __( 'Page details', 'outletpro' ) }
							checked={ includePage }
							onChange={ setIncludePage }
							disabled={ busy }
							__nextHasNoMarginBottom
						/>
						<dl>
							{ details.title !== details.page?.title.raw && (
								<>
									<dt>{ __( 'Name', 'outletpro' ) }</dt>
									<dd>{ details.title }</dd>
								</>
							) }
							{ details.slug !== details.page?.slug && (
								<>
									<dt>{ __( 'Slug', 'outletpro' ) }</dt>
									<dd>{ details.slug }</dd>
								</>
							) }
						</dl>
						{ status !== undefined && (
							<p>
								{ status === 'publish'
									? __(
											'This page will be published.',
											'outletpro'
										)
									: __(
											'This page will be unpublished.',
											'outletpro'
										) }
							</p>
						) }
					</section>
				) }
				{ changes.length > 0 && (
					<section
						aria-label={ __( 'Product changes', 'outletpro' ) }
					>
						<ul className="outletpro-manager-review-products">
							{ changes.map( ( change ) => (
								<li key={ change.id }>
									<CheckboxControl
										label={ decodeEntities( change.name ) }
										help={
											change.outlet
												? __(
														'Include in outlet',
														'outletpro'
													)
												: __(
														'Remove from outlet',
														'outletpro'
													)
										}
										checked={
											! excludedProducts.includes(
												change.id
											)
										}
										disabled={ busy }
										onChange={ ( checked ) =>
											setExcludedProducts( ( current ) =>
												checked
													? current.filter(
															( id ) =>
																id !== change.id
														)
													: [ ...current, change.id ]
											)
										}
										__nextHasNoMarginBottom
									/>
								</li>
							) ) }
						</ul>
					</section>
				) }
			</div>
		</aside>
	);
}
