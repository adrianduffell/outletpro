import { render, screen, fireEvent } from '@testing-library/react';
import { Edit } from '../edit';

jest.mock( '@wordpress/block-editor', () => ( {
	useBlockProps: jest.fn( ( props ) => props ?? {} ),
	InspectorControls: ( { children }: { children: React.ReactNode } ) => (
		<div>{ children }</div>
	),
} ) );

jest.mock( '@wordpress/components', () => ( {
	BaseControl: ( {
		children,
		label,
	}: {
		children: React.ReactNode;
		label: string;
	} ) => (
		<div>
			<div>{ label }</div>
			{ children }
		</div>
	),
	PanelBody: ( { children }: { children: React.ReactNode } ) => (
		<div>{ children }</div>
	),
	TextControl: ( {
		label,
		value,
		onChange,
	}: {
		label: string;
		value: string;
		onChange: ( v: string ) => void;
	} ) => (
		<div>
			<input
				aria-label={ label }
				value={ value }
				onChange={ ( e ) => onChange( e.target.value ) }
			/>
		</div>
	),
} ) );

jest.mock( '@wordpress/i18n', () => ( {
	__: jest.fn( ( str: string ) => str ),
} ) );

describe( 'Edit', () => {
	const defaultAttributes = {
		badgeText: 'Clearance',
		badgeColor: '#2145e6',
	};

	test( 'renders badge with default badge text', () => {
		// Arrange.
		const setAttributes = jest.fn();

		// Act.
		render(
			<Edit
				attributes={ defaultAttributes }
				setAttributes={ setAttributes }
			/>
		);

		// Assert.
		expect( screen.getByText( 'Clearance' ) ).toBeInTheDocument();
	} );

	test( 'renders badge with custom badge text attribute', () => {
		// Arrange.
		const setAttributes = jest.fn();

		// Act.
		render(
			<Edit
				attributes={ { ...defaultAttributes, badgeText: 'Sale' } }
				setAttributes={ setAttributes }
			/>
		);

		// Assert.
		expect( screen.getByText( 'Sale' ) ).toBeInTheDocument();
	} );

	test( 'renders badge with custom badge color as inline style', () => {
		// Arrange.
		const setAttributes = jest.fn();

		// Act.
		render(
			<Edit
				attributes={ { ...defaultAttributes, badgeColor: '#ff0000' } }
				setAttributes={ setAttributes }
			/>
		);

		// Assert.
		const badge = screen.getByText( 'Clearance' );
		expect( badge ).toHaveStyle( { backgroundColor: '#ff0000' } );
	} );

	test( 'calls setAttributes with new badge text when text control changes', () => {
		// Arrange.
		const setAttributes = jest.fn();
		render(
			<Edit
				attributes={ defaultAttributes }
				setAttributes={ setAttributes }
			/>
		);
		const textInput = screen.getByRole( 'textbox', {
			name: 'Badge Text',
		} );

		// Act.
		fireEvent.change( textInput, { target: { value: 'Discounted' } } );

		// Assert.
		expect( setAttributes ).toHaveBeenCalledWith( {
			badgeText: 'Discounted',
		} );
	} );

	test( 'calls setAttributes with new badge color when color input changes', () => {
		// Arrange.
		const setAttributes = jest.fn();
		render(
			<Edit
				attributes={ defaultAttributes }
				setAttributes={ setAttributes }
			/>
		);
		const colorInput = document.getElementById(
			'wc-clearance-badge-color'
		);

		// Act.
		fireEvent.change( colorInput!, { target: { value: '#abcdef' } } );

		// Assert.
		expect( setAttributes ).toHaveBeenCalledWith( {
			badgeColor: '#abcdef',
		} );
	} );
} );
