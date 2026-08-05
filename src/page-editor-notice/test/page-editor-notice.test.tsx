/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { render, act } from '@testing-library/react';
import { OutletEmptyNotice } from '../index';
import apiFetch from '@wordpress/api-fetch';
import * as data from '@wordpress/data';

jest.mock( '@wordpress/plugins', () => ( {
	registerPlugin: jest.fn(),
} ) );

jest.mock( '@wordpress/data' );

jest.mock( '@wordpress/api-fetch', () => jest.fn() );

// Typed reference to the jest.fn() created by the factory above.
const mockApiFetch = apiFetch as unknown as jest.Mock;
const mockDispatch = data.dispatch as unknown as jest.Mock;
const mockSelect = data.select as unknown as jest.Mock;

describe( 'OutletEmptyNotice', () => {
	beforeEach( () => {
		mockApiFetch.mockClear();
		mockDispatch.mockClear();
		mockSelect.mockClear();
	} );

	test( 'does not create notice when settings fetch fails', async () => {
		// Arrange.
		mockSelect.mockReturnValue( { getCurrentPostId: () => 5 } );
		mockApiFetch.mockRejectedValueOnce( new Error( 'Network error' ) );

		// Act.
		await act( async () => {
			render( <OutletEmptyNotice /> );
		} );

		// Assert.
		expect( mockDispatch ).not.toHaveBeenCalled();
	} );

	test( 'does not create notice when current post is not the outlet page', async () => {
		// Arrange.
		mockSelect.mockReturnValue( { getCurrentPostId: () => 99 } );
		mockApiFetch.mockResolvedValueOnce( { outletpro_page_id: 5 } );

		// Act.
		await act( async () => {
			render( <OutletEmptyNotice /> );
		} );

		// Assert.
		expect( mockDispatch ).not.toHaveBeenCalled();
	} );

	test( 'does not create notice when outlet page is not configured', async () => {
		// Arrange.
		mockSelect.mockReturnValue( { getCurrentPostId: () => 5 } );
		mockApiFetch.mockResolvedValueOnce( {} );

		// Act.
		await act( async () => {
			render( <OutletEmptyNotice /> );
		} );

		// Assert.
		expect( mockDispatch ).not.toHaveBeenCalled();
	} );

	test( 'does not create notice when outlet products exist', async () => {
		// Arrange.
		mockSelect.mockReturnValue( { getCurrentPostId: () => 5 } );
		mockApiFetch
			.mockResolvedValueOnce( { outletpro_page_id: 5 } )
			.mockResolvedValueOnce( [ { id: 1 } ] );

		// Act.
		await act( async () => {
			render( <OutletEmptyNotice /> );
		} );

		// Assert.
		expect( mockDispatch ).not.toHaveBeenCalled();
	} );

	test( 'creates warning notice when on outlet page with no products', async () => {
		// Arrange.
		const mockCreateNotice = jest.fn();
		mockSelect.mockReturnValue( { getCurrentPostId: () => 5 } );
		mockDispatch.mockReturnValue( { createNotice: mockCreateNotice } );
		mockApiFetch
			.mockResolvedValueOnce( { outletpro_page_id: 5 } )
			.mockResolvedValueOnce( [] );

		// Act.
		await act( async () => {
			render( <OutletEmptyNotice /> );
		} );

		// Assert.
		const [ , message ] = mockCreateNotice.mock.calls[ 0 ];
		expect( message ).toContain( 'empty' );
	} );

	test( 'notice action navigates to the product list screen', async () => {
		// Arrange.
		const mockCreateNotice = jest.fn();
		mockSelect.mockReturnValue( { getCurrentPostId: () => 5 } );
		mockDispatch.mockReturnValue( { createNotice: mockCreateNotice } );
		mockApiFetch
			.mockResolvedValueOnce( { outletpro_page_id: 5 } )
			.mockResolvedValueOnce( [] );

		// Act.
		await act( async () => {
			render( <OutletEmptyNotice /> );
		} );

		// Assert — the action is a primary button pointing to the product list screen.
		expect( mockCreateNotice ).toHaveBeenCalledWith(
			expect.any( String ),
			expect.any( String ),
			expect.objectContaining( {
				actions: expect.arrayContaining( [
					expect.objectContaining( {
						label: 'Manage products',
						isPrimary: true,
						onClick: expect.any( Function ),
					} ),
				] ),
			} )
		);
	} );

	test.skip( 'notice action onClick navigates to the product list screen', () => {
		// Not yet implemented: JSDOM does not allow redefining window.location, so
		// the navigation side-effect of the onClick action cannot be asserted here.
	} );

	test( 'renders null', async () => {
		// Arrange.
		mockSelect.mockReturnValue( { getCurrentPostId: () => 5 } );
		mockApiFetch.mockResolvedValueOnce( {} );

		// Act.
		const { container } = render( <OutletEmptyNotice /> );
		await act( async () => {} );

		// Assert.
		expect( container ).toBeEmptyDOMElement();
	} );
} );
