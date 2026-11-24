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
import apiFetch from '@wordpress/api-fetch';
import { expect, test, vi } from 'vitest';
import OutletEditor from '../OutletEditor';
import { createEditorRegistry } from '../test-utils';

vi.mock( '@wordpress/api-fetch', () => ( { default: vi.fn() } ) );

const cases = [
	{
		kind: 'markdown',
		option: 'outletpro_markdown_levels',
		before: '30% off',
		after: '40% off',
		input: 'Percentage',
	},
	{
		kind: 'price',
		option: 'outletpro_price_levels',
		before: 'Under £30',
		after: 'Under £40',
		input: 'Price',
	},
];

function page() {
	return {
		id: 39,
		title: { raw: 'Outlet' },
		slug: 'outlet',
		status: 'draft',
		link: '/outlet/',
	};
}

test( 'shows settings load failures in the sidebar', async () => {
	// Arrange.
	vi.stubGlobal(
		'matchMedia',
		vi.fn( () => ( {
			matches: true,
			addListener: vi.fn(),
			removeListener: vi.fn(),
		} ) )
	);
	const failure = { message: 'Settings access denied.' };
	vi.mocked( apiFetch ).mockReset();
	vi.mocked( apiFetch ).mockImplementation( async ( options ) => {
		if ( options.path === '/wp/v2/settings' ) {
			throw failure;
		}
		return new Response( JSON.stringify( page() ) );
	} );
	const { wrapper } = createEditorRegistry();
	// Act.
	const view = render(
		<OutletEditor config={ { pageId: 39, canPublish: true } } />,
		{ wrapper }
	);
	// Assert.
	expect(
		( await within( view.container ).findAllByText( failure.message ) )[ 0 ]
	).toBeVisible();
	expect(
		view.queryByRole( 'button', { name: 'Add markdown level' } )
	).not.toBeInTheDocument();
} );

test.each( cases )( 'retains $kind edits', async ( level ) => {
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
		.mockResolvedValueOnce( new Response( JSON.stringify( page() ) ) );
	const { wrapper } = createEditorRegistry( {
		[ level.option ]: [ 30 ],
	} );
	const view = render(
		<OutletEditor config={ { pageId: 39, canPublish: true } } />,
		{ wrapper }
	);
	await view.findByRole( 'button', { name: level.before } );
	await view.findByRole( 'button', { name: 'Edit slug' } );
	// Act.
	fireEvent.click( view.getByRole( 'button', { name: level.before } ) );
	fireEvent.change( view.getByRole( 'spinbutton' ), {
		target: { value: '40' },
	} );
	fireEvent.click( view.getByRole( 'button', { name: 'Close settings' } ) );
	fireEvent.click( view.getByRole( 'button', { name: 'Page settings' } ) );
	// Assert.
	expect(
		await view.findByRole( 'button', { name: level.after } )
	).toBeVisible();
	expect(
		within(
			view.getByRole( 'toolbar', { name: 'Page actions' } )
		).getByRole( 'button', { name: 'Save' } )
	).toBeEnabled();
	expect( apiFetch ).not.toHaveBeenCalledWith(
		expect.objectContaining( {
			method: expect.stringMatching( /^(POST|PUT|PATCH|DELETE)$/ ),
		} )
	);
} );

test.each( cases )( 'saves $kind edits', async ( level ) => {
	// Arrange.
	vi.stubGlobal(
		'matchMedia',
		vi.fn( () => ( {
			matches: true,
			addListener: vi.fn(),
			removeListener: vi.fn(),
		} ) )
	);
	const completion: {
		resolve?: ( value: Record< string, unknown > ) => void;
	} = {};
	const response = new Promise< Record< string, unknown > >( ( resolve ) => {
		completion.resolve = resolve;
	} );
	vi.mocked( apiFetch )
		.mockReset()
		.mockResolvedValueOnce( new Response( JSON.stringify( page() ) ) )
		.mockReturnValueOnce( response );
	const { wrapper } = createEditorRegistry( {
		[ level.option ]: [ 30 ],
	} );
	const view = render(
		<OutletEditor config={ { pageId: 39, canPublish: true } } />,
		{ wrapper }
	);
	await view.findByRole( 'button', { name: `Add ${ level.kind } level` } );
	// Act.
	fireEvent.click(
		view.getByRole( 'button', { name: `Add ${ level.kind } level` } )
	);
	fireEvent.change( view.getByRole( 'spinbutton', { name: level.input } ), {
		target: { value: '40' },
	} );
	fireEvent.click( view.getByRole( 'button', { name: 'Add' } ) );
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
	await waitFor( () =>
		expect(
			view.getByRole( 'button', { name: level.after } )
		).toBeDisabled()
	);
	expect(
		view.getByRole( 'button', { name: 'Rename page' } )
	).toBeDisabled();
	expect( view.getByRole( 'button', { name: 'Edit slug' } ) ).toBeDisabled();
	expect(
		view.getByRole( 'button', { name: 'Change status' } )
	).toBeDisabled();
	expect( view.getByRole( 'button', { name: 'Publish' } ) ).toBeDisabled();
	await act( async () => {
		if ( completion.resolve === undefined ) {
			throw new Error( 'The pending save could not be completed.' );
		}
		completion.resolve( { [ level.option ]: [ 40 ] } );
	} );
	await waitFor( () =>
		expect(
			view.getByRole( 'button', { name: 'Page settings' } )
		).toHaveFocus()
	);
	expect( view.getByRole( 'button', { name: level.after } ) ).toBeEnabled();
} );
