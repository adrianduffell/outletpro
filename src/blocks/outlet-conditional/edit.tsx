/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import {
	InnerBlocks,
	InspectorControls,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { ConditionControl } from './condition-control';
import { ConditionLabel } from './condition-label';

export function Edit( {
	attributes,
	setAttributes,
}: {
	attributes: { conditionType?: 'product' | 'page' };
	setAttributes: ( attributes: {
		conditionType: 'product' | 'page';
	} ) => void;
} ): JSX.Element {
	const blockProps = useBlockProps();
	const { children, ...innerBlocksProps } = useInnerBlocksProps( blockProps, {
		renderAppender: InnerBlocks.ButtonBlockAppender,
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Condition', 'outletpro' ) }>
					<ConditionControl
						conditionType={ attributes.conditionType }
						onChange={ ( conditionType ) =>
							setAttributes( { conditionType } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...innerBlocksProps }>
				<ConditionLabel conditionType={ attributes.conditionType } />
				{ children }
			</div>
		</>
	);
}
