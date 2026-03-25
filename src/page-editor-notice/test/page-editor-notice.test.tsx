import { render, act } from '@testing-library/react';
import { PageEditorNotice } from '../index';
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

describe( 'PageEditorNotice', () => {
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
			render( <PageEditorNotice /> );
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
			render( <PageEditorNotice /> );
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
			render( <PageEditorNotice /> );
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
			render( <PageEditorNotice /> );
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
			render( <PageEditorNotice /> );
		} );

		// Assert.
		expect( mockCreateNotice ).toHaveBeenCalledWith(
			'warning',
			'The clearance section has no products. Include products to display them on this page.',
			expect.objectContaining( {
				id: 'wc-clearance-no-products',
				isDismissible: false,
			} )
		);
	} );

	test( 'notice action links to the product list screen', async () => {
		// Arrange.
		const mockCreateNotice = jest.fn();
		mockSelect.mockReturnValue( { getCurrentPostId: () => 5 } );
		mockDispatch.mockReturnValue( { createNotice: mockCreateNotice } );
		mockApiFetch
			.mockResolvedValueOnce( { wc_clearance_page_id: 5 } )
			.mockResolvedValueOnce( [] );

		// Act.
		await act( async () => {
			render( <PageEditorNotice /> );
		} );

		// Assert.
		expect( mockCreateNotice ).toHaveBeenCalledWith(
			expect.any( String ),
			expect.any( String ),
			expect.objectContaining( {
				actions: expect.arrayContaining( [
					expect.objectContaining( {
						url: expect.stringContaining(
							'edit.php?post_type=product'
						),
					} ),
				] ),
			} )
		);
	} );

	test( 'renders null', async () => {
		// Arrange.
		mockSelect.mockReturnValue( { getCurrentPostId: () => 5 } );
		mockApiFetch.mockResolvedValueOnce( {} );

		// Act.
		const { container } = await act( async () =>
			render( <PageEditorNotice /> )
		);

		// Assert.
		expect( container ).toBeEmptyDOMElement();
	} );
} );
