/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { render, screen, fireEvent } from '@testing-library/react';
import { describe, expect, test, vi } from 'vitest';
import { useEntityProp, useEntityRecord } from '@wordpress/core-data';
import type { PropsWithChildren } from 'react';
import { Edit } from '../edit';

vi.mock( '@wordpress/block-editor', () => ( {
	InspectorControls: ( { children }: PropsWithChildren ) => children,
	useBlockProps: vi.fn( ( props ) => props ?? {} ),
	RichText: vi.fn(
		( {
			value,
			onChange,
			placeholder,
		}: {
			value: string;
			onChange: ( v: string ) => void;
			placeholder?: string;
			[ key: string ]: unknown;
		} ) => (
			<input
				type="text"
				value={ value }
				placeholder={ placeholder }
				onChange={ ( e ) => onChange( e.target.value ) }
			/>
		)
	),
} ) );

vi.mock( '@wordpress/core-data', () => ( {
	useEntityProp: vi.fn(),
	useEntityRecord: vi.fn(),
} ) );

describe( 'Edit', () => {
	test( 'renders empty message when setting is undefined', () => {
		// Arrange.
		const setMessage = vi.fn();
		vi.mocked( useEntityProp ).mockReturnValue( [
			undefined,
			setMessage,
			undefined,
		] );
		vi.mocked( useEntityRecord, { partial: true } ).mockReturnValue( {
			record: null,
		} );

		// Act.
		render(
			<Edit
				attributes={ { showOutletLink: false } }
				setAttributes={ vi.fn() }
			/>
		);

		// Assert.
		expect( screen.getByDisplayValue( '' ) ).toBeInTheDocument();
	} );

	test( 'renders empty message when setting is empty string', () => {
		// Arrange.
		const setMessage = vi.fn();
		vi.mocked( useEntityProp ).mockImplementation(
			( _kind, _name, prop ) => [
				prop === 'outletpro_message' ? '' : undefined,
				setMessage,
				undefined,
			]
		);
		vi.mocked( useEntityRecord, { partial: true } ).mockReturnValue( {
			record: null,
		} );

		// Act.
		render(
			<Edit
				attributes={ { showOutletLink: false } }
				setAttributes={ vi.fn() }
			/>
		);

		// Assert.
		expect( screen.getByDisplayValue( '' ) ).toBeInTheDocument();
	} );

	test( 'renders message with value from global setting', () => {
		// Arrange.
		const setMessage = vi.fn();
		vi.mocked( useEntityProp ).mockImplementation(
			( _kind, _name, prop ) => [
				prop === 'outletpro_message' ? 'Final sale — no returns.' : 42,
				setMessage,
				undefined,
			]
		);
		vi.mocked( useEntityRecord, { partial: true } ).mockReturnValue( {
			record: {
				link: 'https://example.com/outlet/',
				status: 'publish',
				title: { rendered: 'Clearance' },
			},
		} );

		// Act.
		render(
			<Edit
				attributes={ { showOutletLink: false } }
				setAttributes={ vi.fn() }
			/>
		);

		// Assert.
		expect(
			screen.getByDisplayValue( 'Final sale — no returns.' )
		).toBeInTheDocument();
		expect( screen.queryByRole( 'link' ) ).not.toBeInTheDocument();
	} );

	test( 'calls setMessage with new value when content changes', () => {
		// Arrange.
		const setMessage = vi.fn();
		vi.mocked( useEntityProp ).mockImplementation(
			( _kind, _name, prop ) => [
				prop === 'outletpro_message'
					? 'Not eligible for change of mind returns'
					: undefined,
				setMessage,
				undefined,
			]
		);
		vi.mocked( useEntityRecord, { partial: true } ).mockReturnValue( {
			record: null,
		} );
		render(
			<Edit
				attributes={ { showOutletLink: false } }
				setAttributes={ vi.fn() }
			/>
		);
		const input = screen.getByDisplayValue(
			'Not eligible for change of mind returns'
		);

		// Act.
		fireEvent.change( input, {
			target: { value: 'Final sale — <strong>no returns</strong>.' },
		} );

		// Assert.
		expect( setMessage ).toHaveBeenCalledWith(
			'Final sale — <strong>no returns</strong>.'
		);
	} );

	test( 'updates the block attribute when the link is toggled', () => {
		// Arrange.
		vi.mocked( useEntityProp ).mockImplementation(
			( _kind, _name, prop ) => [
				prop === 'outletpro_message' ? 'Final sale.' : undefined,
				vi.fn(),
				undefined,
			]
		);
		vi.mocked( useEntityRecord, { partial: true } ).mockReturnValue( {
			record: null,
		} );
		const setAttributes = vi.fn();
		render(
			<Edit
				attributes={ { showOutletLink: false } }
				setAttributes={ setAttributes }
			/>
		);

		// Act.
		fireEvent.click(
			screen.getByRole( 'checkbox', { name: 'Link to outlet page' } )
		);

		// Assert.
		expect(
			screen.getByRole( 'button', { name: 'Settings' } )
		).toBeInTheDocument();
		expect( setAttributes ).toHaveBeenCalledWith( {
			showOutletLink: true,
		} );
	} );

	test( 'previews the outlet link using the draft outlet page title', () => {
		// Arrange.
		vi.mocked( useEntityProp ).mockImplementation(
			( _kind, _name, prop ) => [
				prop === 'outletpro_message' ? 'Final sale.' : 42,
				vi.fn(),
				undefined,
			]
		);
		vi.mocked( useEntityRecord, { partial: true } ).mockReturnValue( {
			record: {
				link: 'https://example.com/outlet/',
				status: 'draft',
				title: { rendered: 'Last Chance &amp; Final Sale' },
			},
		} );

		// Act.
		render(
			<Edit
				attributes={ { showOutletLink: true } }
				setAttributes={ vi.fn() }
			/>
		);

		// Assert.
		expect(
			screen.getByRole( 'link', {
				name: 'Shop all last chance & final sale',
			} )
		).toHaveAttribute( 'href', 'https://example.com/outlet/' );
	} );
} );
