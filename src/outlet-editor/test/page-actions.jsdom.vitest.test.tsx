/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { fireEvent, render, within } from '@testing-library/react';
import apiFetch from '@wordpress/api-fetch';
import { expect, test, vi } from 'vitest';
import OutletEditor from '../OutletEditor';
import { createEditorRegistry } from '../test-utils';

vi.mock( '@wordpress/api-fetch', () => ( { default: vi.fn() } ) );

function page( status = 'draft' ) {
	return {
		id: 39,
		title: { raw: 'Outlet' },
		slug: 'outlet',
		status,
		link: '/outlet/',
	};
}

test( 'reviews and saves renamed page details through the pages REST API', async () => {
	// Arrange.
	vi.stubGlobal(
		'matchMedia',
		vi.fn( () => ( { matches: true } ) )
	);
	vi.mocked( apiFetch ).mockReset();
	vi.mocked( apiFetch )
		.mockResolvedValueOnce( new Response( JSON.stringify( page() ) ) )
		.mockResolvedValueOnce( {
			...page(),
			title: { raw: 'Clearance' },
			slug: 'sale',
			link: '/sale/',
		} );
	const { wrapper } = createEditorRegistry( {
		outletpro_markdown_levels: [],
	} );
	const view = render(
		<OutletEditor config={ { pageId: 39, canPublish: true } } />,
		{ wrapper }
	);
	await view.findByRole( 'textbox', { name: 'Slug' } );

	// Act.
	fireEvent.click( view.getByRole( 'button', { name: 'Rename page' } ) );
	fireEvent.change( view.getByRole( 'textbox', { name: 'Name' } ), {
		target: { value: 'Clearance' },
	} );
	fireEvent.click( view.getByRole( 'button', { name: 'Done' } ) );
	fireEvent.change( view.getByRole( 'textbox', { name: 'Slug' } ), {
		target: { value: 'sale' },
	} );
	fireEvent.click(
		within(
			view.getByRole( 'toolbar', { name: 'Page actions' } )
		).getByRole( 'button', { name: 'Save' } )
	);
	fireEvent.click(
		within(
			view.getByRole( 'region', { name: 'Review changes' } )
		).getByRole( 'button', { name: 'Save' } )
	);

	// Assert.
	await view.findByRole( 'textbox', { name: 'Slug' } );
	expect( apiFetch ).toHaveBeenLastCalledWith( {
		path: '/wp/v2/pages/39',
		method: 'PUT',
		data: { id: 39, title: 'Clearance', slug: 'sale' },
	} );
	expect( view.getByRole( 'link', { name: 'View outlet' } ) ).toHaveAttribute(
		'href',
		'/sale/'
	);
} );

test( 'publishes and unpublishes through the shared review flow', async () => {
	// Arrange.
	vi.stubGlobal(
		'matchMedia',
		vi.fn( () => ( { matches: true } ) )
	);
	vi.mocked( apiFetch ).mockReset();
	vi.mocked( apiFetch )
		.mockResolvedValueOnce( new Response( JSON.stringify( page() ) ) )
		.mockResolvedValueOnce( page( 'publish' ) )
		.mockResolvedValueOnce( page() );
	const { wrapper } = createEditorRegistry( {
		outletpro_markdown_levels: [],
	} );
	const view = render(
		<OutletEditor config={ { pageId: 39, canPublish: true } } />,
		{ wrapper }
	);
	fireEvent.click( await view.findByRole( 'button', { name: 'Publish' } ) );

	// Act.
	fireEvent.click(
		within(
			view.getByRole( 'region', { name: 'Review changes' } )
		).getByRole( 'button', { name: 'Publish' } )
	);
	await view.findByText( 'Published', { exact: true } );
	fireEvent.click( view.getByRole( 'button', { name: 'Unpublish' } ) );
	fireEvent.click(
		within(
			view.getByRole( 'region', { name: 'Review changes' } )
		).getByRole( 'button', { name: 'Save' } )
	);

	// Assert.
	expect( await view.findByText( 'Draft', { exact: true } ) ).toBeVisible();
	expect( apiFetch ).toHaveBeenNthCalledWith( 2, {
		path: '/wp/v2/pages/39',
		method: 'PUT',
		data: {
			id: 39,
			status: 'publish',
			date: expect.any( String ),
		},
	} );
	expect( apiFetch ).toHaveBeenLastCalledWith( {
		path: '/wp/v2/pages/39',
		method: 'PUT',
		data: { id: 39, status: 'draft' },
	} );
} );

test( 'hides publishing controls when publishing is not permitted', async () => {
	// Arrange.
	vi.stubGlobal(
		'matchMedia',
		vi.fn( () => ( { matches: true } ) )
	);
	vi.mocked( apiFetch ).mockReset();
	vi.mocked( apiFetch ).mockResolvedValueOnce(
		new Response( JSON.stringify( page( 'publish' ) ) )
	);
	const { wrapper } = createEditorRegistry( {
		outletpro_markdown_levels: [],
	} );

	// Act.
	const view = render(
		<OutletEditor config={ { pageId: 39, canPublish: false } } />,
		{ wrapper }
	);
	await view.findByRole( 'textbox', { name: 'Slug' } );

	// Assert.
	expect(
		view.queryByRole( 'button', { name: 'Publish' } )
	).not.toBeInTheDocument();
	expect(
		view.queryByRole( 'button', { name: 'Unpublish' } )
	).not.toBeInTheDocument();
} );

test( 'stops before publishing when a feature save fails', async () => {
	// Arrange.
	vi.stubGlobal(
		'matchMedia',
		vi.fn( () => ( { matches: true } ) )
	);
	const log = vi.spyOn( console, 'error' ).mockImplementation( () => {} );
	vi.mocked( apiFetch ).mockReset();
	vi.mocked( apiFetch )
		.mockResolvedValueOnce( new Response( JSON.stringify( page() ) ) )
		.mockRejectedValueOnce( { message: 'Panel failed.' } );
	const source = {
		id: 'feature',
		label: 'Feature',
		changes: [ { id: 'one', label: 'Setting' } ],
		save: async () => {
			await apiFetch( { path: '/feature', method: 'POST' } );
		},
	};
	const { wrapper } = createEditorRegistry( {
		outletpro_markdown_levels: [],
	} );
	const view = render(
		<OutletEditor
			config={ { pageId: 39, canPublish: true } }
			changeSources={ [ source ] }
		/>,
		{ wrapper }
	);
	fireEvent.click( await view.findByRole( 'button', { name: 'Publish' } ) );

	// Act.
	fireEvent.click(
		within(
			view.getByRole( 'region', { name: 'Review changes' } )
		).getByRole( 'button', { name: 'Publish' } )
	);

	// Assert.
	expect(
		await within( view.container ).findByText(
			'Changes could not be saved. Reload the editor before continuing.'
		)
	).toBeVisible();
	expect( apiFetch ).toHaveBeenCalledTimes( 2 );
	expect( apiFetch ).toHaveBeenLastCalledWith( {
		path: '/feature',
		method: 'POST',
	} );
	fireEvent.click( view.getByRole( 'button', { name: 'Cancel' } ) );
	expect(
		await view.findByRole( 'textbox', { name: 'Slug' } )
	).toBeDisabled();
	log.mockRestore();
} );
