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
	await view.findByRole( 'button', { name: 'Edit slug' } );

	// Act.
	fireEvent.click( view.getByRole( 'button', { name: 'Rename page' } ) );
	fireEvent.change( view.getByRole( 'textbox', { name: 'Name' } ), {
		target: { value: 'Clearance' },
	} );
	fireEvent.keyDown( view.getByRole( 'textbox', { name: 'Name' } ), {
		key: 'Escape',
		keyCode: 27,
	} );
	fireEvent.click( view.getByRole( 'button', { name: 'Edit slug' } ) );
	fireEvent.change( view.getByRole( 'textbox', { name: 'Slug' } ), {
		target: { value: 'sale' },
	} );
	fireEvent.keyDown( view.getByRole( 'textbox', { name: 'Slug' } ), {
		key: 'Escape',
		keyCode: 27,
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
	await view.findByRole( 'button', { name: 'Edit slug' } );
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
	fireEvent.click( view.getByRole( 'button', { name: 'Change status' } ) );
	fireEvent.click( view.getByRole( 'radio', { name: 'Draft' } ) );
	fireEvent.keyDown( view.getByRole( 'radio', { name: 'Draft' } ), {
		key: 'Escape',
		keyCode: 27,
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
	await view.findByRole( 'button', { name: 'Edit slug' } );

	// Assert.
	expect(
		view.queryByRole( 'button', { name: 'Publish' } )
	).not.toBeInTheDocument();
	expect(
		view.queryByRole( 'button', { name: 'Change status' } )
	).not.toBeInTheDocument();
} );

test( 'keeps the selected status when cancelling save review', async () => {
	// Arrange.
	vi.stubGlobal(
		'matchMedia',
		vi.fn( () => ( {
			matches: true,
			addListener: vi.fn(),
			removeListener: vi.fn(),
		} ) )
	);
	const scheduled = { ...page( 'future' ), date: '2030-01-01T12:00:00' };
	vi.mocked( apiFetch )
		.mockReset()
		.mockResolvedValueOnce( new Response( JSON.stringify( scheduled ) ) );
	const { registry, wrapper } = createEditorRegistry( {
		outletpro_markdown_levels: [],
	} );
	const view = render(
		<OutletEditor config={ { pageId: 39, canPublish: true } } />,
		{ wrapper }
	);
	fireEvent.click(
		await view.findByRole( 'button', { name: 'Change status' } )
	);
	const current = view.getByRole( 'radio', { name: 'Scheduled' } );
	expect( current ).toBeChecked();
	expect( current ).toBeDisabled();
	fireEvent.click( view.getByRole( 'radio', { name: 'Published' } ) );
	fireEvent.keyDown( view.getByRole( 'radio', { name: 'Published' } ), {
		key: 'Escape',
		keyCode: 27,
	} );
	// Act.
	fireEvent.click(
		within(
			view.getByRole( 'toolbar', { name: 'Page actions' } )
		).getByRole( 'button', { name: 'Save' } )
	);
	fireEvent.click(
		within(
			view.getByRole( 'dialog', { name: 'Are you ready to save?' } )
		).getByRole( 'button', { name: 'Cancel' } )
	);
	// Assert.
	expect(
		registry
			.select( coreStore )
			.getEntityRecordEdits( 'postType', 'page', 39 )
	).toEqual( { status: 'publish' } );
	expect(
		registry
			.select( coreStore )
			.getEditedEntityRecord( 'postType', 'page', 39 )
	).toMatchObject( { date: scheduled.date } );
	expect( apiFetch ).toHaveBeenCalledTimes( 1 );
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
		fireEvent.click(
			await view.findByRole( 'button', { name: 'Edit slug' } )
		);
		fireEvent.change( view.getByRole( 'textbox', { name: 'Slug' } ), {
			target: { value: 'sale' },
		} );
		fireEvent.keyDown( view.getByRole( 'textbox', { name: 'Slug' } ), {
			key: 'Escape',
			keyCode: 27,
		} );
		act( () => {
			registry
				.dispatch( coreStore )
				.editEntityRecord( 'root', 'site', undefined, {
					outletpro_markdown_levels: [ 40 ],
				} );
		} );
		// Act.
		if ( status === 'publish' ) {
			fireEvent.click(
				view.getByRole( 'button', { name: 'Change status' } )
			);
			fireEvent.click( view.getByRole( 'radio', { name: 'Draft' } ) );
			fireEvent.keyDown( view.getByRole( 'radio', { name: 'Draft' } ), {
				key: 'Escape',
				keyCode: 27,
			} );
		}
		fireEvent.click(
			view.getByRole( 'button', {
				name: 'Publish',
			} )
		);
		fireEvent.click(
			within(
				view.getByRole( 'dialog', { name: 'Are you ready to save?' } )
			).getByRole( 'button', { name: 'Cancel' } )
		);
		// Assert.
		expect(
			registry
				.select( coreStore )
				.getEntityRecordEdits( 'postType', 'page', 39 )
		).toEqual(
			status === 'publish'
				? { slug: 'sale', status: 'draft' }
				: { slug: 'sale' }
		);
		expect(
			view.getByRole( 'button', { name: 'Edit slug' } )
		).toHaveTextContent( 'sale' );
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
	fireEvent.click( await view.findByRole( 'button', { name: 'Edit slug' } ) );
	fireEvent.change( view.getByRole( 'textbox', { name: 'Slug' } ), {
		target: { value: 'sale' },
	} );
	fireEvent.keyDown( view.getByRole( 'textbox', { name: 'Slug' } ), {
		key: 'Escape',
		keyCode: 27,
	} );
	act( () => {
		registry
			.dispatch( coreStore )
			.editEntityRecord( 'root', 'site', undefined, {
				outletpro_markdown_levels: [],
			} );
	} );
	// Act.
	fireEvent.click( view.getByRole( 'button', { name: 'Change status' } ) );
	fireEvent.click( view.getByRole( 'radio', { name: 'Published' } ) );
	fireEvent.keyDown( view.getByRole( 'radio', { name: 'Published' } ), {
		key: 'Escape',
		keyCode: 27,
	} );
	fireEvent.click(
		within(
			view.getByRole( 'toolbar', { name: 'Page actions' } )
		).getByRole( 'button', { name: 'Save' } )
	);
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
	).toEqual( { slug: 'sale', status: 'publish' } );
	expect( apiFetch ).toHaveBeenCalledTimes( 2 );
	expect( apiFetch ).toHaveBeenLastCalledWith( {
		path: '/wp/v2/settings',
		method: 'POST',
		data: { outletpro_markdown_levels: [] },
	} );
} );
