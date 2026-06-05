import { buildPreviewStyles } from '../index';

describe( 'buildPreviewStyles', () => {
	const vars = {
		bgColor: '--outletpro-badge-bg-color',
		textColor: '--outletpro-badge-text-color',
		fontWeight: '--outletpro-badge-font-weight',
		borderColor: '--outletpro-badge-border-color',
		borderStyle: '--outletpro-badge-border-style',
		borderWidth: '--outletpro-badge-border-width',
		borderRadius: '--outletpro-badge-border-radius',
		scale: '--outletpro-badge-scale',
		density: '--outletpro-badge-density',
	};

	test( 'wraps declarations in a :root rule', () => {
		const result = buildPreviewStyles( {} );

		expect( result ).toMatch( /^:root \{ .+ \}$/ );
	} );

	test( 'serializes label as a JSON string', () => {
		const result = buildPreviewStyles( { label: 'Sale' } );

		expect( result ).toContain( '--outletpro-badge-label: "Sale"' );
	} );

	test( 'escapes special characters in label', () => {
		const resultQuote = buildPreviewStyles( { label: 'Bob"s Sale' } );
		const resultNewline = buildPreviewStyles( {
			label: 'A "quote"\nand newline',
		} );

		expect( resultQuote ).toContain(
			'--outletpro-badge-label: "Bob\\"s Sale"'
		);
		expect( resultNewline ).toContain(
			'--outletpro-badge-label: "A \\"quote\\"\\nand newline"'
		);
	} );

	test( 'uses none for label when undefined', () => {
		const result = buildPreviewStyles( {} );

		expect( result ).toContain( '--outletpro-badge-label: none' );
	} );

	test( 'uses none for label when empty string', () => {
		const result = buildPreviewStyles( { label: '' } );

		expect( result ).toContain( '--outletpro-badge-label: none' );
	} );

	test.each( [
		[ 'bgColor', '#ff0000' ],
		[ 'textColor', '#ffffff' ],
		[ 'fontWeight', '700' ],
		[ 'borderColor', '#cccccc' ],
		[ 'borderStyle', 'solid' ],
		[ 'borderWidth', '1px' ],
		[ 'borderRadius', '4px' ],
		[ 'scale', 120 ],
		[ 'density', 60 ],
	] as const )( 'includes %s CSS var', ( key, value ) => {
		const result = buildPreviewStyles( { [ key ]: value } );

		expect( result ).toContain( `${ vars[ key ] }: ${ value }` );
	} );

	test( 'includes all CSS vars when all settings are provided', () => {
		const result = buildPreviewStyles( {
			label: 'Sale',
			bgColor: '#ff0000',
			textColor: '#ffffff',
			fontWeight: '700',
			borderColor: '#cccccc',
			borderStyle: 'solid',
			borderWidth: '1px',
			borderRadius: '4px',
			scale: 120,
			density: 60,
		} );

		expect( result ).toContain( '--outletpro-badge-label: "Sale"' );
		expect( result ).toContain( '--outletpro-badge-bg-color: #ff0000' );
		expect( result ).toContain( '--outletpro-badge-text-color: #ffffff' );
		expect( result ).toContain( '--outletpro-badge-font-weight: 700' );
		expect( result ).toContain( '--outletpro-badge-border-color: #cccccc' );
		expect( result ).toContain( '--outletpro-badge-border-style: solid' );
		expect( result ).toContain( '--outletpro-badge-border-width: 1px' );
		expect( result ).toContain( '--outletpro-badge-border-radius: 4px' );
		expect( result ).toContain( '--outletpro-badge-scale: 120' );
		expect( result ).toContain( '--outletpro-badge-density: 60' );
	} );

	test.each(
		Object.entries( vars ) as Array< [ keyof typeof vars, string ] >
	)( 'outputs unset for undefined %s', ( key, cssVar ) => {
		const result = buildPreviewStyles( { [ key ]: undefined } );

		expect( result ).toContain( `${ cssVar }: unset` );
	} );

	test( 'outputs unset for empty or undefined CSS values', () => {
		const result = buildPreviewStyles( {
			bgColor: '',
			borderWidth: '0',
			borderRadius: '0',
			scale: undefined,
			density: undefined,
		} );

		expect( result ).toContain( '--outletpro-badge-bg-color: unset' );
		expect( result ).toContain( '--outletpro-badge-border-width: 0' );
		expect( result ).toContain( '--outletpro-badge-border-radius: 0' );
		expect( result ).toContain( '--outletpro-badge-scale: unset' );
		expect( result ).toContain( '--outletpro-badge-density: unset' );
	} );

	test( 'outputs unset for undefined or empty alongside defined values', () => {
		const result = buildPreviewStyles( {
			bgColor: '#000',
			textColor: undefined,
		} );

		expect( result ).toContain( '--outletpro-badge-bg-color: #000' );
		expect( result ).toContain( '--outletpro-badge-text-color: unset' );
	} );

	test( 'outputs 0 for zero scale', () => {
		const result = buildPreviewStyles( {
			scale: 0,
		} );

		expect( result ).toContain( '--outletpro-badge-scale: 0' );
	} );

	test( 'outputs 0 for zero density', () => {
		const result = buildPreviewStyles( {
			density: 0,
		} );

		expect( result ).toContain( '--outletpro-badge-density: 0' );
	} );

	test( 'joins declarations with semicolons', () => {
		const result = buildPreviewStyles( {
			label: 'Sale',
			bgColor: '#ff0000',
		} );

		expect( result ).toContain( '--outletpro-badge-label: "Sale";' );

		expect( result ).toContain( '--outletpro-badge-bg-color: #ff0000;' );
	} );

	test( 'does not append a trailing semicolon before the closing brace', () => {
		const result = buildPreviewStyles( {} );

		expect( result ).not.toMatch( /;\s+\}$/ );
	} );
} );
