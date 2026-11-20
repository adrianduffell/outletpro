/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import {
	act,
	fireEvent,
	render,
	renderHook,
	waitFor,
	within,
} from '@testing-library/react';
import apiFetch from '@wordpress/api-fetch';
import { store as commandsStore } from '@wordpress/commands';
import { dispatch, select } from '@wordpress/data';
import { expect, test, vi } from 'vitest';
import Manager from '../Manager';
import useOutletPage from '../useOutletPage';

vi.mock( '@wordpress/api-fetch', () => ( { default: vi.fn() } ) );
vi.hoisted( () => {
	vi.stubGlobal( 'ResizeObserver', function ResizeObserver() {
		return { observe() {}, unobserve() {}, disconnect() {} };
	} );
	Object.defineProperty( window, 'matchMedia', {
		writable: true,
		value: () => ( {
			matches: true,
			addListener() {},
			removeListener() {},
			addEventListener() {},
			removeEventListener() {},
		} ),
	} );
} );

test( 'saves page fields without changing scheduled publishing or content', async () => {
	// Arrange.
	const page = {
		id: 42,
		title: { raw: 'Outlet' },
		slug: 'outlet',
		status: 'future',
		link: '/outlet/',
	};
	vi.mocked( apiFetch )
		.mockResolvedValueOnce( page )
		.mockResolvedValueOnce( { ...page, title: { raw: 'Clearance' } } );
	const { result } = renderHook( () => useOutletPage( 42 ) );
	await waitFor( () => expect( result.current.page ).toEqual( page ) );
	act( () => result.current.setTitle( 'Clearance' ) );

	// Act.
	await act( () => result.current.save() );

	// Assert.
	expect( apiFetch ).toHaveBeenLastCalledWith( {
		path: '/wp/v2/pages/42',
		method: 'POST',
		data: { title: 'Clearance', slug: 'outlet' },
	} );
	expect( result.current.page?.status ).toBe( 'future' );
	expect( result.current.dirty ).toBe( false );
} );

test( 'only publishes when explicitly requested', async () => {
	// Arrange.
	const page = {
		id: 42,
		title: { raw: 'Outlet' },
		slug: 'outlet',
		status: 'draft',
		link: '/outlet/',
	};
	vi.mocked( apiFetch )
		.mockResolvedValueOnce( page )
		.mockResolvedValueOnce( { ...page, status: 'publish' } );
	const { result } = renderHook( () => useOutletPage( 42 ) );
	await waitFor( () => expect( result.current.page ).toEqual( page ) );

	// Act.
	await act( () => result.current.save( 'publish' ) );

	// Assert.
	expect( apiFetch ).toHaveBeenLastCalledWith( {
		path: '/wp/v2/pages/42',
		method: 'POST',
		data: {
			title: 'Outlet',
			slug: 'outlet',
			status: 'publish',
			date: expect.any( String ),
		},
	} );
	expect( result.current.page?.status ).toBe( 'publish' );
} );

test( 'warns on navigation only while page details have unsaved edits', async () => {
	// Arrange.
	const page = {
		id: 42,
		title: { raw: 'Outlet' },
		slug: 'outlet',
		status: 'private',
		link: '/outlet/',
	};
	vi.mocked( apiFetch )
		.mockResolvedValueOnce( page )
		.mockResolvedValueOnce( { ...page, slug: 'clearance' } );
	const { result } = renderHook( () => useOutletPage( 42 ) );
	await waitFor( () => expect( result.current.page ).toEqual( page ) );
	act( () => result.current.setSlug( 'clearance' ) );
	const dirtyEvent = new Event( 'beforeunload', { cancelable: true } );

	// Act.
	window.dispatchEvent( dirtyEvent );
	await act( () => result.current.save() );
	const savedEvent = new Event( 'beforeunload', { cancelable: true } );
	window.dispatchEvent( savedEvent );

	// Assert.
	expect( dirtyEvent.defaultPrevented ).toBe( true );
	expect( savedEvent.defaultPrevented ).toBe( false );
	expect( result.current.page?.status ).toBe( 'private' );
} );

