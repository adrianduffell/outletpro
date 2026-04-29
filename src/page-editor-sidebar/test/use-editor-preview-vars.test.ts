import { renderHook } from '@testing-library/react';
import { buildCss, useEditorPreviewVars } from '../use-editor-preview-vars';

jest.mock( '@wordpress/element', () => ( {
	...jest.requireActual( '@wordpress/element' ),
} ) );

describe( 'buildCss', () => {
	test( 'returns empty string when all values are empty', () => {
		// Arrange.
		const vars = {};

		// Act.
		const result = buildCss( vars );

		// Assert.
		expect( result ).toBe( '' );
	} );

	test( 'returns empty string when all values are undefined', () => {
		// Arrange.
		const vars = {
			label: undefined,
			textColor: undefined,
			bgColor: undefined,
		};

		// Act.
		const result = buildCss( vars );

		// Assert.
		expect( result ).toBe( '' );
	} );

	test( 'returns empty string when all values are empty strings', () => {
		// Arrange.
		const vars = {
			label: '',
			textColor: '',
			bgColor: '',
		};

		// Act.
		const result = buildCss( vars );

		// Assert.
		expect( result ).toBe( '' );
	} );

	test( 'encodes label with JSON.stringify', () => {
		// Arrange.
		const vars = { label: 'Clearance' };

		// Act.
		const result = buildCss( vars );

		// Assert.
		expect( result ).toContain(
			'--wc-clearance-badge-label: "Clearance";'
		);
	} );

	test( 'encodes label with special characters using JSON.stringify', () => {
		// Arrange.
		const vars = { label: 'It\'s a "sale"' };

		// Act.
		const result = buildCss( vars );

		// Assert.
		expect( result ).toContain(
			`--wc-clearance-badge-label: ${ JSON.stringify(
				'It\'s a "sale"'
			) };`
		);
	} );

	test( 'outputs text color as raw CSS value', () => {
		// Arrange.
		const vars = { textColor: '#ff0000' };

		// Act.
		const result = buildCss( vars );

		// Assert.
		expect( result ).toContain(
			'--wc-clearance-badge-text-color: #ff0000;'
		);
	} );

	test( 'outputs background color as raw CSS value', () => {
		// Arrange.
		const vars = { bgColor: '#ffee85' };

		// Act.
		const result = buildCss( vars );

		// Assert.
		expect( result ).toContain( '--wc-clearance-badge-bg-color: #ffee85;' );
	} );

	test( 'outputs font size as raw CSS value', () => {
		// Arrange.
		const vars = { fontSize: '1rem' };

		// Act.
		const result = buildCss( vars );

		// Assert.
		expect( result ).toContain( '--wc-clearance-badge-font-size: 1rem;' );
	} );

	test( 'outputs font weight as raw CSS value', () => {
		// Arrange.
		const vars = { fontWeight: '700' };

		// Act.
		const result = buildCss( vars );

		// Assert.
		expect( result ).toContain( '--wc-clearance-badge-font-weight: 700;' );
	} );

	test( 'outputs border color as raw CSS value', () => {
		// Arrange.
		const vars = { borderColor: '#000' };

		// Act.
		const result = buildCss( vars );

		// Assert.
		expect( result ).toContain(
			'--wc-clearance-badge-border-color: #000;'
		);
	} );

	test( 'outputs border style as raw CSS value', () => {
		// Arrange.
		const vars = { borderStyle: 'solid' };

		// Act.
		const result = buildCss( vars );

		// Assert.
		expect( result ).toContain(
			'--wc-clearance-badge-border-style: solid;'
		);
	} );

	test( 'outputs border width as raw CSS value', () => {
		// Arrange.
		const vars = { borderWidth: '2px' };

		// Act.
		const result = buildCss( vars );

		// Assert.
		expect( result ).toContain( '--wc-clearance-badge-border-width: 2px;' );
	} );

	test( 'outputs border radius as raw CSS value', () => {
		// Arrange.
		const vars = { borderRadius: '4px' };

		// Act.
		const result = buildCss( vars );

		// Assert.
		expect( result ).toContain(
			'--wc-clearance-badge-border-radius: 4px;'
		);
	} );

	test( 'outputs padding sides as raw CSS values', () => {
		// Arrange.
		const vars = {
			paddingTop: '4px',
			paddingRight: '8px',
			paddingBottom: '4px',
			paddingLeft: '8px',
		};

		// Act.
		const result = buildCss( vars );

		// Assert.
		expect( result ).toContain( '--wc-clearance-badge-padding-top: 4px;' );
		expect( result ).toContain(
			'--wc-clearance-badge-padding-right: 8px;'
		);
		expect( result ).toContain(
			'--wc-clearance-badge-padding-bottom: 4px;'
		);
		expect( result ).toContain( '--wc-clearance-badge-padding-left: 8px;' );
	} );

	test( 'omits empty string values', () => {
		// Arrange.
		const vars = { label: 'Sale', textColor: '' };

		// Act.
		const result = buildCss( vars );

		// Assert.
		expect( result ).not.toContain( '--wc-clearance-badge-text-color' );
	} );

	test( 'omits undefined values', () => {
		// Arrange.
		const vars = { label: 'Sale', bgColor: undefined };

		// Act.
		const result = buildCss( vars );

		// Assert.
		expect( result ).not.toContain( '--wc-clearance-badge-bg-color' );
	} );

	test( 'wraps output in :root block', () => {
		// Arrange.
		const vars = { label: 'Sale' };

		// Act.
		const result = buildCss( vars );

		// Assert.
		expect( result ).toMatch( /^:root \{/ );
		expect( result ).toMatch( /\}$/ );
	} );

	test( 'outputs multiple variables in a single :root block', () => {
		// Arrange.
		const vars = { label: 'Sale', textColor: '#222', bgColor: '#ffee85' };

		// Act.
		const result = buildCss( vars );

		// Assert.
		expect( result ).toContain( '--wc-clearance-badge-label' );
		expect( result ).toContain( '--wc-clearance-badge-text-color' );
		expect( result ).toContain( '--wc-clearance-badge-bg-color' );
		expect( ( result.match( /:root/g ) || [] ).length ).toBe( 1 );
	} );
} );

