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

		expect( result ).toContain(
			'--wc-clearance-badge-label: "Sale" !important'
		);
	} );

	test( 'escapes quotes in label', () => {
		const result = buildPreviewStyles( { label: 'Bob"s Sale' } );

		expect( result ).toContain(
			'--wc-clearance-badge-label: "Bob\\"s Sale" !important'
		);
	} );

	test( 'escapes newlines in label', () => {
		const result = buildPreviewStyles( { label: 'Line 1\nLine 2' } );

		expect( result ).toContain(
			'--wc-clearance-badge-label: "Line 1\\nLine 2" !important'
		);
	} );

	test( 'uses empty string for label when undefined', () => {
		const result = buildPreviewStyles( {} );

		expect( result ).toContain(
			'--wc-clearance-badge-label: "" !important'
		);
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

		expect( result ).toContain(
			`${ vars[ key ] }: ${ value } !important`
		);
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

		expect( result ).toContain(
			'--wc-clearance-badge-label: "Sale" !important'
		);
		expect( result ).toContain(
			'--wc-clearance-badge-bg-color: #ff0000 !important'
		);
		expect( result ).toContain(
			'--wc-clearance-badge-text-color: #ffffff !important'
		);
		expect( result ).toContain(
			'--wc-clearance-badge-font-size: 0.875rem !important'
		);
		expect( result ).toContain(
			'--wc-clearance-badge-font-weight: 700 !important'
		);
		expect( result ).toContain(
			'--wc-clearance-badge-border-color: #cccccc !important'
		);
		expect( result ).toContain(
			'--wc-clearance-badge-border-style: solid !important'
		);
		expect( result ).toContain(
			'--wc-clearance-badge-border-width: 1px !important'
		);
		expect( result ).toContain(
			'--wc-clearance-badge-border-radius: 4px !important'
		);
		expect( result ).toContain(
			'--wc-clearance-badge-padding-top: 8px !important'
		);
		expect( result ).toContain(
			'--wc-clearance-badge-padding-right: 12px !important'
		);
		expect( result ).toContain(
			'--wc-clearance-badge-padding-bottom: 8px !important'
		);
		expect( result ).toContain(
			'--wc-clearance-badge-padding-left: 12px !important'
		);
	} );

	test.each( Object.entries( vars ) as Array<
		[ keyof typeof vars, string ]
	> )( 'outputs unset for undefined %s', ( key, cssVar ) => {
		const result = buildPreviewStyles( { [ key ]: undefined } );

		expect( result ).toContain( `${ cssVar }: unset !important` );
	} );

	test( 'does not convert empty CSS values to unset', () => {
		const result = buildPreviewStyles( {
			bgColor: '',
			borderWidth: '',
		} );

		expect( result ).toContain(
			'--wc-clearance-badge-bg-color:  !important'
		);
		expect( result ).toContain(
			'--wc-clearance-badge-border-width:  !important'
		);
	} );

	test( 'does not convert zero-like CSS values to unset', () => {
		const result = buildPreviewStyles( {
			fontWeight: '0',
			borderWidth: '0',
			borderRadius: '0',
			paddingTop: '0',
		} );

		expect( result ).toContain(
			'--wc-clearance-badge-font-weight: 0 !important'
		);
		expect( result ).toContain(
			'--wc-clearance-badge-border-width: 0 !important'
		);
		expect( result ).toContain(
			'--wc-clearance-badge-border-radius: 0 !important'
		);
		expect( result ).toContain(
			'--wc-clearance-badge-padding-top: 0 !important'
		);
	} );

	test( 'joins declarations with semicolons', () => {
		const result = buildPreviewStyles( {
			label: 'Sale',
			bgColor: '#ff0000',
		} );

		expect( result ).toContain(
			'--wc-clearance-badge-label: "Sale" !important; --wc-clearance-badge-bg-color: #ff0000 !important'
		);
	} );

	test( 'does not append a trailing semicolon before the closing brace', () => {
		const result = buildPreviewStyles( {} );

		expect( result ).not.toMatch( /;\s+\}$/ );
	} );

	test( 'mix of defined and undefined values', () => {
		const result = buildPreviewStyles( {
			bgColor: '#000',
			textColor: undefined,
		} );

		expect( result ).toContain(
			'--wc-clearance-badge-bg-color: #000 !important'
		);
		expect( result ).toContain(
			'--wc-clearance-badge-text-color: unset !important'
		);
	} );

	test( 'does not convert empty string to unset', () => {
		const result = buildPreviewStyles( {
			bgColor: '',
		} );

		expect( result ).toContain(
			'--wc-clearance-badge-bg-color:  !important'
		);
	} );

	test( 'preserves zero-like values', () => {
		const result = buildPreviewStyles( {
			borderWidth: '0',
			paddingTop: '0',
		} );

		expect( result ).toContain(
			'--wc-clearance-badge-border-width: 0 !important'
		);
		expect( result ).toContain(
			'--wc-clearance-badge-padding-top: 0 !important'
		);
	} );

	test( 'handles special characters in label', () => {
		const result = buildPreviewStyles( {
			label: 'A "quote"\nand newline',
		} );

		expect( result ).toContain(
			'--wc-clearance-badge-label: "A \\"quote\\"\\nand newline" !important'
		);
	} );
} );