test( 'preserves unsaved details after a failed save', async () => {
	// Arrange.
	const log = vi.spyOn( console, 'error' ).mockImplementation( () => {} );
	const page = {
		id: 42,
		title: { raw: 'Outlet' },
		slug: 'outlet',
		status: 'draft',
		link: '/outlet/',
	};
	vi.mocked( apiFetch )
		.mockResolvedValueOnce( page )
		.mockRejectedValueOnce( { message: 'Save failed.' } );
	const { result } = renderHook( () => useOutletPage( 42 ) );
	await waitFor( () => expect( result.current.page ).toEqual( page ) );
	act( () => result.current.setTitle( 'Clearance' ) );

	// Act.
	await act( () => result.current.save() );

	// Assert.
	expect( result.current.error ).toBe( 'Save failed.' );
	expect( result.current.title ).toBe( 'Clearance' );
	expect( result.current.dirty ).toBe( true );
	expect( log ).toHaveBeenCalled();
	log.mockRestore();
} );

test( 'uses public toolbar controls and toggles the page sidebar', async () => {
	// Arrange.
	vi.mocked( apiFetch ).mockResolvedValue( {
		id: 42,
		title: { raw: 'Outlet' },
		slug: 'outlet',
		status: 'publish',
		link: '/outlet/',
	} );
	const config = {
		pageId: 42,
		canPublish: true,
		canManageProducts: false,
		settingsUrl: '/settings/',
		discountTiers: [],
		priceBands: [],
		categories: [],
	};
	const view = render( <Manager config={ config } /> );
	const sidebar = await view.findByRole( 'complementary', {
		name: 'Page details',
	} );
	await within( sidebar ).findByRole( 'heading', { name: 'Outlet' } );
	const toolbar = view.getByRole( 'toolbar', { name: 'Page actions' } );

	// Act.
	fireEvent.click(
		within( toolbar ).getByRole( 'button', { name: 'Page settings' } )
	);

	// Assert.
	expect(
		view.queryByRole( 'complementary', { name: 'Page details' } )
	).not.toBeInTheDocument();
	expect(
		view.getByRole( 'toolbar', { name: 'Outlet navigation' } )
	).toBeVisible();
	expect(
		within( toolbar ).getByRole( 'button', { name: 'Save' } )
	).toHaveAttribute( 'aria-disabled', 'true' );
	expect(
		view.queryByRole( 'button', { name: 'Add block' } )
	).not.toBeInTheDocument();
} );

test( 'closes the sidebar from its header and restores toolbar focus', async () => {
	// Arrange.
	vi.mocked( apiFetch ).mockResolvedValue( {
		id: 42,
		title: { raw: 'Outlet' },
		slug: 'outlet',
		status: 'publish',
		link: '/outlet/',
	} );
	const config = {
		pageId: 42,
		canPublish: true,
		canManageProducts: false,
		settingsUrl: null,
		discountTiers: [],
		priceBands: [],
		categories: [],
	};
	const view = render( <Manager config={ config } /> );
	const sidebar = await view.findByRole( 'complementary', {
		name: 'Page details',
	} );
	expect(
		within( sidebar ).getByRole( 'tab', { name: 'Page' } )
	).toHaveAttribute( 'aria-selected', 'true' );

	// Act.
	fireEvent.click(
		within( sidebar ).getByRole( 'button', { name: 'Close settings' } )
	);

	// Assert.
	expect(
		view.queryByRole( 'complementary', { name: 'Page details' } )
	).not.toBeInTheDocument();
	expect(
		view.getByRole( 'button', { name: 'Page settings' } )
	).toHaveFocus();
	expect(
		view.getByRole( 'button', { name: 'Page settings' } )
	).toHaveAttribute( 'aria-expanded', 'false' );
} );

