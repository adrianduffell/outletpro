import { buildPreviewStyles } from '../index';

describe( 'buildPreviewStyles', () => {
	const vars = {
		bgColor: '--wc-clearance-badge-bg-color',
		textColor: '--wc-clearance-badge-text-color',
		fontSize: '--wc-clearance-badge-font-size',
		fontWeight: '--wc-clearance-badge-font-weight',
		borderColor: '--wc-clearance-badge-border-color',
		borderStyle: '--wc-clearance-badge-border-style',
		borderWidth: '--wc-clearance-badge-border-width',
		borderRadius: '--wc-clearance-badge-border-radius',
		paddingTop: '--wc-clearance-badge-padding-top',
		paddingRight: '--wc-clearance-badge-padding-right',
		paddingBottom: '--wc-clearance-badge-padding-bottom',
		paddingLeft: '--wc-clearance-badge-padding-left',
	};

	test( 'wraps declarations in a :root rule', () => {
		const result = buildPreviewStyles( {} );

		expect( result ).toMatch( /^:root \{ .+ \}$/ );
	} );

	test( 'serializes label as a JSON string', () => {
		const result = buildPreviewStyles( { label: 'Sale' } );

		expect( result ).toContain( '--wc-clearance-badge-label: "Sale"' );
	} );

	test( 'escapes special characters in label', () => {
		const resultQuote = buildPreviewStyles( { label: 'Bob"s Sale' } );
		const resultNewline = buildPreviewStyles( {
			label: 'A "quote"\nand newline',
		} );

		expect( resultQuote ).toContain(
			'--wc-clearance-badge-label: "Bob\\"s Sale"'
		);
		expect( resultNewline ).toContain(
			'--wc-clearance-badge-label: "A \\"quote\\"\\nand newline"'
		);
	} );

	test( 'uses empty string for label when undefined', () => {
		const result = buildPreviewStyles( {} );

		expect( result ).toContain( '--wc-clearance-badge-label: ""' );
	} );

	test.each( [
		[ 'bgColor', '#ff0000' ],
		[ 'textColor', '#ffffff' ],
		[ 'fontSize', '0.875rem' ],
		[ 'fontWeight', '700' ],
		[ 'borderColor', '#cccccc' ],
		[ 'borderStyle', 'solid' ],
		[ 'borderWidth', '1px' ],
		[ 'borderRadius', '4px' ],
		[ 'paddingTop', '8px' ],
		[ 'paddingRight', '12px' ],
		[ 'paddingBottom', '8px' ],
		[ 'paddingLeft', '12px' ],
	] as const )( 'includes %s CSS var', ( key, value ) => {
		const result = buildPreviewStyles( { [ key ]: value } );

		expect( result ).toContain( `${ vars[ key ] }: ${ value }` );
	} );

	test( 'includes all CSS vars when all settings are provided', () => {
		const result = buildPreviewStyles( {
			label: 'Sale',
			bgColor: '#ff0000',
			textColor: '#ffffff',
			fontSize: '0.875rem',
			fontWeight: '700',
			borderColor: '#cccccc',
			borderStyle: 'solid',
			borderWidth: '1px',
			borderRadius: '4px',
			paddingTop: '8px',
			paddingRight: '12px',
			paddingBottom: '8px',
			paddingLeft: '12px',
		} );

		expect( result ).toContain( '--wc-clearance-badge-label: "Sale"' );
		expect( result ).toContain( '--wc-clearance-badge-bg-color: #ff0000' );
		expect( result ).toContain(
			'--wc-clearance-badge-text-color: #ffffff'
		);
		expect( result ).toContain(
			'--wc-clearance-badge-font-size: 0.875rem'
		);
		expect( result ).toContain( '--wc-clearance-badge-font-weight: 700' );
		expect( result ).toContain(
			'--wc-clearance-badge-border-color: #cccccc'
		);
		expect( result ).toContain(
			'--wc-clearance-badge-border-style: solid'
		);
		expect( result ).toContain( '--wc-clearance-badge-border-width: 1px' );
		expect( result ).toContain( '--wc-clearance-badge-border-radius: 4px' );
		expect( result ).toContain( '--wc-clearance-badge-padding-top: 8px' );
		expect( result ).toContain(
			'--wc-clearance-badge-padding-right: 12px'
		);
		expect( result ).toContain(
			'--wc-clearance-badge-padding-bottom: 8px'
		);
		expect( result ).toContain( '--wc-clearance-badge-padding-left: 12px' );
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
			paddingTop: '0',
		} );

		expect( result ).toContain( '--wc-clearance-badge-bg-color: unset' );
		expect( result ).toContain( '--wc-clearance-badge-border-width: 0' );
		expect( result ).toContain( '--wc-clearance-badge-border-radius: 0' );
		expect( result ).toContain( '--wc-clearance-badge-padding-top: 0' );
	} );

	test( 'outputs unset for undefined or empty alongside defined values', () => {
		const result = buildPreviewStyles( {
			bgColor: '#000',
			textColor: undefined,
			fontSize: '',
		} );

		expect( result ).toContain( '--wc-clearance-badge-bg-color: #000' );
		expect( result ).toContain( '--wc-clearance-badge-text-color: unset' );
		expect( result ).toContain( '--wc-clearance-badge-font-size: unset' );
	} );

	test( 'joins declarations with semicolons', () => {
		const result = buildPreviewStyles( {
			label: 'Sale',
			bgColor: '#ff0000',
		} );

		expect( result ).toContain(
			'--wc-clearance-badge-label: "Sale"; --wc-clearance-badge-bg-color: #ff0000'
		);
	} );

	test( 'does not append a trailing semicolon before the closing brace', () => {
		const result = buildPreviewStyles( {} );

		expect( result ).not.toMatch( /;\s+\}$/ );
	} );
} );
