import type { ComponentType, ReactNode } from 'react';
import { fireEvent, render, screen } from '@testing-library/react';

let addFilter: jest.Mock;
let updateOutletQueryAttributes: any;
let withOutletQueryInspector: any;

jest.mock( '@wordpress/block-editor', () => ( {
	InspectorControls: ( { children }: { children: ReactNode } ) => (
		<div>{ children }</div>
	),
} ) );

jest.mock( '@wordpress/components', () => ( {
	CheckboxControl: ( {
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
				id="outlet-checkbox"
				type="checkbox"
				aria-label={ label }
				checked={ checked }
				onChange={ ( event ) => onChange( event.target.checked ) }
			/>
			<label htmlFor="outlet-checkbox">{ label }</label>
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
		addFilter = jest.fn();
		window.wp = {
			hooks: {
				addFilter:
					addFilter as unknown as typeof window.wp.hooks.addFilter,
			},
		};
		( { updateOutletQueryAttributes, withOutletQueryInspector } =
			await import( '../edit' ) );
	} );

	it( 'registers the product collection block edit filter', () => {
		expect( addFilter ).toHaveBeenCalledWith(
			'editor.BlockEdit',
			'wc-outlet/product-collection/outlet-query-inspector',
			withOutletQueryInspector
		);
	} );

	it( 'adds the outlet query flag and query context when checked', () => {
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

		fireEvent.click( screen.getByRole( 'checkbox', { name: 'Outlet' } ) );

		expect( setAttributes ).toHaveBeenCalledWith( {
			query: { wc_outlet: true },
			queryContextIncludes: [ 'query' ],
		} );
	} );

	it( 'removes the outlet query flag and query context when unchecked', () => {
		expect(
			updateOutletQueryAttributes(
				{
					query: {
						perPage: 9,
						wc_outlet: true,
					},
					queryContextIncludes: [ 'collection', 'query' ],
				},
				false
			)
		).toEqual( {
			query: { perPage: 9 },
			queryContextIncludes: [ 'collection' ],
		} );
	} );

	it( 'does not render the checkbox for the outlet collection variation', () => {
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
					collection: 'wc-outlet/product-collection/outlet',
				} }
				setAttributes={ jest.fn() }
			/>
		);

		expect(
			screen.queryByRole( 'checkbox', { name: 'Outlet' } )
		).not.toBeInTheDocument();
	} );
} );
