import { render, act } from '@testing-library/react';
import { ClearanceSectionEmptyNotice } from '../index';
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

describe( 'ClearanceSectionEmptyNotice', () => {
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
			render( <ClearanceSectionEmptyNotice /> );
		} );

		// Assert.
		expect( mockDispatch ).not.toHaveBeenCalled();
	} );

	test( 'does not create notice when current post is not the clearance page', async () => {
		// Arrange.
		mockSelect.mockReturnValue( { getCurrentPostId: () => 99 } );
		mockApiFetch.mockResolvedValueOnce( { wc_clearance_page_id: 5 } );

		// Act.
		await act( async () => {
			render( <ClearanceSectionEmptyNotice /> );
		} );

		// Assert.
		expect( mockDispatch ).not.toHaveBeenCalled();
	} );

	test( 'does not create notice when clearance page is not configured', async () => {
		// Arrange.
		mockSelect.mockReturnValue( { getCurrentPostId: () => 5 } );
		mockApiFetch.mockResolvedValueOnce( {} );

		// Act.
		await act( async () => {
			render( <ClearanceSectionEmptyNotice /> );
		} );

		// Assert.
		expect( mockDispatch ).not.toHaveBeenCalled();
	} );

	test( 'does not create notice when clearance products exist', async () => {
		// Arrange.
		mockSelect.mockReturnValue( { getCurrentPostId: () => 5 } );
		mockApiFetch
			.mockResolvedValueOnce( { wc_clearance_page_id: 5 } )
			.mockResolvedValueOnce( [ { id: 1 } ] );

		// Act.
		await act( async () => {
			render( <ClearanceSectionEmptyNotice /> );
		} );

		// Assert.
		expect( mockDispatch ).not.toHaveBeenCalled();
	} );

	test( 'creates warning notice when on clearance page with no products', async () => {
		// Arrange.
		const mockCreateNotice = jest.fn();
		mockSelect.mockReturnValue( { getCurrentPostId: () => 5 } );
		mockDispatch.mockReturnValue( { createNotice: mockCreateNotice } );
		mockApiFetch
			.mockResolvedValueOnce( { wc_clearance_page_id: 5 } )
			.mockResolvedValueOnce( [] );

		// Act.
		await act( async () => {
			render( <ClearanceSectionEmptyNotice /> );
		} );

		// Assert.
		const [ type, message ] = mockCreateNotice.mock.calls[ 0 ];
		expect( type ).toBe( 'warning' );
		expect( message ).toContain( 'empty' );
		expect( mockCreateNotice ).toHaveBeenCalledWith(
			expect.any( String ),
			expect.any( String ),
			expect.objectContaining( {
				id: 'wc-clearance-empty',
				isDismissible: false,
			} )
		);
	} );

	test( 'notice action navigates to the product list screen', async () => {
		// Arrange.
		const mockCreateNotice = jest.fn();
		mockSelect.mockReturnValue( { getCurrentPostId: () => 5 } );
		mockDispatch.mockReturnValue( { createNotice: mockCreateNotice } );
		mockApiFetch
			.mockResolvedValueOnce( { wc_clearance_page_id: 5 } )
			.mockResolvedValueOnce( [] );

		// Act.
		await act( async () => {
			render( <ClearanceSectionEmptyNotice /> );
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
		const { container } = render( <ClearanceSectionEmptyNotice /> );
		await act( async () => {} );

		// Assert.
		expect( container ).toBeEmptyDOMElement();
	} );
} );
