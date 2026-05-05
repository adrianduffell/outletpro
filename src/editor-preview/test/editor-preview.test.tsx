import { render } from '@testing-library/react';
import { useEntityProp } from '@wordpress/core-data';
import EditorPreview from '../index';

jest.mock( '@wordpress/core-data', () => ( {
	useEntityProp: jest.fn(),
} ) );

const mockUseEntityProp = useEntityProp as jest.Mock;

function setupEntityPropMock(
	overrides: Record< string, [ string | undefined, jest.Mock ] > = {}
) {
	mockUseEntityProp.mockImplementation(
		( _kind: string, _name: string, key: string ) => {
			if ( overrides[ key ] ) {
				return [ ...overrides[ key ], undefined ];
			}

			return [ undefined, jest.fn(), undefined ];
		}
	);
}

describe( 'EditorPreview', () => {
	afterEach( () => {
		jest.clearAllMocks();
	} );

	test( 'renders the preview style tag into the document head', () => {
		// Arrange.
		setupEntityPropMock();

		// Act.
		render( <EditorPreview /> );

		// Assert.
		expect(
			document.head.querySelector( '#wc-clearance-preview-vars' )
		).not.toBeNull();
	} );

	test( 'renders CSS vars from settings', () => {
		// Arrange.
		setupEntityPropMock( {
			wc_clearance_badge_label: [ 'Sale', jest.fn() ],
			wc_clearance_badge_bg_color: [ '#ff0000', jest.fn() ],
			wc_clearance_badge_text_color: [ '#ffffff', jest.fn() ],
		} );

		// Act.
		render( <EditorPreview /> );

		// Assert.
		const styleEl = document.head.querySelector(
			'#wc-clearance-preview-vars'
		);

		expect( styleEl?.textContent ).toContain(
			'--wc-clearance-badge-label: "Sale"'
		);
		expect( styleEl?.textContent ).toContain(
			'--wc-clearance-badge-bg-color: #ff0000'
		);
		expect( styleEl?.textContent ).toContain(
			'--wc-clearance-badge-text-color: #ffffff'
		);
	} );

	test( 'falls back unset for undefined style values', () => {
		// Arrange.
		setupEntityPropMock();

		// Act.
		render( <EditorPreview /> );

		// Assert.
		const styleEl = document.head.querySelector(
			'#wc-clearance-preview-vars'
		);

		expect( styleEl?.textContent ).toContain(
			'--wc-clearance-badge-bg-color: unset'
		);
		expect( styleEl?.textContent ).toContain(
			'--wc-clearance-badge-text-color: unset'
		);
	} );
} );
