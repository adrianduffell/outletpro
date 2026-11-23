/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { fireEvent, render } from '@testing-library/react';
import { createRegistry, RegistryProvider } from '@wordpress/data';
import { store as commandsStore } from '@wordpress/commands';
import { expect, test, vi } from 'vitest';
import EditorFrame from '../EditorFrame';

test( 'closes settings with Escape and restores focus to the toolbar', () => {
	// Arrange.
	vi.stubGlobal(
		'matchMedia',
		vi.fn( () => ( { matches: true } ) )
	);
	const registry = createRegistry();
	registry.register( commandsStore );
	const view = render(
		<RegistryProvider value={ registry }>
			<EditorFrame
				title="Clearance"
				link="/clearance/"
				settings={ <button>Settings control</button> }
			/>
		</RegistryProvider>
	);
	const toggle = view.getByRole( 'button', { name: 'Page settings' } );

	// Act.
	const control = view.getByRole( 'button', { name: 'Settings control' } );
	control.focus();
	fireEvent.keyDown( control, { key: 'Escape' } );

	// Assert.
	expect( view.queryByRole( 'complementary' ) ).not.toBeInTheDocument();
	expect( toggle ).toHaveFocus();
	expect( toggle ).toHaveAttribute( 'aria-expanded', 'false' );
	fireEvent.click( toggle );
	expect( view.getByRole( 'complementary' ) ).toBeVisible();
	fireEvent.click( view.getByRole( 'button', { name: 'Close settings' } ) );
	expect( toggle ).toHaveFocus();
	expect( view.getByRole( 'link', { name: 'View outlet' } ) ).toHaveAttribute(
		'href',
		'/clearance/'
	);
} );

test( 'opens the real command store and starts narrow screens with settings closed', () => {
	// Arrange.
	vi.stubGlobal(
		'matchMedia',
		vi.fn( () => ( { matches: false } ) )
	);
	const registry = createRegistry();
	registry.register( commandsStore );
	const view = render(
		<RegistryProvider value={ registry }>
			<EditorFrame title="Clearance" />
		</RegistryProvider>
	);

	// Act.
	fireEvent.click(
		view.getByRole( 'button', { name: 'Search commands and settings' } )
	);

	// Assert.
	expect( registry.select( commandsStore ).isOpen() ).toBe( true );
	expect( view.queryByRole( 'complementary' ) ).not.toBeInTheDocument();
	expect(
		view.queryByRole( 'link', { name: 'View outlet' } )
	).not.toBeInTheDocument();
	expect(
		view
			.getByRole( 'link', { name: 'Back to pages' } )
			.getAttribute( 'href' )
	).toContain( 'edit.php?post_type=page' );
} );