test( 'renames the page through its title actions menu', async () => {
	// Arrange.
	const page = {
		id: 42,
		title: { raw: 'Outlet' },
		slug: 'outlet',
		status: 'publish',
		link: '/outlet/',
	};
	vi.mocked( apiFetch )
		.mockResolvedValueOnce( page )
		.mockResolvedValueOnce( { ...page, title: { raw: 'Clearance' } } );
	const config = {
		pageId: 42,
		canPublish: true,
		canManageProducts: false,
		settingsUrl: null,
		discountTiers: [],
		priceBands: [],
		categories: [],
	};
	const view = render( <Manager config={ config } /> );
	const sidebar = await view.findByRole( 'complementary', {
		name: 'Page details',
	} );
	await within( sidebar ).findByRole( 'heading', { name: 'Outlet' } );

	// Act.
	fireEvent.click(
		within( sidebar ).getByRole( 'button', { name: 'Page actions' } )
	);
	fireEvent.click( await view.findByRole( 'menuitem', { name: 'Rename' } ) );
	fireEvent.change( await view.findByRole( 'textbox', { name: 'Name' } ), {
		target: { value: 'Clearance' },
	} );
	fireEvent.click( view.getByRole( 'button', { name: 'Done' } ) );
	fireEvent.click( view.getByRole( 'button', { name: 'Save' } ) );
	const review = view.getByRole( 'complementary', {
		name: 'Review changes',
	} );
	expect( within( review ).getByText( 'Clearance' ) ).toBeVisible();
	fireEvent.click( within( review ).getByRole( 'button', { name: 'Save' } ) );

	// Assert.
	await waitFor( () =>
		expect( apiFetch ).toHaveBeenLastCalledWith( {
			path: '/wp/v2/pages/42',
			method: 'POST',
			data: { title: 'Clearance', slug: 'outlet' },
		} )
	);
	expect(
		within(
			view.getByRole( 'complementary', { name: 'Page details' } )
		).getByRole( 'heading', { name: 'Clearance' } )
	).toBeVisible();
} );

test( 'uses the native summary field editor to unpublish the page', async () => {
	// Arrange.
	const page = {
		id: 42,
		title: { raw: 'Outlet' },
		slug: 'outlet',
		status: 'publish',
		link: '/outlet/',
	};
	vi.mocked( apiFetch )
		.mockResolvedValueOnce( page )
		.mockResolvedValueOnce( { ...page, status: 'draft' } );
	const config = {
		pageId: 42,
		canPublish: true,
		canManageProducts: false,
		settingsUrl: null,
		discountTiers: [],
		priceBands: [],
		categories: [],
	};
	const view = render( <Manager config={ config } /> );
	const sidebar = await view.findByRole( 'complementary', {
		name: 'Page details',
	} );
	await within( sidebar ).findByText( 'Published', { exact: true } );

	// Act.
	fireEvent.click(
		within( sidebar ).getByRole( 'button', { name: 'Edit Status' } )
	);
	fireEvent.click( await view.findByRole( 'button', { name: 'Unpublish' } ) );
	const review = view.getByRole( 'complementary', {
		name: 'Review changes',
	} );
	expect(
		within( review ).getByText( 'This page will be unpublished.' )
	).toBeVisible();
	fireEvent.click( within( review ).getByRole( 'button', { name: 'Save' } ) );

	// Assert.
	await waitFor( () =>
		expect( apiFetch ).toHaveBeenLastCalledWith( {
			path: '/wp/v2/pages/42',
			method: 'POST',
			data: { title: 'Outlet', slug: 'outlet', status: 'draft' },
		} )
	);
	await within(
		view.getByRole( 'complementary', { name: 'Page details' } )
	).findByText( 'Draft', { exact: true } );
} );

test( 'opens the native command palette from the page bar', () => {
	// Arrange.
	dispatch( commandsStore ).close();
	vi.mocked( apiFetch ).mockResolvedValue( {
		id: 42,
		title: { raw: 'Outlet' },
		slug: 'outlet',
		status: 'publish',
		link: '/outlet/',
	} );
	const config = {
		pageId: 42,
		canPublish: true,
		canManageProducts: false,
		settingsUrl: null,
		discountTiers: [],
		priceBands: [],
		categories: [],
	};
	const view = render( <Manager config={ config } /> );

	// Act.
	fireEvent.click(
		view.getByRole( 'button', { name: 'Search commands and settings' } )
	);

	// Assert.
	expect( select( commandsStore ).isOpen() ).toBe( true );
	dispatch( commandsStore ).close();
} );

