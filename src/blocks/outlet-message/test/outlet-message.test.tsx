/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { useState, type ReactNode } from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import { Edit } from '../edit';

jest.mock( '@wordpress/block-editor', () => ( {
	useBlockProps: jest.fn( ( props ) => props ?? {} ),
	InspectorControls: ( { children }: { children: ReactNode } ) => (
		<div>{ children }</div>
	),
	RichText: jest.fn(
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

jest.mock( '@wordpress/core-data', () => ( {
	useEntityProp: jest.fn(),
} ) );

jest.mock( '@wordpress/blocks', () => ( {
	hasBlockSupport: jest.fn(),
} ) );

jest.mock( '@wordpress/components', () => ( {
	PanelBody: ( { children }: { children: ReactNode } ) => (
		<div>{ children }</div>
	),
	ToggleControl: ( {
		label,
		checked,
		onChange,
	}: {
		label: string;
		checked: boolean;
		onChange: ( value: boolean ) => void;
	} ) => (
		<input
			type="checkbox"
			aria-label={ label }
			checked={ checked }
			onChange={ ( event ) => onChange( event.target.checked ) }
		/>
	),
} ) );

import { useEntityProp } from '@wordpress/core-data';
import { RichText } from '@wordpress/block-editor';
import { hasBlockSupport } from '@wordpress/blocks';

const mockUseEntityProp = useEntityProp as jest.Mock;
const mockRichText = RichText as jest.Mock;

describe( 'Edit', () => {
	test.each( [ false, true ] )( 'toggles Show icon from %s', ( showIcon ) => {
		// Arrange.
		jest.mocked( hasBlockSupport ).mockReturnValue( true );
		mockUseEntityProp.mockReturnValue( [
			'Final sale.',
			jest.fn(),
			undefined,
		] );
		function MessageWithAttributes() {
			const [ attributes, setAttributes ] = useState( { showIcon } );
			return (
				<Edit
					attributes={ attributes }
					setAttributes={ setAttributes }
				/>
			);
		}
		const { container } = render( <MessageWithAttributes /> );

		// Act.
		fireEvent.click(
			screen.getByRole( 'checkbox', { name: 'Show icon' } )
		);

		// Assert.
		expect(
			container.querySelectorAll( 'p.outletpro-has-icon > svg' )
		).toHaveLength( showIcon ? 0 : 1 );
	} );

	test( 'renders the WordPress info icon when enabled', () => {
		// Arrange.
		mockRichText.mockClear();
		jest.mocked( hasBlockSupport ).mockReturnValue( true );
		mockUseEntityProp.mockReturnValue( [
			'Final sale.',
			jest.fn(),
			undefined,
		] );

		// Act.
		const { container } = render(
			<Edit
				attributes={ { showIcon: true } }
				setAttributes={ jest.fn() }
			/>
		);

		// Assert.
		expect(
			container.querySelector( 'p.outletpro-has-icon > svg' )
		).toBeInTheDocument();
		expect( screen.getByDisplayValue( 'Final sale.' ) ).toBeInTheDocument();
		const [ richTextProps ] = mockRichText.mock.calls[ 0 ];
		expect( richTextProps.tagName ).toBe( 'span' );
		expect( container.querySelector( 'p > p' ) ).not.toBeInTheDocument();
	} );

	test( 'hides the icon on WordPress versions without icon support', () => {
		// Arrange.
		mockRichText.mockClear();
		jest.mocked( hasBlockSupport ).mockReturnValue( false );
		mockUseEntityProp.mockReturnValue( [
			'Final sale.',
			jest.fn(),
			undefined,
		] );

		// Act.
		const { container } = render(
			<Edit
				attributes={ { showIcon: true } }
				setAttributes={ jest.fn() }
			/>
		);

		// Assert.
		expect( container.querySelector( 'svg' ) ).not.toBeInTheDocument();
		expect(
			screen.queryByRole( 'checkbox', { name: 'Show icon' } )
		).not.toBeInTheDocument();
		expect( screen.getByDisplayValue( 'Final sale.' ) ).toBeInTheDocument();
		const [ richTextProps ] = mockRichText.mock.calls[ 0 ];
		expect( richTextProps.tagName ).toBe( 'p' );
	} );

	test( 'renders empty message when setting is undefined', () => {
		// Arrange.
		jest.mocked( hasBlockSupport ).mockReturnValue( false );
		const setMessage = jest.fn();
		mockUseEntityProp.mockReturnValue( [
			undefined,
			setMessage,
			undefined,
		] );

		// Act.
		render(
			<Edit
				attributes={ { showIcon: false } }
				setAttributes={ jest.fn() }
			/>
		);

		// Assert.
		expect( screen.getByDisplayValue( '' ) ).toBeInTheDocument();
	} );

	test( 'renders empty message when setting is empty string', () => {
		// Arrange.
		jest.mocked( hasBlockSupport ).mockReturnValue( false );
		const setMessage = jest.fn();
		mockUseEntityProp.mockReturnValue( [ '', setMessage, undefined ] );

		// Act.
		render(
			<Edit
				attributes={ { showIcon: false } }
				setAttributes={ jest.fn() }
			/>
		);

		// Assert.
		expect( screen.getByDisplayValue( '' ) ).toBeInTheDocument();
	} );

	test( 'renders message with value from global setting', () => {
		// Arrange.
		jest.mocked( hasBlockSupport ).mockReturnValue( false );
		const setMessage = jest.fn();
		mockUseEntityProp.mockReturnValue( [
			'Final sale — no returns.',
			setMessage,
			undefined,
		] );

		// Act.
		render(
			<Edit
				attributes={ { showIcon: false } }
				setAttributes={ jest.fn() }
			/>
		);

		// Assert.
		expect(
			screen.getByDisplayValue( 'Final sale — no returns.' )
		).toBeInTheDocument();
	} );

	test( 'calls setMessage with new value when content changes', () => {
		// Arrange.
		jest.mocked( hasBlockSupport ).mockReturnValue( false );
		const setMessage = jest.fn();
		mockUseEntityProp.mockReturnValue( [
			'Not eligible for change of mind returns',
			setMessage,
			undefined,
		] );
		render(
			<Edit
				attributes={ { showIcon: false } }
				setAttributes={ jest.fn() }
			/>
		);
		const input = screen.getByDisplayValue(
			'Not eligible for change of mind returns'
		);

		// Act.
		fireEvent.change( input, {
			target: { value: 'Final sale — no returns.' },
		} );

		// Assert.
		expect( setMessage ).toHaveBeenCalledWith( 'Final sale — no returns.' );
	} );

	test( 'disables rich text formatting controls', () => {
		// Arrange.
		jest.mocked( hasBlockSupport ).mockReturnValue( false );
		mockRichText.mockClear();
		const setMessage = jest.fn();
		mockUseEntityProp.mockReturnValue( [
			'Final sale',
			setMessage,
			undefined,
		] );

		// Act.
		render(
			<Edit
				attributes={ { showIcon: false } }
				setAttributes={ jest.fn() }
			/>
		);

		// Assert.
		const [ richTextProps ] = mockRichText.mock.calls[ 0 ];
		expect( richTextProps.allowedFormats ).toEqual( [] );
	} );
} );
