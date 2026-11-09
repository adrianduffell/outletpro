/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { useState, type ReactNode } from 'react';
import { render, renderHook, screen, fireEvent } from '@testing-library/react';
import { Edit } from '../edit';
import { useRegistry } from '@wordpress/data';

jest.mock( '@wordpress/block-editor', () => ( {
	useBlockProps: jest.fn( ( props ) => props ?? {} ),
	useInnerBlocksProps: jest.fn( ( props ) => props ?? {} ),
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
	createBlock: jest.fn(),
} ) );

jest.mock( '@wordpress/data', () => {
	const registry = { select: jest.fn(), dispatch: jest.fn() };
	return {
		useRegistry: () => registry,
		useSelect: ( callback: () => unknown ) => callback(),
	};
} );

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
import { createBlock, hasBlockSupport } from '@wordpress/blocks';

const mockUseEntityProp = useEntityProp as jest.Mock;
const mockRichText = RichText as jest.Mock;

function arrangeLinkBlocks(
	blocks: { clientId: string; name: string }[] = []
) {
	const getBlocks = jest.fn().mockReturnValue( blocks );
	const insertBlock = jest.fn();
	const replaceInnerBlocks = jest.fn();
	const { result } = renderHook( useRegistry );
	jest.mocked( result.current.select ).mockReturnValue( { getBlocks } );
	jest.mocked( result.current.dispatch ).mockReturnValue( {
		insertBlock,
		replaceInnerBlocks,
	} );
	return { getBlocks, insertBlock, replaceInnerBlocks };
}

describe( 'Edit', () => {
	test.each( [
		[ false, false, 'p' ],
		[ true, false, 'span' ],
		[ false, true, 'span' ],
		[ true, true, 'span' ],
	] )(
		'uses %s icon and %s link to choose %s markup',
		( showIcon, showLink, tagName ) => {
			// Arrange.
			arrangeLinkBlocks(
				showLink
					? [ { clientId: 'link', name: 'outletpro/outlet-link' } ]
					: []
			);
			jest.mocked( hasBlockSupport ).mockReturnValue( true );
			mockUseEntityProp.mockReturnValue( [ 'Final sale.', jest.fn() ] );
			mockRichText.mockClear();

			// Act.
			render(
				<Edit
					clientId="message"
					attributes={ { showIcon } }
					setAttributes={ jest.fn() }
				/>
			);

			// Assert.
			expect( mockRichText.mock.calls[ 0 ][ 0 ].tagName ).toBe( tagName );
		}
	);

	test( 'inserts and removes the link while preserving the message', () => {
		// Arrange.
		const { getBlocks, insertBlock, replaceInnerBlocks } =
			arrangeLinkBlocks();
		const link = {
			clientId: 'link',
			name: 'outletpro/outlet-link',
			attributes: {},
			innerBlocks: [],
		};
		jest.mocked( createBlock ).mockReturnValue( link );
		jest.mocked( hasBlockSupport ).mockReturnValue( true );
		mockUseEntityProp.mockReturnValue( [ 'Final sale.', jest.fn() ] );
		const props = {
			clientId: 'message',
			attributes: { showIcon: false },
			setAttributes: jest.fn(),
		};
		const { container, rerender } = render( <Edit { ...props } /> );
		const toggle = screen.getByRole( 'checkbox', {
			name: 'Show link to outlet page',
		} );
		expect( toggle ).not.toBeChecked();

		// Act.
		fireEvent.click( toggle );
		getBlocks.mockReturnValue( [ link ] );
		rerender( <Edit { ...props } /> );

		// Assert.
		expect( insertBlock ).toHaveBeenCalledWith( link, 0, 'message', false );
		expect( toggle ).toBeChecked();
		expect(
			container.querySelector( '.outletpro-has-link' )
		).toBeInTheDocument();

		// Act.
		fireEvent.click( toggle );
		getBlocks.mockReturnValue( [] );
		rerender( <Edit { ...props } /> );

		// Assert.
		expect( replaceInnerBlocks ).toHaveBeenCalledWith(
			'message',
			[],
			false
		);
		expect( toggle ).not.toBeChecked();
		expect( screen.getByDisplayValue( 'Final sale.' ) ).toBeInTheDocument();
		expect(
			container.querySelector( '.outletpro-has-link' )
		).not.toBeInTheDocument();
	} );

	test.each( [ false, true ] )( 'toggles Show icon from %s', ( showIcon ) => {
		// Arrange.
		arrangeLinkBlocks();
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
					clientId="message"
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
			container.querySelectorAll( 'div.outletpro-has-icon > svg' )
		).toHaveLength( showIcon ? 0 : 1 );
	} );

	test( 'renders the WordPress info icon when enabled', () => {
		// Arrange.
		arrangeLinkBlocks();
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
				clientId="message"
				attributes={ { showIcon: true } }
				setAttributes={ jest.fn() }
			/>
		);

		// Assert.
		expect(
			container.querySelector( 'div.outletpro-has-icon > svg' )
		).toBeInTheDocument();
		expect( screen.getByDisplayValue( 'Final sale.' ) ).toBeInTheDocument();
		const [ richTextProps ] = mockRichText.mock.calls[ 0 ];
		expect( richTextProps.tagName ).toBe( 'span' );
		expect( container.querySelector( 'p > p' ) ).not.toBeInTheDocument();
	} );

	test( 'hides the icon on WordPress versions without icon support', () => {
		// Arrange.
		arrangeLinkBlocks();
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
				clientId="message"
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
		arrangeLinkBlocks();
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
				clientId="message"
				attributes={ { showIcon: false } }
				setAttributes={ jest.fn() }
			/>
		);

		// Assert.
		expect( screen.getByDisplayValue( '' ) ).toBeInTheDocument();
	} );

	test( 'renders empty message when setting is empty string', () => {
		// Arrange.
		arrangeLinkBlocks();
		jest.mocked( hasBlockSupport ).mockReturnValue( false );
		const setMessage = jest.fn();
		mockUseEntityProp.mockReturnValue( [ '', setMessage, undefined ] );

		// Act.
		render(
			<Edit
				clientId="message"
				attributes={ { showIcon: false } }
				setAttributes={ jest.fn() }
			/>
		);

		// Assert.
		expect( screen.getByDisplayValue( '' ) ).toBeInTheDocument();
	} );

	test( 'renders message with value from global setting', () => {
		// Arrange.
		arrangeLinkBlocks();
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
				clientId="message"
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
		arrangeLinkBlocks();
		jest.mocked( hasBlockSupport ).mockReturnValue( false );
		const setMessage = jest.fn();
		mockUseEntityProp.mockReturnValue( [
			'Not eligible for change of mind returns',
			setMessage,
			undefined,
		] );
		render(
			<Edit
				clientId="message"
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
		arrangeLinkBlocks();
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
				clientId="message"
				attributes={ { showIcon: false } }
				setAttributes={ jest.fn() }
			/>
		);

		// Assert.
		const [ richTextProps ] = mockRichText.mock.calls[ 0 ];
		expect( richTextProps.allowedFormats ).toEqual( [] );
	} );
} );
