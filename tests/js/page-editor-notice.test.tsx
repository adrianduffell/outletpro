import { render } from '@testing-library/react';
import { PageEditorNotice } from '../../src/page-editor-notice';

jest.mock( '@wordpress/plugins', () => ( {
	registerPlugin: jest.fn(),
} ) );

const mockCreateNotice = jest.fn();
jest.mock( '@wordpress/data', () => ( {
	dispatch: jest.fn( () => ( {
		createNotice: mockCreateNotice,
	} ) ),
} ) );

jest.mock( '@wordpress/element', () => ( {
	useEffect: ( callback: () => void ) => callback(),
} ) );

describe( 'PageEditorNotice', () => {
	beforeEach( () => {
		mockCreateNotice.mockClear();
		delete window.wcClearanceEditorData;
	} );

	test( 'does not create a notice when noProductsNotice is false', () => {
		// Arrange.
		window.wcClearanceEditorData = {
			noProductsNotice: false,
		};

		// Act.
		render( <PageEditorNotice /> );

		// Assert.
		expect( mockCreateNotice ).not.toHaveBeenCalled();
	} );

	test( 'does not create a notice when wcClearanceEditorData is absent', () => {
		// Arrange: window.wcClearanceEditorData is undefined (deleted in beforeEach).

		// Act.
		render( <PageEditorNotice /> );

		// Assert.
		expect( mockCreateNotice ).not.toHaveBeenCalled();
	} );

	test( 'creates a warning notice when noProductsNotice is true', () => {
		// Arrange.
		window.wcClearanceEditorData = {
			noProductsNotice: true,
		};

		// Act.
		render( <PageEditorNotice /> );

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

	test( 'notice action links to the product list screen', () => {
		// Arrange.
		window.wcClearanceEditorData = {
			noProductsNotice: true,
		};

		// Act.
		render( <PageEditorNotice /> );

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

	test( 'renders null', () => {
		// Arrange.
		window.wcClearanceEditorData = {
			noProductsNotice: false,
		};

		// Act.
		const { container } = render( <PageEditorNotice /> );

		// Assert.
		expect( container ).toBeEmptyDOMElement();
	} );
} );
