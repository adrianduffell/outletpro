import { render, act } from '@testing-library/react';
import { useEntityProp } from '@wordpress/core-data';
import EditorPreview from '../index';

jest.mock( '@wordpress/core-data', () => ( {
	useEntityProp: jest.fn(),
} ) );

const mockUseEntityProp = useEntityProp as jest.Mock;

function setupEntityPropMock(
	overrides: Record< string, [ string | number | undefined, jest.Mock ] > = {}
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
			document.head.querySelector( '#wc-outlet-preview-vars' )
		).not.toBeNull();
	} );

	test( 'renders CSS vars from settings', () => {
		// Arrange.
		setupEntityPropMock( {
			wc_outlet_badge_label: [ 'Sale', jest.fn() ],
			wc_outlet_badge_bg_color: [ '#ff0000', jest.fn() ],
			wc_outlet_badge_text_color: [ '#ffffff', jest.fn() ],
			wc_outlet_badge_scale: [ 140, jest.fn() ],
			wc_outlet_badge_density: [ 80, jest.fn() ],
		} );

		// Act.
		render( <EditorPreview /> );

		// Assert.
		const styleEl = document.head.querySelector(
			'#wc-outlet-preview-vars'
		);

		expect( styleEl?.textContent ).toContain(
			'--wc-outlet-badge-label: "Sale"'
		);
		expect( styleEl?.textContent ).toContain(
			'--wc-outlet-badge-bg-color: #ff0000'
		);
		expect( styleEl?.textContent ).toContain(
			'--wc-outlet-badge-text-color: #ffffff'
		);
		expect( styleEl?.textContent ).toContain(
			'--wc-outlet-badge-scale: 140'
		);
		expect( styleEl?.textContent ).toContain(
			'--wc-outlet-badge-density: 80'
		);
	} );

	test( 'falls back unset for undefined style values', () => {
		// Arrange.
		setupEntityPropMock();

		// Act.
		render( <EditorPreview /> );

		// Assert.
		const styleEl = document.head.querySelector(
			'#wc-outlet-preview-vars'
		);

		expect( styleEl?.textContent ).toContain(
			'--wc-outlet-badge-bg-color: unset'
		);
		expect( styleEl?.textContent ).toContain(
			'--wc-outlet-badge-text-color: unset'
		);
	} );

	test( 'falls back to unset for empty string style values', () => {
		// Arrange.
		setupEntityPropMock( {
			wc_outlet_badge_bg_color: [ '', jest.fn() ],
			wc_outlet_badge_border_style: [ '', jest.fn() ],
			wc_outlet_badge_font_weight: [ '', jest.fn() ],
		} );

		// Act.
		render( <EditorPreview /> );

		// Assert.
		const styleEl = document.head.querySelector(
			'#wc-outlet-preview-vars'
		);

		expect( styleEl?.textContent ).toContain(
			'--wc-outlet-badge-bg-color: unset'
		);
		expect( styleEl?.textContent ).toContain(
			'--wc-outlet-badge-border-style: unset'
		);
		expect( styleEl?.textContent ).toContain(
			'--wc-outlet-badge-font-weight: unset'
		);
	} );

	describe( 'iframe portal targeting', () => {
		test( 'portals style tag into canvas document head when ready event fires', () => {
			// Arrange.
			const canvasDoc = document.implementation.createHTMLDocument();

			setupEntityPropMock();
			render( <EditorPreview /> );

			// Act.
			act( () => {
				window.dispatchEvent(
					new CustomEvent( 'wc-outlet-canvas-ready', {
						detail: {
							document: canvasDoc,
						},
					} )
				);
			} );

			// Assert.
			expect(
				canvasDoc.head.querySelector( '#wc-outlet-preview-vars' )
			).not.toBeNull();
			expect(
				document.head.querySelector( '#wc-outlet-preview-vars' )
			).toBeNull();
		} );

		test( 're-portals style tag to replacement canvas document head when ready event fires again', () => {
			// Arrange.
			const canvasDoc1 = document.implementation.createHTMLDocument();
			const canvasDoc2 = document.implementation.createHTMLDocument();

			setupEntityPropMock();
			render( <EditorPreview /> );

			act( () => {
				window.dispatchEvent(
					new CustomEvent( 'wc-outlet-canvas-ready', {
						detail: {
							document: canvasDoc1,
						},
					} )
				);
			} );

			expect(
				canvasDoc1.head.querySelector( '#wc-outlet-preview-vars' )
			).not.toBeNull();

			// Act.
			act( () => {
				window.dispatchEvent(
					new CustomEvent( 'wc-outlet-canvas-ready', {
						detail: {
							document: canvasDoc2,
						},
					} )
				);
			} );

			// Assert.
			expect(
				canvasDoc2.head.querySelector( '#wc-outlet-preview-vars' )
			).not.toBeNull();
			expect(
				canvasDoc1.head.querySelector( '#wc-outlet-preview-vars' )
			).toBeNull();
		} );
	} );
} );