test( 'reviews queued products without writing until confirmation and warns before leaving', async () => {
	// Arrange.
	const product = {
		id: 9,
		name: 'Queued hat',
		image: '/hat.jpg',
		sku: 'HAT',
		price: '£10',
		stock_status: 'instock',
		manage_stock: false,
		stock_quantity: null,
		total_sales: 0,
		age_days: 0,
		status: 'publish',
		outlet: false,
		canEdit: true,
		editUrl: '/product/9',
		categories: [],
	};
	let storedOutlet = false;
	vi.mocked( apiFetch ).mockImplementation( async ( options ) => {
		if ( options.method === 'POST' ) {
			storedOutlet = true;
			return { results: [ { id: 9, outlet: true } ] };
		}
		if ( options.path?.startsWith( '/wp/v2/pages/' ) ) {
			return {
				id: 42,
				title: { raw: 'Outlet' },
				slug: 'outlet',
				status: 'publish',
				link: '/outlet/',
			};
		}
		return {
			items: [ { ...product, outlet: storedOutlet } ],
			totalItems: 1,
			totalPages: 1,
			totalOutletItems: storedOutlet ? 1 : 0,
		};
	} );
	const view = render(
		<Manager
			config={ {
				pageId: 42,
				canPublish: true,
				canManageProducts: true,
				settingsUrl: null,
				discountTiers: [],
				priceBands: [],
				categories: [],
			} }
		/>
	);
	const toggle = await view.findByRole( 'checkbox', {
		name: 'Include Queued hat in outlet',
	} );
	const toolbar = view.getByRole( 'toolbar', { name: 'Page actions' } );
	const requestsBefore = vi.mocked( apiFetch ).mock.calls.length;

	// Act.
	fireEvent.click( toggle );
	fireEvent.click(
		within( toolbar ).getByRole( 'button', { name: 'Save' } )
	);
	const review = view.getByRole( 'complementary', {
		name: 'Review changes',
	} );

	// Assert.
	expect( storedOutlet ).toBe( false );
	expect( vi.mocked( apiFetch ).mock.calls ).toHaveLength( requestsBefore );
	expect( toggle ).toBeChecked();
	expect( toggle ).toBeEnabled();
	expect( within( review ).getByText( 'Queued hat' ) ).toBeVisible();
	expect( within( review ).getByText( 'Include in outlet' ) ).toBeVisible();
	const dirtyEvent = new Event( 'beforeunload', { cancelable: true } );
	window.dispatchEvent( dirtyEvent );
	expect( dirtyEvent.defaultPrevented ).toBe( true );
	fireEvent.click(
		within( review ).getByRole( 'button', { name: 'Cancel' } )
	);
	expect( toggle ).toBeChecked();
	expect(
		within( toolbar ).getByRole( 'button', { name: 'Save' } )
	).toHaveFocus();
	fireEvent.click(
		within( toolbar ).getByRole( 'button', { name: 'Save' } )
	);
	fireEvent.click(
		within(
			view.getByRole( 'complementary', { name: 'Review changes' } )
		).getByRole( 'button', { name: 'Save' } )
	);
	await waitFor( () =>
		expect(
			view.queryByRole( 'complementary', { name: 'Review changes' } )
		).not.toBeInTheDocument()
	);
	expect( storedOutlet ).toBe( true );
	expect( toggle ).toBeChecked();
	expect( view.queryByTestId( 'snackbar' ) ).not.toBeInTheDocument();
	const savedEvent = new Event( 'beforeunload', { cancelable: true } );
	window.dispatchEvent( savedEvent );
	expect( savedEvent.defaultPrevented ).toBe( false );
} );

