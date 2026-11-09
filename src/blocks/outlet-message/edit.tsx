/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	useBlockProps,
	RichText,
} from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { hasBlockSupport } from '@wordpress/blocks';
import { Icon, info } from '@wordpress/icons';

type EditProps = {
	attributes: { showIcon: boolean };
	setAttributes: ( attributes: { showIcon: boolean } ) => void;
};

type EntityProp< T > = [
	T | undefined,
	( value: T | undefined ) => void,
	unknown,
];

export function Edit( {
	attributes: { showIcon },
	setAttributes,
}: EditProps ): JSX.Element {
	const [ message, setMessage ] = useEntityProp(
		'root',
		'site',
		'outletpro_message'
	) as EntityProp< string >;
	const supportsIcons = hasBlockSupport(
		'outletpro/outlet-message',
		'outletproInfoIcon',
		false
	);
	const hasIcon = supportsIcons && showIcon;
	const blockProps = useBlockProps( {
		className: hasIcon
			? 'outletpro-message outletpro-has-icon'
			: 'outletpro-message',
	} );
	const richText = (
		<RichText
			{ ...( hasIcon ? {} : blockProps ) }
			tagName={ hasIcon ? 'span' : 'p' }
			value={ message ?? '' }
			onChange={ ( value: string ) => setMessage( value ) }
			allowedFormats={ [] }
			placeholder={ __( 'Write outlet message…', 'outletpro' ) }
		/>
	);

	return (
		<>
			{ supportsIcons && (
				<InspectorControls>
					<PanelBody title={ __( 'Settings', 'outletpro' ) }>
						<ToggleControl
							label={ __( 'Show icon', 'outletpro' ) }
							checked={ showIcon }
							onChange={ ( value ) =>
								setAttributes( { showIcon: value } )
							}
							help={ __(
								'Display a info icon next to the message.',
								'outletpro'
							) }
							__nextHasNoMarginBottom
						/>
					</PanelBody>
				</InspectorControls>
			) }
			{ hasIcon ? (
				<p { ...blockProps }>
					<Icon icon={ info } />
					{ richText }
				</p>
			) : (
				richText
			) }
		</>
	);
}
