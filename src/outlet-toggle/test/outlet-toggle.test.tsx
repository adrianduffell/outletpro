import type { ComponentType, ReactNode } from 'react';
import { fireEvent, render, screen } from '@testing-library/react';

let addFilter: jest.Mock;
let withOutletQueryInspector: any;

jest.mock( '@wordpress/hooks', () => ( {
	addFilter: jest.fn(),
} ) );

jest.mock( '@wordpress/block-editor', () => ( {
	InspectorControls: ( { children }: { children: ReactNode } ) => (
		<div>{ children }</div>
	),
} ) );

jest.mock( '@wordpress/components', () => ( {
	ToggleControl: ( {
		label,
		checked,
		onChange,
	}: {
		label: string;
		checked: boolean;
		onChange: ( checked: boolean ) => void;
	} ) => (
		<>
			<input
				id="outlet-toggle"
				type="checkbox"
				aria-label={ label }
				checked={ checked }
				onChange={ ( event ) => onChange( event.target.checked ) }
			/>
			<label htmlFor="outlet-toggle">{ label }</label>
		</>
	),
	PanelBody: ( { children }: { children: ReactNode; title?: string } ) => (
		<div>{ children }</div>
	),
} ) );

jest.mock( '@wordpress/i18n', () => ( {
	__: ( value: string ) => value,
} ) );

describe( 'product collection outlet inspector', () => {
	beforeEach( async () => {
		jest.resetModules();
		addFilter = ( await import( '@wordpress/hooks' ) )
			.addFilter as unknown as jest.Mock;
		( { withOutletQueryInspector } = await import( '../index' ) );
	} );

	it( 'registers the product collection block edit filter', () => {
		expect( addFilter ).toHaveBeenCalledWith(
			'editor.BlockEdit',
			'wc-outlet/product-collection/outlet-query-inspector',
			withOutletQueryInspector
		);
	} );

	it( 'adds the outlet query flag when checked', () => {
		const setAttributes = jest.fn();
		const BlockEdit = () => <div>Base block edit</div>;
		const WrappedBlockEdit: ComponentType< {
			name: string;
			attributes: Record< string, unknown >;
			setAttributes: jest.Mock;
		} > = withOutletQueryInspector( BlockEdit );

		render(
			<WrappedBlockEdit
				name="woocommerce/product-collection"
				attributes={ {} }
				setAttributes={ setAttributes }
			/>
		);

		fireEvent.click(
			screen.getByRole( 'checkbox', {
				name: 'Show outlet products only',
			} )
		);

		expect( setAttributes ).toHaveBeenCalledWith( {
			query: { outletpro: true },
		} );
	} );

	it( 'removes the outlet query flag when unchecked', () => {
		const setAttributes = jest.fn();
		const BlockEdit = () => <div>Base block edit</div>;
		const WrappedBlockEdit: ComponentType< {
			name: string;
			attributes: Record< string, unknown >;
			setAttributes: jest.Mock;
		} > = withOutletQueryInspector( BlockEdit );

		render(
			<WrappedBlockEdit
				name="woocommerce/product-collection"
				attributes={ {
					query: {
						perPage: 9,
						outletpro: true,
					},
				} }
				setAttributes={ setAttributes }
			/>
		);

		fireEvent.click(
			screen.getByRole( 'checkbox', {
				name: 'Show outlet products only',
			} )
		);

		expect( setAttributes ).toHaveBeenCalledWith( {
			query: { perPage: 9 },
		} );
	} );
} );
