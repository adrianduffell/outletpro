import type { ReactNode } from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import { Edit } from '../edit';

jest.mock( '@wordpress/block-editor', () => ( {
	useBlockProps: jest.fn( ( props ) => props ?? {} ),
	InspectorControls: ( { children }: { children: ReactNode } ) => (
		<div>{ children }</div>
	),
	RichText: ( {
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
	),
} ) );

jest.mock( '@wordpress/components', () => ( {
	BaseControl: ( { children }: { children: ReactNode; label?: string } ) => (
		<div>{ children }</div>
	),
	PanelBody: ( { children }: { children: ReactNode } ) => (
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
		<input
			aria-label={ label }
			value={ value }
			onChange={ ( e ) => onChange( e.target.value ) }
		/>
	),
} ) );

jest.mock( '@wordpress/i18n', () => ( {
	__: jest.fn( ( str: string ) => str ),
} ) );

jest.mock( '@wordpress/core-data', () => ( {
	useEntityProp: jest.fn(),
} ) );

import { useEntityProp } from '@wordpress/core-data';

const mockUseEntityProp = useEntityProp as jest.Mock;

describe( 'Edit', () => {
	test( 'renders badge with default label when setting is empty', () => {
		// Arrange.
		const SetLabel = jest.fn();
		mockUseEntityProp.mockReturnValue( [ undefined, SetLabel, undefined ] );

		// Act.
		render( <Edit /> );

		// Assert.
		expect( screen.getByDisplayValue( 'Clearance' ) ).toBeInTheDocument();
	} );

	test( 'renders badge with label from global setting', () => {
		// Arrange.
		const SetLabel = jest.fn();
		mockUseEntityProp.mockReturnValue( [ 'Sale', SetLabel, undefined ] );

		// Act.
		render( <Edit /> );

		// Assert.
		expect( screen.getByDisplayValue( 'Sale' ) ).toBeInTheDocument();
	} );

	test( 'calls SetLabel with updated label when content changes', () => {
		// Arrange.
		const SetLabel = jest.fn();
		mockUseEntityProp.mockReturnValue( [
			'Clearance',
			SetLabel,
			undefined,
		] );
		render( <Edit /> );
		const input = screen.getByDisplayValue( 'Clearance' );

		// Act.
		fireEvent.change( input, { target: { value: 'Discounted' } } );

		// Assert.
		expect( SetLabel ).toHaveBeenCalledWith( 'Discounted' );
	} );

	test( 'uses root/site/settings entity prop', () => {
		// Arrange.
		const SetLabel = jest.fn();
		mockUseEntityProp.mockReturnValue( [ {}, SetLabel ] );

		// Act.
		render( <Edit /> );

		// Assert.
		expect( mockUseEntityProp ).toHaveBeenCalledWith(
			'root',
			'site',
			'wc_clearance_badge_label'
		);
	} );
} );