test( 'keeps failed product changes in the review and shows only save errors in a snackbar', async () => {
	// Arrange.
	const product = {
		id: 1,
		name: 'Hat',
		image: '/hat.jpg',
		sku: '',
		price: '',
		stock_status: 'instock',
		manage_stock: false,
		stock_quantity: null,
		total_sales: 0,
		age_days: 0,
		status: 'publish',
		outlet: false,
		canEdit: true,
		editUrl: null,
		categories: [],
	};
	vi.mocked( apiFetch ).mockImplementation( async ( options ) => {
		if ( options.method === 'POST' ) {
			return {
				results: [
					{ id: 1, outlet: true },
					{ id: 2, error: 'Permission denied.' },
				],
			};
		}
		if ( options.path?.startsWith( '/wp/v2/pages/' ) ) {
			return {
				id: 42,
				title: { raw: 'Outlet' },
				slug: 'outlet',
				status: 'publish',
				link: '/outlet/',
			};
		}
		return {
			items: [ product, { ...product, id: 2, name: 'Scarf' } ],
			totalItems: 2,
			totalPages: 1,
			totalOutletItems: 0,
		};
	} );
	const view = render(
		<Manager
			config={ {
				pageId: 42,
				canPublish: true,
				canManageProducts: true,
				settingsUrl: null,
				discountTiers: [],
				priceBands: [],
				categories: [],
			} }
		/>
	);
	fireEvent.click(
		await view.findByRole( 'checkbox', { name: 'Include Hat in outlet' } )
	);
	fireEvent.click(
		view.getByRole( 'checkbox', { name: 'Include Scarf in outlet' } )
	);
	fireEvent.click( view.getByRole( 'button', { name: 'Save' } ) );
	const review = view.getByRole( 'complementary', {
		name: 'Review changes',
	} );

	// Act.
	fireEvent.click( within( review ).getByRole( 'button', { name: 'Save' } ) );

	// Assert.
	await waitFor( () =>
		expect( within( review ).queryByText( 'Hat' ) ).not.toBeInTheDocument()
	);
	expect( within( review ).getByText( 'Scarf' ) ).toBeVisible();
	expect( await view.findByTestId( 'snackbar' ) ).toHaveTextContent(
		'Scarf: Permission denied.'
	);
	expect(
		view.getByRole( 'checkbox', { name: 'Include Scarf in outlet' } )
	).toBeChecked();
} );

test( 'keeps unticked products and page edits listed and saves only selected changes', async () => {
	// Arrange.
	const product = {
		id: 1,
		name: 'Hat',
		image: '/hat.jpg',
		sku: '',
		price: '',
		stock_status: 'instock',
		manage_stock: false,
		stock_quantity: null,
		total_sales: 0,
		age_days: 0,
		status: 'publish',
		outlet: true,
		canEdit: true,
		editUrl: null,
		categories: [],
	};
	let savedScarf = false;
	vi.mocked( apiFetch ).mockImplementation( async ( options ) => {
		if ( options.method === 'POST' ) {
			savedScarf = true;
			return { results: [ { id: 2, outlet: true } ] };
		}
		if ( options.path?.startsWith( '/wp/v2/pages/' ) ) {
			return {
				id: 42,
				title: { raw: 'Outlet' },
				slug: 'outlet',
				status: 'publish',
				link: '/outlet/',
			};
		}
		return {
			items: [
				product,
				{ ...product, id: 2, name: 'Scarf', outlet: savedScarf },
			],
			totalItems: 2,
			totalPages: 1,
			totalOutletItems: savedScarf ? 2 : 1,
		};
	} );
	const view = render(
		<Manager
			config={ {
				pageId: 42,
				canPublish: true,
				canManageProducts: true,
				settingsUrl: null,
				discountTiers: [],
				priceBands: [],
				categories: [],
			} }
		/>
	);
	const hat = await view.findByRole( 'checkbox', {
		name: 'Include Hat in outlet',
	} );
	const scarf = view.getByRole( 'checkbox', {
		name: 'Include Scarf in outlet',
	} );
	const sidebar = view.getByRole( 'complementary', { name: 'Page details' } );
	fireEvent.click(
		within( sidebar ).getByRole( 'button', { name: 'Page actions' } )
	);
	fireEvent.click( await view.findByRole( 'menuitem', { name: 'Rename' } ) );
	fireEvent.change( await view.findByRole( 'textbox', { name: 'Name' } ), {
		target: { value: 'Clearance' },
	} );
	fireEvent.click( view.getByRole( 'button', { name: 'Done' } ) );
	fireEvent.click( hat );
	fireEvent.click( scarf );
	fireEvent.click( view.getByRole( 'button', { name: 'Save' } ) );
	const review = view.getByRole( 'complementary', {
		name: 'Review changes',
	} );
	const hatChoice = within( review ).getByRole( 'checkbox', { name: 'Hat' } );
	const scarfChoice = within( review ).getByRole( 'checkbox', {
		name: 'Scarf',
	} );
	const pageChoice = within( review ).getByRole( 'checkbox', {
		name: 'Page details',
	} );
	const requestCount = vi.mocked( apiFetch ).mock.calls.length;

	// Act.
	fireEvent.click( hatChoice );
	fireEvent.click( pageChoice );

	// Assert.
	expect( hatChoice ).toBeVisible();
	expect( hatChoice ).not.toBeChecked();
	expect( pageChoice ).toBeVisible();
	expect( pageChoice ).not.toBeChecked();
	expect( hat ).not.toBeChecked();
	expect( scarf ).toBeChecked();
	expect( within( review ).getByText( 'Clearance' ) ).toBeVisible();
	expect(
		within( review ).queryByRole( 'heading', { name: 'Outlet products' } )
	).not.toBeInTheDocument();
	fireEvent.click( hatChoice );
	expect( hatChoice ).toBeChecked();
	fireEvent.click( hatChoice );
	fireEvent.click( scarfChoice );
	expect( scarfChoice ).toBeVisible();
	expect( scarfChoice ).not.toBeChecked();
	expect(
		within( review ).getByRole( 'button', { name: 'Save' } )
	).toBeDisabled();
	expect( vi.mocked( apiFetch ).mock.calls ).toHaveLength( requestCount );
	fireEvent.click( scarfChoice );
	fireEvent.click( within( review ).getByRole( 'button', { name: 'Save' } ) );
	await waitFor( () =>
		expect(
			view.queryByRole( 'complementary', { name: 'Review changes' } )
		).not.toBeInTheDocument()
	);
	expect( apiFetch ).toHaveBeenCalledWith( {
		path: '/outletpro/v1/products/batch',
		method: 'POST',
		data: { ids: [ 2 ], outlet: true },
	} );
	expect(
		vi
			.mocked( apiFetch )
			.mock.calls.slice( requestCount )
			.filter( ( [ options ] ) => options.method === 'POST' )
			.map( ( [ options ] ) => options.path )
	).toEqual( [ '/outletpro/v1/products/batch' ] );
	expect(
		view.getByRole( 'button', { name: 'Search commands and settings' } )
	).toHaveTextContent( 'Clearance' );
	fireEvent.click( view.getByRole( 'button', { name: 'Save' } ) );
	const nextReview = view.getByRole( 'complementary', {
		name: 'Review changes',
	} );
	expect(
		within( nextReview ).getByRole( 'checkbox', { name: 'Hat' } )
	).toBeChecked();
	expect(
		within( nextReview ).getByRole( 'checkbox', { name: 'Page details' } )
	).toBeChecked();
	expect(
		within( nextReview ).queryByRole( 'checkbox', { name: 'Scarf' } )
	).not.toBeInTheDocument();
	const event = new Event( 'beforeunload', { cancelable: true } );
	window.dispatchEvent( event );
	expect( event.defaultPrevented ).toBe( true );
} );

