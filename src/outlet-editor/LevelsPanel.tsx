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
import { __ } from '@wordpress/i18n';
import LevelPopover from './LevelPopover';
import './markdown-levels.css';

export type LevelsProps = {
	levels: number[] | null;
	error: string;
	onChange: ( levels: number[] ) => void;
	disabled: boolean;
};

export default function LevelsPanel( {
	levels: unsortedLevels,
	error,
	onChange,
	disabled,
	labels,
	formatLevel,
	formatRemoveLabel,
	min,
	max,
}: LevelsProps & {
	labels: {
		title: string;
		add: string;
		edit: string;
		input: string;
		duplicate: string;
	};
	formatLevel: ( level: number ) => string;
	formatRemoveLabel: ( level: number ) => string;
	min: number;
	max?: number;
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
	const popoverTitle = editing?.level === 'add' ? labels.add : labels.edit;
	function removeLevel( removedLevel: number ) {
		if ( levels === null ) {
			throw new Error( 'Levels are unavailable.' );
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
			title={ labels.title }
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
								aria-expanded={ editing?.level === level }
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
								{ formatLevel( level ) }
							</Button>
							<DropdownMenu
								className="outletpro-markdown-level-menu"
								icon={ moreVertical }
								label={ __( 'Options', 'outletpro' ) }
								toggleProps={ { disabled } }
								controls={ [
									{
										title: formatRemoveLabel( level ),
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
						label={ labels.add }
						variant="primary"
						disabled={ disabled }
						aria-haspopup="dialog"
						aria-expanded={ editing?.level === 'add' }
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
					label={ labels.input }
					min={ min }
					max={ max }
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
							? labels.duplicate
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
