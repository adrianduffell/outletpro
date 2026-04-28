import type { ReactNode } from 'react';
import { render, screen } from '@testing-library/react';
import { registerPlugin } from '@wordpress/plugins';

jest.mock( '@wordpress/plugins', () => ( {
	registerPlugin: jest.fn(),
} ) );

jest.mock( '@wordpress/components', () => ( {
	PanelBody: ( { children }: { children: ReactNode } ) => (
		<div>{ children }</div>
	),
} ) );

jest.mock( '@wordpress/editor', () => ( {
	PluginSidebar: ( {
		children,
		title,
	}: {
		children: ReactNode;
		title: string;
	} ) => (
		<section>
			<h1>{ title }</h1>
			{ children }
		</section>
	),
} ) );

jest.mock( '@wordpress/i18n', () => ( {
	__: jest.fn( ( text: string ) => text ),
} ) );

const mockRegisterPlugin = registerPlugin as jest.Mock;

describe( 'page-editor-sidebar registration', () => {
	test( 'registers the sidebar plugin with expected name and render function', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();

		// Act.
		jest.isolateModules( () => {
			require( '../index' );
		} );

		// Assert.
		expect( mockRegisterPlugin ).toHaveBeenCalledWith(
			'wc-clearance-sidebar',
			expect.objectContaining( {
				render: expect.any( Function ),
			} )
		);
	} );

	test( 'render function outputs the sidebar title and placeholder content', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];

		// Act.
		render( pluginConfig.render() );

		// Assert.
		expect( screen.getByText( 'Clearance section' ) ).toBeInTheDocument();
		expect(
			screen.getByText(
				'Customize the appearance of the clearance section. Changes apply to the whole site.'
			)
		).toBeInTheDocument();
		expect( screen.getByText( 'Badge' ) ).toBeInTheDocument();
	} );
} );
