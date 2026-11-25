/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { render, within } from '@testing-library/react';
import { expect, test } from 'vitest';
import EditorShell from '../EditorShell';

test( 'renders an empty workspace without unfinished feature controls', () => {
	// Arrange and act.
	const view = render( <EditorShell /> );

	// Assert.
	expect(
		view.getByRole( 'main', { name: 'Outlet workspace' } )
	).toBeEmptyDOMElement();
	expect( view.queryByRole( 'complementary' ) ).not.toBeInTheDocument();
	expect( view.queryByRole( 'button' ) ).not.toBeInTheDocument();
} );

test( 'places independent content in the header, workspace, settings and notices', () => {
	// Arrange and act.
	const view = render(
		<EditorShell
			header={ <header>Editor navigation</header> }
			workspace={ <section>Workspace panel</section> }
			sidebar={ <section>Settings panel</section> }
			notices={ <p role="status">Saved</p> }
		/>
	);

	// Assert.
	expect( view.getByRole( 'banner' ) ).toHaveTextContent(
		'Editor navigation'
	);
	expect(
		within( view.getByRole( 'main' ) ).getByText( 'Workspace panel' )
	).toBeVisible();
	expect(
		within(
			view.getByRole( 'complementary', { name: 'Page settings' } )
		).getByText( 'Settings panel' )
	).toBeVisible();
	expect( view.getByRole( 'status' ) ).toHaveTextContent( 'Saved' );
	expect(
		view.container.querySelector( '.outletpro-editor-layout' )
	).toHaveClass( 'is-sidebar-open' );
} );

test( 'places review above the editor layout and restores settings when dismissed', () => {
	// Arrange.
	const sidebar = <button>Settings control</button>;
	const view = render(
		<EditorShell sidebar={ sidebar } review={ <button>Cancel</button> } />
	);

	// Assert.
	const review = view.getByRole( 'complementary', {
		name: 'Review changes',
	} );
	expect( review.parentElement ).toHaveClass( 'outletpro-editor' );
	expect(
		within( review ).getByRole( 'button', { name: 'Cancel' } )
	).toBeVisible();
	expect(
		view.queryByRole( 'button', { name: 'Settings control' } )
	).not.toBeInTheDocument();
	expect(
		view.container.querySelector( '.outletpro-editor-layout' )
	).toHaveClass( 'is-sidebar-open' );

	// Act.
	view.rerender( <EditorShell sidebar={ sidebar } /> );

	// Assert.
	expect(
		view.queryByRole( 'complementary', { name: 'Review changes' } )
	).not.toBeInTheDocument();
	expect(
		within(
			view.getByRole( 'complementary', { name: 'Page settings' } )
		).getByRole( 'button', { name: 'Settings control' } )
	).toBeVisible();
} );
