/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { render, screen } from '@testing-library/react';
import { Edit } from '../edit';
import { Save } from '../save';

jest.mock( '@wordpress/block-editor', () => ( {
	InnerBlocks: {
		ButtonBlockAppender: () => <button type="button">Add block</button>,
	},
	useBlockProps: Object.assign(
		jest.fn( () => ( {} ) ),
		{
			save: jest.fn( () => ( {} ) ),
		}
	),
	useInnerBlocksProps: Object.assign(
		jest.fn(
			(
				props: Record< string, unknown >,
				settings: { renderAppender: () => JSX.Element }
			) => {
				const Appender = settings.renderAppender;

				return {
					...props,
					children: <Appender />,
				};
			}
		),
		{
			save: jest.fn( ( props: Record< string, unknown > ) => ( {
				...props,
				children: <p>Inner block</p>,
			} ) ),
		}
	),
} ) );

describe( 'Outlet Group', () => {
	test( 'allows blocks to be added inside the container', () => {
		// Arrange.
		const appenderName = 'Add block';

		// Act.
		render( <Edit /> );

		// Assert.
		expect(
			screen.getByRole( 'button', { name: appenderName } )
		).toBeInTheDocument();
	} );

	test( 'saves its inner blocks inside the container', () => {
		// Arrange.
		const innerBlock = 'Inner block';

		// Act.
		render( <Save /> );

		// Assert.
		expect( screen.getByText( innerBlock ) ).toBeInTheDocument();
	} );
} );
