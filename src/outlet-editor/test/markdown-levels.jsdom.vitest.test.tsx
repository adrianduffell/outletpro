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
import { useState } from '@wordpress/element';
import { expect, test, vi } from 'vitest';
import MarkdownLevels from '../MarkdownLevels';

function TestPanel( { initialLevels }: { initialLevels: number[] } ) {
	const [ levels, setLevels ] = useState( initialLevels );
	return (
		<>
			<MarkdownLevels
				levels={ levels }
				onChange={ setLevels }
				error=""
				disabled={ false }
			/>
			<output aria-label="Levels">{ JSON.stringify( levels ) }</output>
		</>
	);
}

function renderPanel( initialLevels: number[] ) {
	vi.stubGlobal(
		'matchMedia',
		vi.fn( () => ( {
			matches: true,
			addListener: vi.fn(),
			removeListener: vi.fn(),
		} ) )
	);
	return render( <TestPanel initialLevels={ initialLevels } /> );
}

test( 'shows a load error without editable defaults', () => {
	// Arrange.
	const change = vi.fn();
	// Act.
	const view = render(
		<MarkdownLevels
			levels={ null }
			error="Settings access denied."
			onChange={ change }
			disabled={ false }
		/>
	);
	// Assert.
	expect(
		within( view.container ).getByText( 'Settings access denied.' )
	).toBeVisible();
	expect(
		view.queryByRole( 'button', { name: 'Add markdown level' } )
	).not.toBeInTheDocument();
	expect( change ).not.toHaveBeenCalled();
} );

test.each( [ '', '0', '101', '1.5', '30' ] )(
	'keeps invalid or duplicate input "%s" out of the controlled list',
	( value ) => {
		// Arrange.
		const view = renderPanel( [ 30, 50 ] );
		// Act.
		fireEvent.click(
			view.getByRole( 'button', { name: 'Add markdown level' } )
		);
		fireEvent.change(
			view.getByRole( 'spinbutton', { name: 'Percentage' } ),
			{ target: { value } }
		);
		// Assert.
		expect( view.getByRole( 'button', { name: 'Add' } ) ).toBeDisabled();
		expect(
			view.getByRole( 'status', { name: 'Levels' } )
		).toHaveTextContent( '[30,50]' );
	}
);

test( 'adds only on confirmation and removes every level through its options menu', () => {
	// Arrange.
	const view = renderPanel( [ 30, 50 ] );
	const add = view.getByRole( 'button', { name: 'Add markdown level' } );
	const edited = view.getByRole( 'button', { name: '30% off' } );
	// Act.
	fireEvent.click( edited );
	fireEvent.change( view.getByRole( 'spinbutton' ), {
		target: { value: '95' },
	} );
	fireEvent.keyDown( view.getByRole( 'spinbutton' ), {
		key: 'Escape',
		keyCode: 27,
	} );
	fireEvent.click( add );
	expect( view.getByRole( 'spinbutton' ) ).toHaveValue( null );
	fireEvent.change( view.getByRole( 'spinbutton' ), {
		target: { value: '30' },
	} );
	expect(
		view.queryByRole( 'button', { name: '30% off' } )
	).not.toBeInTheDocument();
	fireEvent.click( view.getByRole( 'button', { name: 'Cancel' } ) );
	expect( add ).toHaveFocus();
	fireEvent.click( add );
	expect( view.getByRole( 'spinbutton' ) ).toHaveValue( null );
	fireEvent.change( view.getByRole( 'spinbutton' ), {
		target: { value: '30' },
	} );
	fireEvent.click( view.getByRole( 'button', { name: 'Add' } ) );
	expect( view.getByRole( 'status', { name: 'Levels' } ) ).toHaveTextContent(
		'[30,50,95]'
	);
	for ( const level of [ 30, 50, 95 ] ) {
		fireEvent.click(
			view.getAllByRole( 'button', { name: 'Options' } )[ 0 ]
		);
		expect( view.getAllByRole( 'menuitem' ) ).toHaveLength( 1 );
		fireEvent.click(
			view.getByRole( 'menuitem', { name: `Remove ${ level }% off` } )
		);
	}
	// Assert.
	expect(
		view.queryByRole( 'button', { name: /^\d+% off$/ } )
	).not.toBeInTheDocument();
	expect( view.getByRole( 'status', { name: 'Levels' } ) ).toHaveTextContent(
		'[]'
	);
	expect( add ).toHaveFocus();
} );

