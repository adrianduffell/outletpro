/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import {
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis -- Match the header used by WordPress's slug popover.
	__experimentalInspectorPopoverHeader as InspectorPopoverHeader,
} from '@wordpress/block-editor';
import { BaseControl, Button, Popover } from '@wordpress/components';
import { useId, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

export default function LevelPopover( {
	anchor,
	title,
	label,
	min,
	max,
	mode,
	initialValue,
	validate,
	disabled = false,
	onChange,
	onClose,
	onFocusOutside,
}: {
	anchor: HTMLElement;
	title: string;
	label: string;
	min: number;
	max?: number;
	mode: 'add' | 'edit';
	initialValue: number | '';
	validate?: ( value: number ) => string | undefined;
	disabled?: boolean;
	onChange: ( value: number ) => void;
	onClose: () => void;
	onFocusOutside?: () => void;
} ) {
	const [ value, setValue ] = useState( initialValue );
	const id = useId();
	const validNumber =
		typeof value === 'number' &&
		Number.isInteger( value ) &&
		value >= min &&
		( max === undefined || value <= max );
	let validationError = '';
	if ( value !== '' ) {
		if ( validNumber ) {
			validationError = validate?.( value ) ?? '';
		} else if ( max === undefined ) {
			validationError = sprintf(
				/* translators: %d: minimum level. */
				__( 'Enter a whole number of at least %d.', 'outletpro' ),
				min
			);
		} else {
			validationError = sprintf(
				/* translators: 1: minimum level, 2: maximum level. */
				__( 'Enter a whole number from %1$d to %2$d.', 'outletpro' ),
				min,
				max
			);
		}
	}
	const valid = validNumber && ! validationError;
	return (
		<Popover
			anchor={ anchor }
			placement="left-start"
			offset={ 8 }
			shift
			resize={ false }
			noArrow
			focusOnMount="firstInputElement"
			onClose={ onClose }
			onFocusOutside={ onFocusOutside }
			role="dialog"
			aria-label={ title }
		>
			<div
				style={ {
					width: 280,
					maxWidth: 'calc(100vw - 32px)',
					padding: 16,
				} }
			>
				<InspectorPopoverHeader title={ title } onClose={ onClose } />
				<form
					style={ { marginTop: 16 } }
					onSubmit={ ( event ) => {
						event.preventDefault();
						if ( disabled ) {
							return;
						}
						if ( ! valid ) {
							return;
						}
						if ( typeof value !== 'number' ) {
							return;
						}
						if ( mode === 'add' ) {
							onChange( value );
						}
						onClose();
					} }
				>
					<BaseControl
						id={ id }
						label={ label }
						hideLabelFromVision
						help={ validationError }
					>
						<input
							id={ id }
							className="components-text-control__input"
							type="number"
							min={ min }
							max={ max }
							step={ 1 }
							required
							value={ value }
							disabled={ disabled }
							aria-invalid={ value !== '' && ! valid }
							aria-describedby={
								validationError ? `${ id }__help` : undefined
							}
							onChange={ ( event ) => {
								const next =
									event.currentTarget.value === ''
										? ''
										: event.currentTarget.valueAsNumber;
								setValue( next );
								if ( mode === 'add' ) {
									return;
								}
								if ( ! event.currentTarget.validity.valid ) {
									return;
								}
								if ( typeof next !== 'number' ) {
									return;
								}
								if ( validate?.( next ) ) {
									return;
								}
								onChange( next );
							} }
						/>
					</BaseControl>
					{ mode === 'add' && (
						<div
							style={ {
								display: 'flex',
								justifyContent: 'flex-end',
								gap: 8,
								marginTop: 16,
							} }
						>
							<Button variant="secondary" onClick={ onClose }>
								{ __( 'Cancel', 'outletpro' ) }
							</Button>
							<Button
								variant="primary"
								type="submit"
								disabled={ disabled || ! valid }
							>
								{ __( 'Add', 'outletpro' ) }
							</Button>
						</div>
					) }
				</form>
			</div>
		</Popover>
	);
}
