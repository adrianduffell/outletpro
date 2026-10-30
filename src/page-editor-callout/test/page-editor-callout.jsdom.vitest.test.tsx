/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import type { ReactNode } from 'react';
import { render, screen } from '@testing-library/react';
import { useSelect } from '@wordpress/data';
import { expect, test, vi } from 'vitest';
import useUnsignedIntegerEntityProp from '../../use-unsigned-integer-entity-prop';
import OutletPageEditorCallout from '../index';

vi.mock( '@wordpress/data', async ( importOriginal ) => ( {
	...( await importOriginal< typeof import( '@wordpress/data' ) >() ),
	useSelect: vi.fn(),
} ) );

vi.mock( '@wordpress/editor', () => ( {
	PluginPostStatusInfo: ( { children }: { children: ReactNode } ) => (
		<aside>{ children }</aside>
	),
	store: {},
} ) );

vi.mock( '../../use-unsigned-integer-entity-prop', () => ( {
	default: vi.fn(),
} ) );

const mockUseSelect = vi.mocked( useSelect );
const mockUseUnsignedIntegerEntityProp = vi.mocked(
	useUnsignedIntegerEntityProp
);

test( 'block themes do not add a duplicate callout through the sidebar slot', () => {
	// Arrange.
	mockUseSelect.mockReturnValue( {
		currentPostId: 37,
		isBlockTheme: true,
		templateId: 'theme//outlet-catalog',
	} );
	mockUseUnsignedIntegerEntityProp.mockReturnValue( [ 37, vi.fn() ] );
	// Act.
	const { container } = render( <OutletPageEditorCallout /> );

	// Assert.
	expect( container ).toBeEmptyDOMElement();
} );

test( 'links to the Outlet Pro Customizer section for a classic theme', () => {
	// Arrange.
	mockUseSelect.mockReturnValue( {
		currentPostId: 37,
		isBlockTheme: false,
	} );
	mockUseUnsignedIntegerEntityProp.mockReturnValue( [ 37, vi.fn() ] );
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
		`${ window.location.origin }/wp-admin/customize.php?autofocus%5Bsection%5D=outletpro`
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
		isBlockTheme: false,
	} );
	mockUseUnsignedIntegerEntityProp.mockReturnValue( [ 37, vi.fn() ] );

	// Act.
	const { container } = render( <OutletPageEditorCallout /> );

	// Assert.
	expect( container ).toBeEmptyDOMElement();
} );