test( 'keeps edits local until closing and restores focus after sorting', () => {
	// Arrange.
	const view = renderPanel( [ 30, 50, 70 ] );
	// Act.
	fireEvent.click( view.getByRole( 'button', { name: '30% off' } ) );
	expect( view.getByRole( 'spinbutton' ) ).toHaveValue( 30 );
	expect(
		view.getByRole( 'dialog', { name: 'Markdown level' } )
	).not.toHaveTextContent( '% off' );
	expect(
		within( view.getByRole( 'dialog' ) ).getAllByRole( 'button' )
	).toHaveLength( 1 );
	for ( const value of [ '90', '95' ] ) {
		fireEvent.change( view.getByRole( 'spinbutton' ), {
			target: { value },
		} );
		expect(
			view
				.getAllByRole( 'button', { name: /^\d+% off$/ } )
				.map( ( button ) => button.textContent )
		).toEqual( [ '30% off', '50% off', '70% off' ] );
		expect(
			view.getByRole( 'status', { name: 'Levels' } )
		).toHaveTextContent( '[30,50,70]' );
	}
	fireEvent.keyDown( view.getByRole( 'spinbutton' ), {
		key: 'Escape',
		keyCode: 27,
	} );
	expect( view.getByRole( 'status', { name: 'Levels' } ) ).toHaveTextContent(
		'[50,70,95]'
	);
	expect( view.getByRole( 'button', { name: '95% off' } ) ).toHaveFocus();
	fireEvent.click( view.getByRole( 'button', { name: '95% off' } ) );
	for ( const value of [ '20', '30' ] ) {
		fireEvent.change( view.getByRole( 'spinbutton' ), {
			target: { value },
		} );
	}
	expect( view.getByRole( 'status', { name: 'Levels' } ) ).toHaveTextContent(
		'[50,70,95]'
	);
	fireEvent.click( view.getByRole( 'button', { name: 'Close' } ) );
	// Assert.
	expect( view.getByRole( 'status', { name: 'Levels' } ) ).toHaveTextContent(
		'[30,50,70]'
	);
	expect( view.queryByRole( 'dialog' ) ).not.toBeInTheDocument();
	expect( view.getByRole( 'button', { name: '30% off' } ) ).toHaveFocus();
} );

test( 'displays saved levels in numeric order without changing the supplied list', () => {
	// Arrange.
	const levels = [ 100, 50, 5 ];
	const change = vi.fn();
	// Act.
	const view = render(
		<MarkdownLevels
			levels={ levels }
			error=""
			onChange={ change }
			disabled={ false }
		/>
	);
	// Assert.
	expect(
		view
			.getAllByRole( 'button', { name: /^\d+% off$/ } )
			.map( ( button ) => button.textContent )
	).toEqual( [ '5% off', '50% off', '100% off' ] );
	expect( levels ).toEqual( [ 100, 50, 5 ] );
	expect( change ).not.toHaveBeenCalled();
} );

test.each( [ 'level', 'options' ] )(
	'keeps focus on the same logical row when closing onto another row’s %s button',
	async ( target ) => {
		// Arrange.
		const view = renderPanel( [ 30, 50, 70 ] );
		const edited = view.getByRole( 'button', { name: '30% off' } );
		fireEvent.click( edited );
		fireEvent.change( view.getByRole( 'spinbutton' ), {
			target: { value: '95' },
		} );
		await waitFor( () =>
			expect( view.getByRole( 'spinbutton' ) ).toHaveFocus()
		);
		const focused =
			target === 'level'
				? view.getByRole( 'button', { name: '50% off' } )
				: view.getAllByRole( 'button', { name: 'Options' } )[ 1 ];
		// Act.
		act( () => focused.focus() );
		await waitFor( () =>
			expect( view.queryByRole( 'dialog' ) ).not.toBeInTheDocument()
		);
		// Assert.
		expect( focused.closest( '.outletpro-markdown-level-row' ) ).toBe(
			view
				.getByRole( 'button', { name: '50% off' } )
				.closest( '.outletpro-markdown-level-row' )
		);
		expect( focused ).toHaveFocus();
		expect(
			view.getByRole( 'status', { name: 'Levels' } )
		).toHaveTextContent( '[50,70,95]' );
	}
);