test( 'can untick and retick a publishing request without changing the page', async () => {
	// Arrange.
	vi.mocked( apiFetch ).mockResolvedValue( {
		id: 42,
		title: { raw: 'Outlet' },
		slug: 'outlet',
		status: 'draft',
		link: '/outlet/',
	} );
	const view = render(
		<Manager
			config={ {
				pageId: 42,
				canPublish: true,
				canManageProducts: false,
				settingsUrl: null,
				discountTiers: [],
				priceBands: [],
				categories: [],
			} }
		/>
	);
	await within(
		view.getByRole( 'complementary', { name: 'Page details' } )
	).findByRole( 'heading', { name: 'Outlet' } );
	fireEvent.click( view.getByRole( 'button', { name: 'Publish' } ) );
	const review = view.getByRole( 'complementary', {
		name: 'Review changes',
	} );
	const checkbox = within( review ).getByRole( 'checkbox', {
		name: 'Page details',
	} );
	const requestCount = vi.mocked( apiFetch ).mock.calls.length;

	// Act.
	fireEvent.click( checkbox );

	// Assert.
	expect( checkbox ).toBeVisible();
	expect( checkbox ).not.toBeChecked();
	expect(
		within( review ).getByRole( 'button', { name: 'Save' } )
	).toBeDisabled();
	fireEvent.click( checkbox );
	expect( checkbox ).toBeChecked();
	expect(
		within( review ).getByRole( 'button', { name: 'Publish' } )
	).toBeEnabled();
	expect( vi.mocked( apiFetch ).mock.calls ).toHaveLength( requestCount );
} );
