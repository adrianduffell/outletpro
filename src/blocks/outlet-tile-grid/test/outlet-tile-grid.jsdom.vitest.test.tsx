/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { expect, test, vi } from 'vitest';
import '@testing-library/jest-dom/vitest';
import { render, screen } from '@testing-library/react';
import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { Edit } from '../edit';
import { Save } from '../save';
import metadata from '../block.json';

vi.mock( '@wordpress/block-editor', () => ( {
	useBlockProps: Object.assign( vi.fn(), { save: vi.fn() } ),
	useInnerBlocksProps: Object.assign( vi.fn(), { save: vi.fn() } ),
} ) );

test( 'passes the allowed tiles to the editor', () => {
	// Arrange.
	const blockProps = { className: 'tile-grid' };
	vi.mocked( useBlockProps, { partial: true } ).mockReturnValue( blockProps );
	vi.mocked( useInnerBlocksProps, { partial: true } ).mockReturnValue( {} );

	// Act.
	render( <Edit /> );

	// Assert.
	expect( useInnerBlocksProps ).toHaveBeenLastCalledWith( blockProps, {
		allowedBlocks: metadata.allowedBlocks,
	} );
} );

test( 'renders the editable tiles inside their wrapper', () => {
	// Arrange.
	vi.mocked( useBlockProps, { partial: true } ).mockReturnValue( {} );
	vi.mocked( useInnerBlocksProps, { partial: true } ).mockReturnValue( {
		id: 'editable-tiles',
		children: <p>Clothing</p>,
	} );

	// Act.
	render( <Edit /> );

	// Assert.
	expect( screen.getByText( 'Clothing' ).parentElement ).toHaveAttribute(
		'id',
		'editable-tiles'
	);
} );

test( 'saves nested tiles with their wrapper attributes', () => {
	// Arrange.
	const blockProps = { id: 'outlet-tiles', className: 'custom-grid' };
	vi.mocked( useBlockProps.save ).mockReturnValue( blockProps );
	vi.mocked( useInnerBlocksProps.save ).mockImplementation(
		( props: Record< string, unknown > ) => ( {
			...props,
			children: <p>Under $25</p>,
		} )
	);

	// Act.
	render( <Save /> );

	// Assert.
	expect( useInnerBlocksProps.save ).toHaveBeenLastCalledWith( blockProps );
	const wrapper = screen.getByText( 'Under $25' ).parentElement;
	expect( wrapper ).toHaveAttribute( 'id', 'outlet-tiles' );
	expect( wrapper ).toHaveClass( 'custom-grid' );
} );
