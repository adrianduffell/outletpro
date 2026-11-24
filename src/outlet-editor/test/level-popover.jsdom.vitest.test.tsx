/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import {
	act,
	fireEvent,
	render,
	waitFor,
	within,
} from '@testing-library/react';
import { expect, test, vi } from 'vitest';
import LevelPopover from '../LevelPopover';

test.each( [ 0, 750, 1000 ] )(
	'edits a price level to %d using the supplied title and range',
	async ( value ) => {
		// Arrange.
		vi.stubGlobal(
			'matchMedia',
			vi.fn( () => ( {
				matches: true,
				addListener: vi.fn(),
				removeListener: vi.fn(),
			} ) )
		);
		const trigger = render( <button>Price level</button> );
		const anchor = trigger.getByRole( 'button' );
		anchor.focus();
		const change = vi.fn();
		const close = vi.fn();
		const view = render(
			<LevelPopover
				anchor={ anchor }
				title="Price level"
				label="Amount"
				min={ 0 }
				max={ 1000 }
				mode="edit"
				initialValue={ 250 }
				onChange={ change }
				onClose={ close }
			/>
		);
		const input = within(
			view.getByRole( 'dialog', { name: 'Price level' } )
		).getByRole( 'spinbutton', { name: 'Amount' } );
		await waitFor( () => expect( input ).toHaveFocus() );
		// Act.
		fireEvent.change( input, { target: { value: value.toString() } } );
		fireEvent.keyDown( input, { key: 'Escape', keyCode: 27 } );
		view.unmount();
		// Assert.
		expect( change ).toHaveBeenCalledExactlyOnceWith( value );
		expect( close ).toHaveBeenCalledOnce();
		expect( input ).toHaveAttribute( 'min', '0' );
		expect( input ).toHaveAttribute( 'max', '1000' );
		expect( anchor ).toHaveFocus();
	}
);

test.each( [
	{ value: '', description: '' },
	{ value: '-1', description: 'Enter a whole number from 0 to 1000.' },
	{ value: '1001', description: 'Enter a whole number from 0 to 1000.' },
	{ value: '1.5', description: 'Enter a whole number from 0 to 1000.' },
	{ value: '250', description: 'This price level already exists.' },
] )(
	'prevents adding invalid or unavailable value "$value"',
	( { value, description } ) => {
		// Arrange.
		vi.stubGlobal(
			'matchMedia',
			vi.fn( () => ( {
				matches: true,
				addListener: vi.fn(),
				removeListener: vi.fn(),
			} ) )
		);
		const trigger = render( <button>Add price level</button> );
		const change = vi.fn();
		const view = render(
			<LevelPopover
				anchor={ trigger.getByRole( 'button' ) }
				title="Add price level"
				label="Amount"
				min={ 0 }
				max={ 1000 }
				mode="add"
				initialValue=""
				validate={ ( next ) =>
					next === 250
						? 'This price level already exists.'
						: undefined
				}
				onChange={ change }
				onClose={ vi.fn() }
			/>
		);
		// Act.
		fireEvent.change( view.getByRole( 'spinbutton' ), {
			target: { value },
		} );
		// Assert.
		expect( view.getByRole( 'button', { name: 'Add' } ) ).toBeDisabled();
		expect( change ).not.toHaveBeenCalled();
		expect( view.getByRole( 'spinbutton' ) ).toHaveAccessibleDescription(
			description
		);
	}
);

test.each( [
	{ action: 'Add', expected: [ [ 750 ] ] },
	{ action: 'Cancel', expected: [] },
	{ action: 'Close', expected: [] },
] )(
	'handles $action without notifying the parent before submission',
	( { action, expected } ) => {
		// Arrange.
		vi.stubGlobal(
			'matchMedia',
			vi.fn( () => ( {
				matches: true,
				addListener: vi.fn(),
				removeListener: vi.fn(),
			} ) )
		);
		const trigger = render( <button>Add price level</button> );
		const change = vi.fn();
		const close = vi.fn();
		const view = render(
			<LevelPopover
				anchor={ trigger.getByRole( 'button' ) }
				title="Add price level"
				label="Amount"
				min={ 0 }
				max={ 1000 }
				mode="add"
				initialValue=""
				onChange={ change }
				onClose={ close }
			/>
		);
		// Act.
		fireEvent.change( view.getByRole( 'spinbutton' ), {
			target: { value: '750' },
		} );
		expect( change ).not.toHaveBeenCalled();
		fireEvent.click( view.getByRole( 'button', { name: action } ) );
		// Assert.
		expect( change.mock.calls ).toEqual( expected );
		expect( close ).toHaveBeenCalledOnce();
	}
);

test( 'keeps invalid edits and unavailable values out of the parent state', () => {
	// Arrange.
	vi.stubGlobal(
		'matchMedia',
		vi.fn( () => ( {
			matches: true,
			addListener: vi.fn(),
			removeListener: vi.fn(),
		} ) )
	);
	const trigger = render( <button>Price level</button> );
	const change = vi.fn();
	const view = render(
		<LevelPopover
			anchor={ trigger.getByRole( 'button' ) }
			title="Price level"
			label="Amount"
			min={ 0 }
			max={ 1000 }
			mode="edit"
			initialValue={ 250 }
			validate={ ( value ) =>
				value === 500 ? 'This price level already exists.' : undefined
			}
			onChange={ change }
			onClose={ vi.fn() }
		/>
	);
	// Act.
	for ( const value of [ '1001', '1.5', '', '500' ] ) {
		fireEvent.change( view.getByRole( 'spinbutton' ), {
			target: { value },
		} );
	}
	// Assert.
	expect( change ).not.toHaveBeenCalled();
	expect( view.getByRole( 'spinbutton' ) ).toHaveAttribute(
		'aria-invalid',
		'true'
	);
	expect( view.getByRole( 'spinbutton' ) ).toHaveAccessibleDescription(
		'This price level already exists.'
	);
} );

test( 'notifies the caller when focus leaves the popover', async () => {
	// Arrange.
	vi.stubGlobal(
		'matchMedia',
		vi.fn( () => ( {
			matches: true,
			addListener: vi.fn(),
			removeListener: vi.fn(),
		} ) )
	);
	const trigger = render(
		<>
			<button>Price level</button>
			<button>Elsewhere</button>
		</>
	);
	const outside = vi.fn();
	const close = vi.fn();
	const view = render(
		<LevelPopover
			anchor={ trigger.getByRole( 'button', { name: 'Price level' } ) }
			title="Price level"
			label="Amount"
			min={ 0 }
			max={ 1000 }
			mode="edit"
			initialValue={ 250 }
			onChange={ vi.fn() }
			onClose={ close }
			onFocusOutside={ outside }
		/>
	);
	await waitFor( () =>
		expect( view.getByRole( 'spinbutton' ) ).toHaveFocus()
	);
	// Act.
	act( () => trigger.getByRole( 'button', { name: 'Elsewhere' } ).focus() );
	// Assert.
	await waitFor( () => expect( outside ).toHaveBeenCalledOnce() );
	expect( close ).not.toHaveBeenCalled();
} );
