/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import type { MouseEvent } from 'react';
import {
	Button,
	DropdownMenu,
	Notice,
	PanelBody,
	Spinner,
} from '@wordpress/components';
import { useRef, useState } from '@wordpress/element';
import { moreVertical, plus, tag } from '@wordpress/icons';
import { __, sprintf } from '@wordpress/i18n';
import LevelPopover from './LevelPopover';
import './markdown-levels.css';

export default function MarkdownLevels( {
	levels: unsortedLevels,
	error,
	onChange,
	disabled,
}: {
	levels: number[] | null;
	error: string;
	onChange: ( levels: number[] ) => void;
	disabled: boolean;
} ) {
	const [ editing, setEditing ] = useState< {
		index: number | 'add';
		anchor: HTMLButtonElement;
		levels: number[];
	} | null >( null );
	const add = useRef< HTMLButtonElement >( null );
	const rows = useRef< ( HTMLButtonElement | null )[] >( [] );
	const levels =
		editing?.levels ??
		( unsortedLevels === null
			? null
			: [ ...unsortedLevels ].sort(
					( first, second ) => first - second
				) );
	const popoverTitle =
		editing?.index === 'add'
			? __( 'Add markdown level', 'outletpro' )
			: __( 'Markdown level', 'outletpro' );
	function removeLevel( removedIndex: number ) {
		if ( levels === null ) {
			throw new Error( 'Markdown levels are unavailable.' );
		}
		onChange( levels.filter( ( _, index ) => index !== removedIndex ) );
		setEditing( null );
		add.current?.focus();
	}
	function closePopover( restoreFocus: boolean ) {
		if ( editing === null ) {
			return;
		}
		let anchor = editing.anchor;
		if ( editing.index !== 'add' ) {
			const sorted = [ ...editing.levels ].sort(
				( first, second ) => first - second
			);
			anchor =
				rows.current[
					sorted.indexOf( editing.levels[ editing.index ] )
				] ?? anchor;
			onChange( sorted );
		}
		if ( restoreFocus ) {
			anchor.focus();
		}
		setEditing( null );
	}
	return (
		<PanelBody
			title={ __( 'Markdown Levels', 'outletpro' ) }
			initialOpen
			className="outletpro-markdown-levels"
		>
			{ error && (
				<Notice status="error" isDismissible={ false }>
					{ error }
				</Notice>
			) }
			{ ! error && levels === null && <Spinner /> }
			{ ! error && levels !== null && (
				<>
					{ levels.map( ( level, index ) => (
						<div
							key={ index }
							className="outletpro-markdown-level-row"
						>
							<Button
								ref={ ( element ) => {
									rows.current[ index ] = element;
								} }
								className="outletpro-markdown-level"
								icon={ tag }
								disabled={ disabled }
								aria-haspopup="dialog"
								aria-expanded={
									editing?.index === index &&
									editing.anchor !== add.current
								}
								onClick={ (
									event: MouseEvent< HTMLButtonElement >
								) => {
									setEditing( {
										index,
										anchor: event.currentTarget,
										levels,
									} );
								} }
							>
								{ `${ level }% off` }
							</Button>
							<DropdownMenu
								className="outletpro-markdown-level-menu"
								icon={ moreVertical }
								label={ __( 'Options', 'outletpro' ) }
								toggleProps={ { disabled } }
								controls={ [
									{
										title: sprintf(
											/* translators: %d: markdown percentage. */
											__(
												'Remove %d%% off',
												'outletpro'
											),
											level
										),
										isDisabled: disabled,
										onClick: () => removeLevel( index ),
									},
								] }
							/>
						</div>
					) ) }
					<Button
						ref={ add }
						icon={ plus }
						label={ __( 'Add markdown level', 'outletpro' ) }
						variant="primary"
						disabled={ disabled }
						aria-haspopup="dialog"
						aria-expanded={
							editing !== null && editing.anchor === add.current
						}
						onClick={ (
							event: MouseEvent< HTMLButtonElement >
						) => {
							setEditing( {
								index: 'add',
								anchor: event.currentTarget,
								levels,
							} );
						} }
					/>
				</>
			) }
			{ editing !== null && levels !== null && (
				<LevelPopover
					key={ editing.index }
					anchor={ editing.anchor }
					title={ popoverTitle }
					label={ __( 'Percentage', 'outletpro' ) }
					min={ 1 }
					max={ 100 }
					mode={ editing.index === 'add' ? 'add' : 'edit' }
					initialValue={
						editing.index === 'add' ? '' : levels[ editing.index ]
					}
					disabled={ disabled }
					validate={ ( value ) =>
						levels.some(
							( level, index ) =>
								index !== editing.index && level === value
						)
							? __(
									'This markdown level already exists.',
									'outletpro'
								)
							: undefined
					}
					onChange={ ( value ) => {
						if ( editing.index === 'add' ) {
							onChange(
								[ ...levels, value ].sort(
									( first, second ) => first - second
								)
							);
							return;
						}
						const nextLevels = levels.map( ( level, index ) =>
							index === editing.index ? value : level
						);
						setEditing( { ...editing, levels: nextLevels } );
						onChange( nextLevels );
					} }
					onClose={ () => closePopover( true ) }
					onFocusOutside={ () => closePopover( false ) }
				/>
			) }
		</PanelBody>
	);
}
