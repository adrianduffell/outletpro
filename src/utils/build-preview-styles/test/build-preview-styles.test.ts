import { buildPreviewStyles } from '../index';

describe( 'buildPreviewStyles', () => {
	test( 'wraps declarations in a :root rule', () => {
		// Arrange.
		// Act.
		const result = buildPreviewStyles( {} );

		// Assert.
		expect( result ).toMatch( /^:root \{.*\}$/ );
	} );

	test( 'serializes label as a JSON string', () => {
		// Arrange.
		// Act.
		const result = buildPreviewStyles( { label: 'Sale' } );

		// Assert.
		expect( result ).toContain(
			'--wc-clearance-badge-label: "Sale" !important'
		);
	} );

	test( 'uses empty string for label when undefined', () => {
		// Arrange.
		// Act.
		const result = buildPreviewStyles( {} );

		// Assert.
		expect( result ).toContain(
			'--wc-clearance-badge-label: "" !important'
		);
	} );

	test( 'includes bg color CSS var', () => {
		// Arrange.
		// Act.
		const result = buildPreviewStyles( { bgColor: '#ff0000' } );

		// Assert.
		expect( result ).toContain(
			'--wc-clearance-badge-bg-color: #ff0000 !important'
		);
	} );

	test( 'includes text color CSS var', () => {
		// Arrange.
		// Act.
		const result = buildPreviewStyles( { textColor: '#ffffff' } );

		// Assert.
		expect( result ).toContain(
			'--wc-clearance-badge-text-color: #ffffff !important'
		);
	} );

	test( 'includes font size CSS var', () => {
		// Arrange.
		// Act.
		const result = buildPreviewStyles( { fontSize: '0.875rem' } );

		// Assert.
		expect( result ).toContain(
			'--wc-clearance-badge-font-size: 0.875rem !important'
		);
	} );

	test( 'includes font weight CSS var', () => {
		// Arrange.
		// Act.
		const result = buildPreviewStyles( { fontWeight: '700' } );

		// Assert.
		expect( result ).toContain(
			'--wc-clearance-badge-font-weight: 700 !important'
		);
	} );

	test( 'includes border color CSS var', () => {
		// Arrange.
		// Act.
		const result = buildPreviewStyles( { borderColor: '#cccccc' } );

		// Assert.
		expect( result ).toContain(
			'--wc-clearance-badge-border-color: #cccccc !important'
		);
	} );

	test( 'includes border style CSS var', () => {
		// Arrange.
		// Act.
		const result = buildPreviewStyles( { borderStyle: 'solid' } );

		// Assert.
		expect( result ).toContain(
			'--wc-clearance-badge-border-style: solid !important'
		);
	} );

	test( 'includes border width CSS var', () => {
		// Arrange.
		// Act.
		const result = buildPreviewStyles( { borderWidth: '1px' } );

		// Assert.
		expect( result ).toContain(
			'--wc-clearance-badge-border-width: 1px !important'
		);
	} );

	test( 'includes border radius CSS var', () => {
		// Arrange.
		// Act.
		const result = buildPreviewStyles( { borderRadius: '4px' } );

		// Assert.
		expect( result ).toContain(
			'--wc-clearance-badge-border-radius: 4px !important'
		);
	} );

	test( 'includes padding CSS vars', () => {
		// Arrange.
		// Act.
		const result = buildPreviewStyles( {
			paddingTop: '8px',
			paddingRight: '12px',
			paddingBottom: '8px',
			paddingLeft: '12px',
		} );

		// Assert.
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

	test( 'outputs "unset" for undefined CSS var values', () => {
		// Arrange.
		// Act.
		const result = buildPreviewStyles( {} );

		// Assert.
		expect( result ).toContain(
			'--wc-clearance-badge-bg-color: unset !important'
		);
		expect( result ).toContain(
			'--wc-clearance-badge-text-color: unset !important'
		);
		expect( result ).toContain(
			'--wc-clearance-badge-border-radius: unset !important'
		);
	} );

	test( 'joins declarations with semicolons', () => {
		// Arrange.
		// Act.
		const result = buildPreviewStyles( {
			label: 'Sale',
			bgColor: '#ff0000',
		} );

		// Assert.
		expect( result ).toContain( '; ' );
	} );
} );
