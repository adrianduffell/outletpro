import { render, act } from '@testing-library/react';
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

	describe( 'iframe portal targeting', () => {
		test( 'portals style tag into iframe document head when editor-canvas iframe appears', () => {
			// Arrange.
			const originalMutationObserver = global.MutationObserver;
			let observerCallback!: MutationCallback;
			global.MutationObserver = jest
				.fn()
				.mockImplementation( ( cb: MutationCallback ) => {
					observerCallback = cb;
					return { observe: jest.fn(), disconnect: jest.fn() };
				} ) as unknown as typeof MutationObserver;

			const iframeDoc = document.implementation.createHTMLDocument();
			const iframe = document.createElement( 'iframe' );
			iframe.setAttribute( 'name', 'editor-canvas' );
			Object.defineProperty( iframe, 'contentDocument', {
				configurable: true,
				get: () => iframeDoc,
			} );

			setupEntityPropMock();
			render( <EditorPreview /> );
			document.body.appendChild( iframe );

			// Act.
			act( () => {
				observerCallback( [], {} as MutationObserver );
			} );

			// Assert.
			expect(
				iframeDoc.head.querySelector( '#wc-clearance-preview-vars' )
			).not.toBeNull();

			global.MutationObserver = originalMutationObserver;
			iframe.remove();
		} );

		test( 're-portals style tag to replacement iframe document head', () => {
			// Arrange.
			const originalMutationObserver = global.MutationObserver;
			let observerCallback!: MutationCallback;
			global.MutationObserver = jest
				.fn()
				.mockImplementation( ( cb: MutationCallback ) => {
					observerCallback = cb;
					return { observe: jest.fn(), disconnect: jest.fn() };
				} ) as unknown as typeof MutationObserver;

			const iframeDoc1 = document.implementation.createHTMLDocument();
			const iframe1 = document.createElement( 'iframe' );
			iframe1.setAttribute( 'name', 'editor-canvas' );
			Object.defineProperty( iframe1, 'contentDocument', {
				configurable: true,
				get: () => iframeDoc1,
			} );

			const iframeDoc2 = document.implementation.createHTMLDocument();
			const iframe2 = document.createElement( 'iframe' );
			iframe2.setAttribute( 'name', 'editor-canvas' );
			Object.defineProperty( iframe2, 'contentDocument', {
				configurable: true,
				get: () => iframeDoc2,
			} );

			setupEntityPropMock();
			render( <EditorPreview /> );

			// Establish the first iframe as the initial portal target.
			document.body.appendChild( iframe1 );
			act( () => {
				observerCallback( [], {} as MutationObserver );
			} );

			expect(
				iframeDoc1.head.querySelector( '#wc-clearance-preview-vars' )
			).not.toBeNull();

			// Act: replace the first iframe with a second.
			document.body.removeChild( iframe1 );
			document.body.appendChild( iframe2 );
			act( () => {
				observerCallback( [], {} as MutationObserver );
			} );

			// Assert.
			expect(
				iframeDoc2.head.querySelector( '#wc-clearance-preview-vars' )
			).not.toBeNull();

			global.MutationObserver = originalMutationObserver;
			iframe2.remove();
		} );
	} );
} );
