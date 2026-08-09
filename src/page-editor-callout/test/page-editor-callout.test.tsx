/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import type { ReactNode } from 'react';
import { render, screen } from '@testing-library/react';
import { useSelect } from '@wordpress/data';
import useUnsignedIntegerEntityProp from '../../use-unsigned-integer-entity-prop';
import { QUERY_PARAM } from '../../settings-sidebar/constants';
import OutletPageEditorCallout from '../index';

jest.mock( '@wordpress/data', () => ( {
	useSelect: jest.fn(),
} ) );

jest.mock( '@wordpress/editor', () => ( {
	PluginPostStatusInfo: ( { children }: { children: ReactNode } ) => (
		<aside>{ children }</aside>
	),
	store: {},
} ) );

jest.mock( '../../use-unsigned-integer-entity-prop', () => jest.fn() );

const mockUseSelect = jest.mocked( useSelect );
const mockUseUnsignedIntegerEntityProp = jest.mocked(
	useUnsignedIntegerEntityProp
);

test( 'links to the Cart template for a block theme', () => {
	// Arrange.
	mockUseSelect.mockReturnValue( {
		currentPostId: 37,
		isBlockTheme: true,
	} );
	mockUseUnsignedIntegerEntityProp.mockReturnValue( [ 37, jest.fn() ] );
	window.history.replaceState(
		{},
		'',
		'/wp-admin/post.php?post=37&action=edit'
	);

	// Act.
	render( <OutletPageEditorCallout /> );

	// Assert.
	expect(
		screen.getByRole( 'heading', { name: 'Outlet settings' } )
	).toBeInTheDocument();
	expect(
		screen.getByRole( 'img', { name: 'Outlet settings' } )
	).toBeInTheDocument();
	const link = screen.getByRole( 'link', { name: 'Open in site editor' } );
	const expectedUrl = new URL(
		'/wp-admin/site-editor.php',
		window.location.origin
	);
	expectedUrl.search = new URLSearchParams( {
		postType: 'wp_template',
		postId: 'woocommerce/woocommerce//page-cart',
		canvas: 'edit',
		[ QUERY_PARAM ]: '1',
	} ).toString();

	expect( link ).toHaveAttribute( 'href', expectedUrl.href );
	expect( link ).toHaveAttribute( 'target', '_blank' );
	expect( link ).toHaveAttribute( 'rel', 'noopener noreferrer' );
	expect( link.querySelector( 'svg' ) ).toHaveAttribute( 'width', '16' );
	expect( link.querySelector( 'svg' ) ).toHaveAttribute( 'height', '16' );

	window.history.replaceState( {}, '', '/' );
} );

test( 'links to the Outlet Pro Customizer section for a classic theme', () => {
	// Arrange.
	mockUseSelect.mockReturnValue( {
		currentPostId: 37,
		isBlockTheme: false,
		cartUrl: 'http://localhost/cart/',
	} );
	mockUseUnsignedIntegerEntityProp.mockReturnValue( [ 37, jest.fn() ] );
	window.history.replaceState(
		{},
		'',
		'/wp-admin/post.php?post=37&action=edit'
	);

	// Act.
	render( <OutletPageEditorCallout /> );

	// Assert.
	expect(
		screen.getByText(
			'Manage the outlet badge and message in the Customizer.'
		)
	).toBeInTheDocument();
	const link = screen.getByRole( 'link', { name: 'Open in customizer' } );
	expect( link ).toHaveAttribute(
		'href',
		'http://localhost/wp-admin/customize.php?autofocus%5Bsection%5D=outletpro&url=http%3A%2F%2Flocalhost%2Fcart%2F'
	);
	expect( link ).toHaveAttribute( 'target', '_blank' );
	expect( link ).toHaveAttribute( 'rel', 'noopener noreferrer' );
	expect( link.querySelector( 'svg' ) ).toHaveAttribute( 'width', '16' );
	expect( link.querySelector( 'svg' ) ).toHaveAttribute( 'height', '16' );

	window.history.replaceState( {}, '', '/' );
} );

test( 'renders nothing when editing another page', () => {
	// Arrange.
	mockUseSelect.mockReturnValue( {
		currentPostId: 42,
		isBlockTheme: true,
	} );
	mockUseUnsignedIntegerEntityProp.mockReturnValue( [ 37, jest.fn() ] );

	// Act.
	const { container } = render( <OutletPageEditorCallout /> );

	// Assert.
	expect( container ).toBeEmptyDOMElement();
} );
