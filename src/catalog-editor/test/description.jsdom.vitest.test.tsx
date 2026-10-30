/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { act, render, waitFor } from '@testing-library/react';
import { expect, test } from 'vitest';
import { PageEditorCallout } from '../../page-editor-callout/callout';
import { CatalogPageDescription } from '../description';
import { CatalogTemplateLink } from '../template-link';

test( 'the description appears beneath the sidebar title without replacing native controls', async () => {
	// Arrange.
	const sidebar = render(
		<section className="editor-post-summary">
			<div className="editor-post-card-panel">
				<div className="editor-post-card-panel__header">
					<h2>Outlet</h2>
				</div>
			</div>
			<button>Set featured image</button>
		</section>
	);

	// Act.
	const view = render(
		<CatalogPageDescription>
			<PageEditorCallout>
				<CatalogTemplateLink
					templateId="theme//outlet-catalog"
					currentUrl="https://example.com/wp-admin/post.php"
				/>
			</PageEditorCallout>
		</CatalogPageDescription>
	);
	try {
		// Assert.
		const description = await sidebar.findByText(
			'Displays products in the outlet.'
		);
		expect(
			description.parentElement?.querySelectorAll( ':scope > p' )
		).toHaveLength( 1 );
		const callout = sidebar
			.getByRole( 'link', { name: 'Edit outlet template' } )
			.closest( '.outletpro-page-editor-callout' );
		expect( callout?.previousElementSibling ).toBe( description );
		expect( callout ).toHaveTextContent( 'Outlet page template' );
		expect(
			sidebar.getByRole( 'img', { name: 'Outlet page template' } )
		).toBeInTheDocument();
		expect( description.parentElement?.previousElementSibling ).toBe(
			sidebar.getByRole( 'heading', { name: 'Outlet' } ).parentElement
		);
		expect(
			sidebar.getByRole( 'button', { name: 'Set featured image' } )
		).toBeInTheDocument();
		expect(
			sidebar.container.querySelectorAll( '.outletpro-page-description' )
		).toHaveLength( 1 );
	} finally {
		view.unmount();
		sidebar.unmount();
	}
} );

test( 'a delayed sidebar receives the description again after it is closed and reopened', async () => {
	// Arrange.
	const view = render(
		<CatalogPageDescription>
			<PageEditorCallout>
				<CatalogTemplateLink
					templateId="theme//outlet-catalog"
					currentUrl="https://example.com/wp-admin/post.php"
				/>
			</PageEditorCallout>
		</CatalogPageDescription>
	);
	const sidebar = render( <></> );
	const contents = (
		<section className="editor-post-summary">
			<div className="editor-post-card-panel">
				<div className="editor-post-card-panel__header">Outlet</div>
			</div>
		</section>
	);
	try {
		// Act.
		sidebar.rerender( contents );
		await sidebar.findByText( 'Displays products in the outlet.' );
		sidebar.rerender( <></> );
		sidebar.rerender( contents );

		// Assert.
		await sidebar.findByText( 'Displays products in the outlet.' );
		expect(
			sidebar.getAllByRole( 'link', { name: 'Edit outlet template' } )
		).toHaveLength( 1 );
		expect(
			sidebar.container.querySelectorAll( '.outletpro-page-description' )
		).toHaveLength( 1 );
	} finally {
		view.unmount();
		sidebar.unmount();
	}
} );

test( 'updating the native title preserves one description', async () => {
	// Arrange.
	const sidebar = render(
		<section className="editor-post-summary">
			<div className="editor-post-card-panel">
				<div className="editor-post-card-panel__header">Outlet</div>
			</div>
		</section>
	);
	const view = render( <CatalogPageDescription /> );
	try {
		await sidebar.findByText( 'Displays products in the outlet.' );

		// Act.
		sidebar.rerender(
			<section className="editor-post-summary">
				<div className="editor-post-card-panel">
					<div className="editor-post-card-panel__header">
						Clearance
					</div>
				</div>
			</section>
		);

		// Assert.
		await waitFor( () => {
			expect( sidebar.getByText( 'Clearance' ) ).toBeInTheDocument();
			expect(
				sidebar.queryAllByText( 'Displays products in the outlet.' )
			).toHaveLength( 1 );
		} );
	} finally {
		view.unmount();
		sidebar.unmount();
	}
} );

test( 'unmounting removes the description and stops watching for rebuilt sidebars', async () => {
	// Arrange.
	const sidebar = render(
		<section className="editor-post-summary">
			<div className="editor-post-card-panel">
				<div className="editor-post-card-panel__header">Outlet</div>
			</div>
		</section>
	);
	const view = render( <CatalogPageDescription /> );
	try {
		await sidebar.findByText( 'Displays products in the outlet.' );

		// Act.
		view.unmount();
		await act( async () => {
			sidebar.rerender(
				<section key="replacement" className="editor-post-summary">
					<div className="editor-post-card-panel">
						<div className="editor-post-card-panel__header">
							Outlet
						</div>
					</div>
				</section>
			);
		} );

		// Assert.
		expect( sidebar.getByText( 'Outlet' ) ).toBeInTheDocument();
		expect(
			sidebar.container.querySelector( '.outletpro-page-description' )
		).toBeNull();
	} finally {
		view.unmount();
		sidebar.unmount();
	}
} );
