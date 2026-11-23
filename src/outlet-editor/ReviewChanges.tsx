/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { Button, CheckboxControl } from '@wordpress/components';
import { useEffect, useRef, useState } from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';
import type { ChangeSelection, ChangeSource } from './changes';
import './review.css';

export default function ReviewChanges( {
	sources,
	busy,
	failed,
	publishing,
	onSave,
	onClose,
}: {
	sources: ChangeSource[];
	busy: boolean;
	failed: boolean;
	publishing: boolean;
	onSave: ( selected: ChangeSelection ) => void;
	onClose: () => void;
} ) {
	const [ excluded, setExcluded ] = useState< ChangeSelection >( {} );
	const cancel = useRef< HTMLButtonElement >( null );
	const panel = useRef< HTMLElement >( null );
	const selected: ChangeSelection = Object.fromEntries(
		sources.map( ( source ) => [
			source.id,
			source.changes
				.filter(
					( change ) => ! excluded[ source.id ]?.includes( change.id )
				)
				.map( ( change ) => change.id ),
		] )
	);
	const count = sources.reduce(
		( total, source ) => total + source.changes.length,
		0
	);
	const selectedCount = Object.values( selected ).reduce(
		( total, ids ) => total + ids.length,
		0
	);
	useEffect( () => cancel.current?.focus(), [] );
	useEffect( () => {
		function onKeyDown( event: KeyboardEvent ) {
			if ( event.key !== 'Escape' ) {
				return;
			}
			if ( busy ) {
				return;
			}
			if ( ! ( event.target instanceof Node ) ) {
				return;
			}
			if ( ! panel.current?.contains( event.target ) ) {
				return;
			}
			onClose();
		}
		window.addEventListener( 'keydown', onKeyDown );
		return () => window.removeEventListener( 'keydown', onKeyDown );
	}, [ busy, onClose ] );
	return (
		<section
			ref={ panel }
			className="outletpro-editor-review"
			aria-label={ __( 'Review changes', 'outletpro' ) }
		>
			<div className="outletpro-editor-review-actions">
				<Button
					ref={ cancel }
					variant="secondary"
					disabled={ busy }
					onClick={ onClose }
				>
					{ __( 'Cancel', 'outletpro' ) }
				</Button>
				<Button
					variant="primary"
					disabled={ busy || failed || selectedCount === 0 }
					isBusy={ busy }
					onClick={ () => onSave( selected ) }
				>
					{ publishing && selected.page?.length
						? __( 'Publish', 'outletpro' )
						: __( 'Save', 'outletpro' ) }
				</Button>
			</div>
			<div className="outletpro-editor-review-content">
				<strong>{ __( 'Are you ready to save?', 'outletpro' ) }</strong>
				<p>
					{ sprintf(
						/* translators: %d: number of pending editor changes. */ _n(
							'There is %d change waiting to be saved.',
							'There are %d changes waiting to be saved.',
							count,
							'outletpro'
						),
						count
					) }
				</p>
				{ sources
					.filter( ( source ) => source.changes.length > 0 )
					.map( ( source ) => (
						<section key={ source.id } aria-label={ source.label }>
							<h3>{ source.label }</h3>
							{ source.changes.map( ( change ) => (
								<div
									key={ change.id }
									className="outletpro-editor-review-change"
								>
									<CheckboxControl
										label={ change.label }
										help={ change.description }
										checked={ selected[
											source.id
										].includes( change.id ) }
										disabled={ busy || failed }
										onChange={ ( checked ) =>
											setExcluded( ( previous ) => ( {
												...previous,
												[ source.id ]: checked
													? (
															previous[
																source.id
															] ?? []
														).filter(
															( id ) =>
																id !== change.id
														)
													: [
															...( previous[
																source.id
															] ?? [] ),
															change.id,
														],
											} ) )
										}
										__nextHasNoMarginBottom
									/>
									{ change.details }
								</div>
							) ) }
						</section>
					) ) }
			</div>
		</section>
	);
}
