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
		const setSettings = jest.fn();
		mockUseEntityProp.mockReturnValue( [ {}, setSettings ] );

		// Act.
		render( <Edit /> );

		// Assert.
		expect( screen.getByDisplayValue( 'Clearance' ) ).toBeInTheDocument();
	} );

	test( 'renders badge with label from global setting', () => {
		// Arrange.
		const setSettings = jest.fn();
		mockUseEntityProp.mockReturnValue( [
			{ wc_clearance_badge_label: 'Sale' },
			setSettings,
		] );

		// Act.
		render( <Edit /> );

		// Assert.
		expect( screen.getByDisplayValue( 'Sale' ) ).toBeInTheDocument();
	} );

	test( 'calls setSettings with updated label when content changes', () => {
		// Arrange.
		const setSettings = jest.fn();
		mockUseEntityProp.mockReturnValue( [
			{ wc_clearance_badge_label: 'Clearance' },
			setSettings,
		] );
		render( <Edit /> );
		const input = screen.getByDisplayValue( 'Clearance' );

		// Act.
		fireEvent.change( input, { target: { value: 'Discounted' } } );

		// Assert.
		expect( setSettings ).toHaveBeenCalledWith( {
			wc_clearance_badge_label: 'Discounted',
		} );
	} );

	test( 'uses root/site/settings entity prop', () => {
		// Arrange.
		const setSettings = jest.fn();
		mockUseEntityProp.mockReturnValue( [ {}, setSettings ] );

		// Act.
		render( <Edit /> );

		// Assert.
		expect( mockUseEntityProp ).toHaveBeenCalledWith(
			'root',
			'site',
			'settings'
		);
	} );
} );
