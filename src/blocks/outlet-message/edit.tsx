/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	useBlockProps,
	useInnerBlocksProps,
	RichText,
} from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl } from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { createBlock, hasBlockSupport } from '@wordpress/blocks';
import { useRegistry, useSelect } from '@wordpress/data';
import { Icon, info } from '@wordpress/icons';

type EditProps = {
	clientId: string;
	attributes: { showIcon: boolean; strong?: boolean };
	setAttributes: ( attributes: Partial< EditProps[ 'attributes' ] > ) => void;
};

type LinkBlock = { clientId: string; name: string };

type EntityProp< T > = [
	T | undefined,
	( value: T | undefined ) => void,
	unknown,
];

export function Edit( {
	clientId,
	attributes: { showIcon, strong = false },
	setAttributes,
}: EditProps ): JSX.Element {
	const registry = useRegistry();
	const linkBlocks: LinkBlock[] = useSelect(
		() =>
			registry
				.select( 'core/block-editor' )
				.getBlocks( clientId )
				.filter(
					( block: LinkBlock ) =>
						block.name === 'outletpro/outlet-link'
				),
		[ clientId, registry ]
	);
	const hasLink = linkBlocks.length > 0;
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
	const classes = [ 'outletpro-message' ];
	if ( hasIcon ) {
		classes.push( 'outletpro-has-icon' );
	}
	if ( hasLink ) {
		classes.push( 'outletpro-has-link' );
	}
	const blockProps = useBlockProps( {
		className: classes.join( ' ' ),
	} );
	const { children, ...innerBlocksProps } = useInnerBlocksProps( blockProps, {
		allowedBlocks: [ 'outletpro/outlet-link' ],
		renderAppender: false,
		orientation: 'horizontal',
	} );
	const richText = (
		<RichText
			tagName={
				( strong && 'strong' ) ||
				( ( hasIcon || hasLink ) && 'span' ) ||
				'p'
			}
			className={ strong ? 'outletpro-strong' : undefined }
			value={ message ?? '' }
			onChange={ ( value: string ) => setMessage( value ) }
			allowedFormats={ [] }
			placeholder={ __( 'Write outlet message…', 'outletpro' ) }
		/>
	);

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'outletpro' ) }>
					{ supportsIcons && (
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
					) }
					<ToggleControl
						label={ __( 'Show link to outlet page', 'outletpro' ) }
						checked={ hasLink }
						onChange={ ( value ) => {
							const editor =
								registry.dispatch( 'core/block-editor' );
							if ( value ) {
								editor.insertBlock(
									createBlock( 'outletpro/outlet-link' ),
									0,
									clientId,
									false
								);
								return;
							}
							editor.replaceInnerBlocks( clientId, [], false );
						} }
						help={ __(
							'Display a link to the outlet page after the message.',
							'outletpro'
						) }
						__nextHasNoMarginBottom
					/>
				</PanelBody>
			</InspectorControls>
			<InspectorControls group="advanced">
				<SelectControl< '' | 'strong' >
					label={ __( 'HTML element', 'outletpro' ) }
					value={ strong ? 'strong' : '' }
					options={ [
						{ label: __( 'Default', 'outletpro' ), value: '' },
						{ label: '<strong>', value: 'strong' },
					] }
					onChange={ ( value ) =>
						setAttributes( { strong: value === 'strong' } )
					}
					__next40pxDefaultSize
					__nextHasNoMarginBottom
				/>
			</InspectorControls>
			<div { ...innerBlocksProps }>
				{ hasIcon && <Icon icon={ info } /> }
				{ strong && ! hasIcon && ! hasLink ? (
					<p>{ richText }</p>
				) : (
					richText
				) }
				{ children }
			</div>
		</>
	);
}
