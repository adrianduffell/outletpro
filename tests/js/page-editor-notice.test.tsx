import { render, act } from '@testing-library/react';
import { PageEditorNotice } from '../../src/page-editor-notice';
import apiFetch from '@wordpress/api-fetch';

jest.mock( '@wordpress/plugins', () => ( {
	registerPlugin: jest.fn(),
} ) );

const mockCreateNotice = jest.fn();
const mockGetCurrentPostId = jest.fn();

jest.mock( '@wordpress/data', () => ( {
	dispatch: jest.fn( () => ( {
		createNotice: mockCreateNotice,
	} ) ),
	select: jest.fn( () => ( {
		getCurrentPostId: mockGetCurrentPostId,
	} ) ),
} ) );

jest.mock( '@wordpress/element', () => ( {
	useEffect: ( callback: () => void ) => callback(),
} ) );

jest.mock( '@wordpress/api-fetch', () => jest.fn() );

// Typed reference to the jest.fn() created by the factory above.
const mockApiFetch = apiFetch as unknown as jest.Mock;

describe( 'PageEditorNotice', () => {
	beforeEach( () => {
		mockCreateNotice.mockClear();
		mockApiFetch.mockClear();
		mockGetCurrentPostId.mockReturnValue( 5 );
	} );

	test( 'does not create notice when settings fetch fails', async () => {
		// Arrange.
		mockApiFetch.mockRejectedValueOnce( new Error( 'Network error' ) );

		// Act.
		await act( async () => {
			render( <PageEditorNotice /> );
		} );

		// Assert.
		expect( mockCreateNotice ).not.toHaveBeenCalled();
	} );

	test( 'does not create notice when current post is not the clearance page', async () => {
		// Arrange.
		mockGetCurrentPostId.mockReturnValue( 99 );
		mockApiFetch.mockResolvedValueOnce( { wc_clearance_page_id: 5 } );

		// Act.
		await act( async () => {
			render( <PageEditorNotice /> );
		} );

		// Assert.
		expect( mockCreateNotice ).not.toHaveBeenCalled();
	} );

	test( 'does not create notice when clearance page is not configured', async () => {
		// Arrange.
		mockApiFetch.mockResolvedValueOnce( {} );

		// Act.
		await act( async () => {
			render( <PageEditorNotice /> );
		} );

		// Assert.
		expect( mockCreateNotice ).not.toHaveBeenCalled();
	} );

	test( 'does not create notice when clearance products exist', async () => {
		// Arrange.
		mockGetCurrentPostId.mockReturnValue( 5 );
		mockApiFetch
			.mockResolvedValueOnce( { wc_clearance_page_id: 5 } )
			.mockResolvedValueOnce( [ { id: 1 } ] );

		// Act.
		await act( async () => {
			render( <PageEditorNotice /> );
		} );

		// Assert.
		expect( mockCreateNotice ).not.toHaveBeenCalled();
	} );

	test( 'creates warning notice when on clearance page with no products', async () => {
		// Arrange.
		mockGetCurrentPostId.mockReturnValue( 5 );
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
		mockGetCurrentPostId.mockReturnValue( 5 );
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
		mockApiFetch.mockResolvedValueOnce( {} );

		// Act.
		let result!: ReturnType< typeof render >;
		await act( async () => {
			result = render( <PageEditorNotice /> );
		} );

		// Assert.
		expect( result.container ).toBeEmptyDOMElement();
	} );
} );
