/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { act, fireEvent, render, within } from '@testing-library/react';
import apiFetch from '@wordpress/api-fetch';
import { store as coreStore } from '@wordpress/core-data';
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
		vi.fn( () => ( {
			matches: true,
			addListener: vi.fn(),
			removeListener: vi.fn(),
		} ) )
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
			view.getByRole( 'dialog', { name: 'Are you ready to save?' } )
		).getByRole( 'button', { name: 'Save' } )
	);

	// Assert.
	await within( view.container ).findByText( 'Site updated.' );
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
		vi.fn( () => ( {
			matches: true,
			addListener: vi.fn(),
			removeListener: vi.fn(),
		} ) )
	);
	vi.mocked( apiFetch ).mockReset();
	vi.mocked( apiFetch ).mockImplementation( async ( options ) => {
		if ( options.parse === false ) {
			return new Response( JSON.stringify( page() ) );
		}
		return page( options.data?.status === 'publish' ? 'publish' : 'draft' );
	} );
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
			view.getByRole( 'dialog', { name: 'Are you ready to save?' } )
		).getByRole( 'button', { name: 'Save' } )
	);
	await view.findByText( 'Published', { exact: true } );
	fireEvent.click( view.getByRole( 'button', { name: 'Unpublish' } ) );
	fireEvent.click(
		within(
			view.getByRole( 'dialog', { name: 'Are you ready to save?' } )
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
		vi.fn( () => ( {
			matches: true,
			addListener: vi.fn(),
			removeListener: vi.fn(),
		} ) )
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
test.each( [ 'draft', 'publish' ] )(
	'cancels publishing intent without discarding drafts for a %s page',
	async ( status ) => {
		// Arrange.
		vi.stubGlobal(
			'matchMedia',
			vi.fn( () => ( {
				matches: true,
				addListener: vi.fn(),
				removeListener: vi.fn(),
			} ) )
		);
		vi.mocked( apiFetch )
			.mockReset()
			.mockResolvedValueOnce(
				new Response( JSON.stringify( page( status ) ) )
			);
		const { registry, wrapper } = createEditorRegistry( {
			outletpro_markdown_levels: [ 30 ],
		} );
		const view = render(
			<OutletEditor config={ { pageId: 39, canPublish: true } } />,
			{ wrapper }
		);
		await view.findByRole( 'textbox', { name: 'Slug' } );
		fireEvent.change( view.getByRole( 'textbox', { name: 'Slug' } ), {
			target: { value: 'sale' },
		} );
		act( () => {
			registry
				.dispatch( coreStore )
				.editEntityRecord( 'root', 'site', undefined, {
					outletpro_markdown_levels: [ 40 ],
				} );
		} );
		// Act.
		fireEvent.click(
			view.getByRole( 'button', {
				name: status === 'draft' ? 'Publish' : 'Unpublish',
			} )
		);
		fireEvent.click(
			within(
				view.getByRole( 'dialog', { name: 'Are you ready to save?' } )
			).getByRole( 'button', { name: 'Cancel' } )
		);
		fireEvent.click(
			view.getByRole( 'button', { name: 'Markdown Levels' } )
		);
		// Assert.
		expect(
			registry
				.select( coreStore )
				.getEntityRecordEdits( 'postType', 'page', 39 )
		).toEqual( { slug: 'sale' } );
		expect( view.getByRole( 'textbox', { name: 'Slug' } ) ).toHaveValue(
			'sale'
		);
		expect( view.getByRole( 'button', { name: '40% off' } ) ).toBeVisible();
		expect( apiFetch ).toHaveBeenCalledTimes( 1 );
	}
);

test( 'excludes publishing without discarding other page edits', async () => {
	// Arrange.
	vi.stubGlobal(
		'matchMedia',
		vi.fn( () => ( {
			matches: true,
			addListener: vi.fn(),
			removeListener: vi.fn(),
		} ) )
	);
	vi.mocked( apiFetch )
		.mockReset()
		.mockResolvedValueOnce( new Response( JSON.stringify( page() ) ) )
		.mockResolvedValueOnce( { outletpro_markdown_levels: [] } );
	const { registry, wrapper } = createEditorRegistry( {
		outletpro_markdown_levels: [ 30 ],
	} );
	const view = render(
		<OutletEditor config={ { pageId: 39, canPublish: true } } />,
		{ wrapper }
	);
	await view.findByRole( 'textbox', { name: 'Slug' } );
	fireEvent.change( view.getByRole( 'textbox', { name: 'Slug' } ), {
		target: { value: 'sale' },
	} );
	act( () => {
		registry
			.dispatch( coreStore )
			.editEntityRecord( 'root', 'site', undefined, {
				outletpro_markdown_levels: [],
			} );
	} );
	// Act.
	fireEvent.click( view.getByRole( 'button', { name: 'Publish' } ) );
	const review = within(
		view.getByRole( 'dialog', { name: 'Are you ready to save?' } )
	);
	fireEvent.click( review.getByRole( 'checkbox', { name: 'Outlet' } ) );
	fireEvent.click( review.getByRole( 'button', { name: 'Save' } ) );
	await within( view.container ).findByText( 'Site updated.' );
	// Assert.
	expect(
		registry
			.select( coreStore )
			.getEntityRecordEdits( 'postType', 'page', 39 )
	).toEqual( { slug: 'sale' } );
	expect( apiFetch ).toHaveBeenCalledTimes( 2 );
	expect( apiFetch ).toHaveBeenLastCalledWith( {
		path: '/wp/v2/settings',
		method: 'POST',
		data: { outletpro_markdown_levels: [] },
	} );
} );
