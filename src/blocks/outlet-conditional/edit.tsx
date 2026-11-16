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
import { BaseControl, PanelBody, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

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
	const innerBlocksProps = useInnerBlocksProps( blockProps, {
		renderAppender: InnerBlocks.ButtonBlockAppender,
	} );
	const isPageCondition = attributes.conditionType === 'page';

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'outletpro' ) }>
					<BaseControl __nextHasNoMarginBottom>
						<BaseControl.VisualLabel>
							{ __( 'Condition', 'outletpro' ) }
						</BaseControl.VisualLabel>
						<ToggleControl
							label={ __(
								'Show on outlet products',
								'outletpro'
							) }
							checked={ ! isPageCondition }
							onChange={ ( checked ) =>
								setAttributes( {
									conditionType: checked ? 'product' : 'page',
								} )
							}
						/>
						<ToggleControl
							label={ __( 'Show on outlet page', 'outletpro' ) }
							checked={ isPageCondition }
							onChange={ ( checked ) =>
								setAttributes( {
									conditionType: checked ? 'page' : 'product',
								} )
							}
							__nextHasNoMarginBottom
						/>
					</BaseControl>
				</PanelBody>
			</InspectorControls>
			<div { ...innerBlocksProps } />
		</>
	);
}
