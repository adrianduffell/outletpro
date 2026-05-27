import type { ReactNode } from 'react';

jest.mock( '@wordpress/hooks', () => ( {
	addFilter: jest.fn(),
} ) );

jest.mock( '@wordpress/block-editor', () => ( {
	InspectorControls: ( { children }: { children: ReactNode } ) => (
		<div>{ children }</div>
	),
} ) );

jest.mock( '@wordpress/components', () => ( {
	ToggleControl: () => null,
	PanelBody: ( { children }: { children: ReactNode; title?: string } ) => (
		<div>{ children }</div>
	),
} ) );

jest.mock( '@wordpress/i18n', () => ( {
	__: ( value: string ) => value,
} ) );

describe( 'product collection outlet inspector', () => {
	it( '[NOT TOGGLE BEHAVIOUR] registers the product collection block edit filter', async () => {
		// Arrange.
		jest.resetModules();
		const addFilter = ( await import( '@wordpress/hooks' ) )
			.addFilter as unknown as jest.Mock;

		// Act.
		const { withOutletQueryInspector } = await import(
			'../../../outlet-toggle'
		);

		// Assert.
		expect( addFilter ).toHaveBeenCalledWith(
			'editor.BlockEdit',
			'wc-outlet/product-collection/outlet-query-inspector',
			withOutletQueryInspector
		);
	} );
} );
