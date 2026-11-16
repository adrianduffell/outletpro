/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { fireEvent, render } from '@testing-library/react';
import { expect, test } from 'vitest';
import { useState } from '@wordpress/element';
import metadata from '../block.json';
import {
	createBlock,
	parse,
	registerBlockType,
	serialize,
	unregisterBlockType,
} from '@wordpress/blocks';
import { Save } from '../save';
import { Edit } from '../edit';
import {
	BlockEdit,
	BlockEditorProvider,
	InspectorControls,
} from '@wordpress/block-editor';
import { SlotFillProvider } from '@wordpress/components';

test.each( [ 'product', 'page' ] )(
	'toggling the %s condition flips the other switch',
	( conditionType ) => {
		// Arrange.
		registerBlockType( metadata.name, {
			...metadata,
			attributes: {
				conditionType: { type: 'string', default: 'product' },
			},
			edit: Edit,
			save: Save,
		} );
		const block = createBlock( metadata.name );
		function TestEditor(): JSX.Element {
			const [ attributes, setAttributes ] = useState( block.attributes );
			return (
				<BlockEditorProvider value={ [ block ] }>
					<SlotFillProvider>
						<BlockEdit
							{ ...block }
							attributes={ attributes }
							isSelected
							mayDisplayControls
							setAttributes={ setAttributes }
						/>
						<InspectorControls.Slot />
					</SlotFillProvider>
				</BlockEditorProvider>
			);
		}
		const view = render( <TestEditor /> );
		const products = view.getByRole( 'checkbox', {
			name: 'Show on outlet products',
		} );
		const page = view.getByRole( 'checkbox', {
			name: 'Show on outlet page',
		} );
		const toggle = conditionType === 'product' ? products : page;
		try {
			// Act.
			fireEvent.click( toggle );

			// Assert.
			expect( products ).not.toBeChecked();
			expect( page ).toBeChecked();

			// Act.
			fireEvent.click( toggle );

			// Assert.
			expect( products ).toBeChecked();
			expect( page ).not.toBeChecked();
		} finally {
			view.unmount();
			unregisterBlockType( metadata.name );
		}
	}
);

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
		expect( page.innerBlocks[ 0 ].name ).toBe( 'outletpro/test-inner' );
	} finally {
		unregisterBlockType( metadata.name );
		unregisterBlockType( 'outletpro/test-inner' );
	}
} );