describe( 'useEditorPreviewVars', () => {
	beforeEach( () => {
		document.getElementById( 'wc-clearance-preview-vars' )?.remove();
	} );

	afterEach( () => {
		document.getElementById( 'wc-clearance-preview-vars' )?.remove();
	} );

	test( 'injects a style tag into the document when values are provided', () => {
		// Arrange.
		const vars = { label: 'Sale', textColor: '#222' };

		// Act.
		renderHook( () => useEditorPreviewVars( vars ) );

		// Assert.
		const el = document.getElementById( 'wc-clearance-preview-vars' );
		expect( el ).not.toBeNull();
		expect( el?.tagName ).toBe( 'STYLE' );
	} );

	test( 'style tag contains generated CSS', () => {
		// Arrange.
		const vars = { label: 'Sale' };

		// Act.
		renderHook( () => useEditorPreviewVars( vars ) );

		// Assert.
		const el = document.getElementById( 'wc-clearance-preview-vars' );
		expect( el?.textContent ).toContain(
			'--wc-clearance-badge-label: "Sale";'
		);
	} );

	test( 'does not inject a style tag when all values are empty', () => {
		// Arrange.
		const vars = { label: '', textColor: '' };

		// Act.
		renderHook( () => useEditorPreviewVars( vars ) );

		// Assert.
		expect(
			document.getElementById( 'wc-clearance-preview-vars' )
		).toBeNull();
	} );

	test( 'removes style tag on unmount', () => {
		// Arrange.
		const vars = { label: 'Sale' };
		const { unmount } = renderHook( () => useEditorPreviewVars( vars ) );

		// Act.
		unmount();

		// Assert.
		expect(
			document.getElementById( 'wc-clearance-preview-vars' )
		).toBeNull();
	} );

	test( 'updates style tag when CSS changes', () => {
		// Arrange.
		const { rerender } = renderHook(
			( props: { label?: string } ) => useEditorPreviewVars( props ),
			{ initialProps: { label: 'Sale' } }
		);

		// Act.
		rerender( { label: 'Discounts' } );

		// Assert.
		const el = document.getElementById( 'wc-clearance-preview-vars' );
		expect( el?.textContent ).toContain(
			'--wc-clearance-badge-label: "Discounts";'
		);
	} );

	test( 'removes style tag when all values become empty on update', () => {
		// Arrange.
		const { rerender } = renderHook(
			( props: { label?: string } ) => useEditorPreviewVars( props ),
			{ initialProps: { label: 'Sale' } }
		);

		// Act.
		rerender( { label: '' } );

		// Assert.
		expect(
			document.getElementById( 'wc-clearance-preview-vars' )
		).toBeNull();
	} );

	test( 'injects CSS into iframe contentDocument when present', () => {
		// Arrange.
		const iframe = document.createElement( 'iframe' );
		iframe.setAttribute( 'name', 'editor-canvas' );
		document.body.appendChild( iframe );

		const vars = { label: 'Sale' };

		// Act.
		renderHook( () => useEditorPreviewVars( vars ) );

		// Assert.
		const el = iframe.contentDocument?.getElementById(
			'wc-clearance-preview-vars'
		);
		expect( el ).not.toBeNull();
		expect( el?.textContent ).toContain(
			'--wc-clearance-badge-label: "Sale";'
		);

		// Cleanup.
		iframe.remove();
	} );

	test( 'removes CSS from iframe on unmount', () => {
		// Arrange.
		const iframe = document.createElement( 'iframe' );
		iframe.setAttribute( 'name', 'editor-canvas' );
		document.body.appendChild( iframe );

		const vars = { label: 'Sale' };
		const { unmount } = renderHook( () => useEditorPreviewVars( vars ) );

		// Act.
		unmount();

		// Assert.
		expect(
			iframe.contentDocument?.getElementById(
				'wc-clearance-preview-vars'
			)
		).toBeNull();

		// Cleanup.
		iframe.remove();
	} );

	test( 'does not attach duplicate load listeners when the same iframe is seen twice', () => {
		// Arrange.
		const iframe = document.createElement( 'iframe' );
		iframe.setAttribute( 'name', 'editor-canvas' );
		document.body.appendChild( iframe );

		const addSpy = jest.spyOn( iframe, 'addEventListener' );

		// Act.
		renderHook( () => useEditorPreviewVars( { label: 'Sale' } ) );

		// Simulate observer firing for the same iframe a second time.
		const loadCalls = addSpy.mock.calls.filter(
			( [ event ] ) => event === 'load'
		).length;

		// Assert — load listener added only once.
		expect( loadCalls ).toBe( 1 );

		// Cleanup.
		iframe.remove();
		addSpy.mockRestore();
	} );

	test( 'removes load listener from previous iframe when iframe changes', () => {
		// Arrange.
		const iframe1 = document.createElement( 'iframe' );
		iframe1.setAttribute( 'name', 'editor-canvas' );
		document.body.appendChild( iframe1 );

		renderHook( () => useEditorPreviewVars( { label: 'Sale' } ) );

		const removeSpy = jest.spyOn( iframe1, 'removeEventListener' );

		// Act — replace the iframe so the observer fires for iframe2.
		iframe1.remove();
		const iframe2 = document.createElement( 'iframe' );
		iframe2.setAttribute( 'name', 'editor-canvas' );
		document.body.appendChild( iframe2 );

		// Wait one microtask tick so MutationObserver callbacks fire.
		return new Promise< void >( ( resolve ) => {
			setTimeout( () => {
				// Assert — load listener removed from the previous iframe.
				const removedLoad = removeSpy.mock.calls.some(
					( [ event ] ) => event === 'load'
				);
				expect( removedLoad ).toBe( true );

				// Cleanup.
				iframe2.remove();
				removeSpy.mockRestore();
				resolve();
			}, 0 );
		} );
	} );
} );
