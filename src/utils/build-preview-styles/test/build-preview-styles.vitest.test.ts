/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { describe, expect, test } from 'vitest';
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
		// Arrange.
		// Act.
		const result = buildPreviewStyles( {} );

		// Assert.
		expect( result ).toMatch( /^:root \{ .+ \}$/ );
	} );

	test( 'serializes label as a JSON string', () => {
		// Arrange.
		// Act.
		const result = buildPreviewStyles( { label: 'Sale' } );

		// Assert.
		expect( result ).toContain( '--outletpro-badge-label: "Sale"' );
	} );

	test( 'escapes special characters in label', () => {
		// Arrange.
		// Act.
		const resultQuote = buildPreviewStyles( { label: 'Bob"s Sale' } );
		const resultNewline = buildPreviewStyles( {
			label: 'A "quote"\nand newline',
		} );

		// Assert.
		expect( resultQuote ).toContain(
			'--outletpro-badge-label: "Bob\\"s Sale"'
		);
		expect( resultNewline ).toContain(
			'--outletpro-badge-label: "A \\"quote\\"\\nand newline"'
		);
	} );

	test( 'uses none for label when undefined', () => {
		// Arrange.
		// Act.
		const result = buildPreviewStyles( {} );

		// Assert.
		expect( result ).toContain( '--outletpro-badge-label: none' );
	} );

	test( 'uses none for label when empty string', () => {
		// Arrange.
		// Act.
		const result = buildPreviewStyles( { label: '' } );

		// Assert.
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
		// Arrange.
		// Act.
		const result = buildPreviewStyles( { [ key ]: value } );

		// Assert.
		expect( result ).toContain( `${ vars[ key ] }: ${ value }` );
	} );

	test( 'includes all CSS vars when all settings are provided', () => {
		// Arrange.
		// Act.
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

		// Assert.
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
		// Arrange.
		// Act.
		const result = buildPreviewStyles( { [ key ]: undefined } );

		// Assert.
		expect( result ).toContain( `${ cssVar }: unset` );
	} );

	test( 'outputs unset for empty or undefined CSS values', () => {
		// Arrange.
		// Act.
		const result = buildPreviewStyles( {
			bgColor: '',
			borderWidth: '0',
			borderRadius: '0',
			scale: undefined,
			density: undefined,
		} );

		// Assert.
		expect( result ).toContain( '--outletpro-badge-bg-color: unset' );
		expect( result ).toContain( '--outletpro-badge-border-width: 0' );
		expect( result ).toContain( '--outletpro-badge-border-radius: 0' );
		expect( result ).toContain( '--outletpro-badge-scale: unset' );
		expect( result ).toContain( '--outletpro-badge-density: unset' );
	} );

	test( 'outputs unset for undefined or empty alongside defined values', () => {
		// Arrange.
		// Act.
		const result = buildPreviewStyles( {
			bgColor: '#000',
			textColor: undefined,
		} );

		// Assert.
		expect( result ).toContain( '--outletpro-badge-bg-color: #000' );
		expect( result ).toContain( '--outletpro-badge-text-color: unset' );
	} );

	test( 'outputs 0 for zero scale', () => {
		// Arrange.
		// Act.
		const result = buildPreviewStyles( {
			scale: 0,
		} );

		// Assert.
		expect( result ).toContain( '--outletpro-badge-scale: 0' );
	} );

	test( 'outputs 0 for zero density', () => {
		// Arrange.
		// Act.
		const result = buildPreviewStyles( {
			density: 0,
		} );

		// Assert.
		expect( result ).toContain( '--outletpro-badge-density: 0' );
	} );

	test( 'joins declarations with semicolons', () => {
		// Arrange.
		// Act.
		const result = buildPreviewStyles( {
			label: 'Sale',
			bgColor: '#ff0000',
		} );

		// Assert.
		expect( result ).toContain( '--outletpro-badge-label: "Sale";' );

		expect( result ).toContain( '--outletpro-badge-bg-color: #ff0000;' );
	} );

	test( 'does not append a trailing semicolon before the closing brace', () => {
		// Arrange.
		// Act.
		const result = buildPreviewStyles( {} );

		// Assert.
		expect( result ).not.toMatch( /;\s+\}$/ );
	} );
} );
