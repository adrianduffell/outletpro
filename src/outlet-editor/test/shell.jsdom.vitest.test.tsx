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
