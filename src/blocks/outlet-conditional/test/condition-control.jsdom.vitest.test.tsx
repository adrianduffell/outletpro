/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { fireEvent, render } from '@testing-library/react';
import { expect, test, vi } from 'vitest';
import { ConditionControl } from '../condition-control';
import metadata from '../block.json';
import {
	createBlock,
	parse,
	registerBlockType,
	serialize,
	unregisterBlockType,
} from '@wordpress/blocks';
import { Save } from '../save';
import { ConditionLabel } from '../condition-label';

test( 'existing blocks default to the product condition', () => {
	// Arrange.
	const onChange = vi.fn();

	// Act.
	const view = render( <ConditionControl onChange={ onChange } /> );
	try {
		// Assert.
		expect( metadata.attributes.conditionType.default ).toBe( 'product' );
		expect( view.getByLabelText( 'Show content for' ) ).toHaveValue(
			'product'
		);
	} finally {
		view.unmount();
	}
} );

test( 'the condition selector changes between product and page', () => {
	// Arrange.
	const onChange = vi.fn();
	const view = render(
		<ConditionControl conditionType="product" onChange={ onChange } />
	);
	try {
		// Act.
		fireEvent.change( view.getByLabelText( 'Show content for' ), {
			target: { value: 'page' },
		} );
		view.rerender(
			<ConditionControl conditionType="page" onChange={ onChange } />
		);

		// Assert.
		expect( onChange ).toHaveBeenCalledWith( 'page' );
		expect( view.getByLabelText( 'Show content for' ) ).toHaveValue(
			'page'
		);
	} finally {
		view.unmount();
	}
} );

test( 'legacy markup stays valid and page conditions survive serialization', () => {
	// Arrange.
	registerBlockType( metadata.name, {
		apiVersion: 3,
		title: metadata.title,
		category: metadata.category,
		attributes: {
			conditionType: {
				type: 'string',
				default: metadata.attributes.conditionType.default,
				enum: metadata.attributes.conditionType.enum,
			},
		},
		save: Save,
	} );
	registerBlockType( 'outletpro/test-inner', {
		title: 'Test inner block',
		category: 'text',
		save: () => <p>Content</p>,
	} );
	const legacyMarkup =
		'<!-- wp:outletpro/outlet-conditional --><div class="wp-block-outletpro-outlet-conditional"><!-- wp:outletpro/test-inner --><p>Content</p><!-- /wp:outletpro/test-inner --></div><!-- /wp:outletpro/outlet-conditional -->';
	try {
		// Act.
		const [ legacy ] = parse( legacyMarkup );
		const pageMarkup = serialize(
			createBlock( metadata.name, { conditionType: 'page' }, [
				createBlock( 'outletpro/test-inner' ),
			] )
		);
		const [ page ] = parse( pageMarkup );

		// Assert.
		expect( legacy.isValid ).toBe( true );
		expect( legacy.attributes.conditionType ).toBe( 'product' );
		expect( page.isValid ).toBe( true );
		expect( page.attributes.conditionType ).toBe( 'page' );
		expect( page.innerBlocks ).toHaveLength( 1 );
		expect( pageMarkup ).not.toContain( 'outletpro-conditional-label' );
	} finally {
		unregisterBlockType( metadata.name );
		unregisterBlockType( 'outletpro/test-inner' );
	}
} );

test( 'the noneditable editor label follows the selected condition', () => {
	// Arrange.
	const view = render( <ConditionLabel /> );
	try {
		expect(
			view.getByText( 'Displays on outlet product only' )
		).toHaveAttribute( 'contenteditable', 'false' );

		// Act.
		view.rerender( <ConditionLabel conditionType="page" /> );

		// Assert.
		expect(
			view.getByText( 'Displays on outlet page only' )
		).toHaveAttribute( 'contenteditable', 'false' );
		expect(
			view.queryByText( 'Displays on outlet product only' )
		).toBeNull();
	} finally {
		view.unmount();
	}
} );
