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
import { useEffect, useRef, useState } from '@wordpress/element';
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
	const [ editing, setEditing ] = useState<
		| { level: 'add'; anchor: HTMLButtonElement }
		| { level: number; anchor: HTMLButtonElement; value: number }
		| null
	>( null );
	const focusAfterClose = useRef< number | null >( null );
	const pendingChange = useRef< ( () => void ) | null >( null );
	useEffect( () => () => pendingChange.current?.(), [] );
	const add = useRef< HTMLButtonElement >( null );
	const levels =
		unsortedLevels === null
			? null
			: [ ...unsortedLevels ].sort( ( first, second ) => first - second );
	const popoverTitle =
		editing?.level === 'add'
			? __( 'Add markdown level', 'outletpro' )
			: __( 'Markdown level', 'outletpro' );
	function removeLevel( removedLevel: number ) {
		if ( levels === null ) {
			throw new Error( 'Markdown levels are unavailable.' );
		}
		pendingChange.current = null;
		onChange( levels.filter( ( level ) => level !== removedLevel ) );
		setEditing( null );
		add.current?.focus();
	}
	function closePopover( restoreFocus: boolean ) {
		if ( editing === null ) {
			return;
		}
		if ( restoreFocus ) {
			if ( editing.level === 'add' ) {
				editing.anchor.focus();
			} else {
				focusAfterClose.current = editing.value;
			}
		}
		const change = pendingChange.current;
		pendingChange.current = null;
		change?.();
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
					{ levels.map( ( level ) => (
						<div
							key={ level }
							className="outletpro-markdown-level-row"
						>
							<Button
								ref={ ( element ) => {
									if (
										element &&
										focusAfterClose.current === level
									) {
										element.focus();
										focusAfterClose.current = null;
									}
								} }
								className="outletpro-markdown-level"
								icon={ tag }
								disabled={ disabled }
								aria-haspopup="dialog"
								aria-expanded={
									editing?.level === level &&
									editing.anchor !== add.current
								}
								onClick={ (
									event: MouseEvent< HTMLButtonElement >
								) => {
									setEditing( {
										level,
										anchor: event.currentTarget,
										value: level,
									} );
								} }
							>
								{ sprintf(
									/* translators: %d: markdown percentage. */
									__( '%d%% off', 'outletpro' ),
									level
								) }
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
										onClick: () => removeLevel( level ),
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
								level: 'add',
								anchor: event.currentTarget,
							} );
						} }
					/>
				</>
			) }
			{ editing !== null && levels !== null && (
				<LevelPopover
					key={ editing.level }
					anchor={ editing.anchor }
					title={ popoverTitle }
					label={ __( 'Percentage', 'outletpro' ) }
					min={ 1 }
					max={ 100 }
					mode={ editing.level === 'add' ? 'add' : 'edit' }
					initialValue={
						editing.level === 'add' ? '' : editing.value
					}
					disabled={ disabled }
					validate={ ( value ) =>
						levels.some(
							( level ) =>
								level !== editing.level && level === value
						)
							? __(
									'This markdown level already exists.',
									'outletpro'
								)
							: undefined
					}
					onChange={ ( value ) => {
						if ( editing.level === 'add' ) {
							onChange(
								[ ...levels, value ].sort(
									( first, second ) => first - second
								)
							);
							return;
						}
						pendingChange.current = () =>
							onChange(
								levels
									.map( ( level ) =>
										level === editing.level ? value : level
									)
									.sort( ( first, second ) => first - second )
							);
						setEditing( { ...editing, value } );
					} }
					onClose={ () => closePopover( true ) }
					onFocusOutside={ () => closePopover( false ) }
				/>
			) }
		</PanelBody>
	);
}
