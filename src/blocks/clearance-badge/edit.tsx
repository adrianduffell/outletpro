import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { BaseControl, PanelBody, TextControl } from '@wordpress/components';

interface Attributes {
	badgeText: string;
	badgeColor: string;
}

interface EditProps {
	attributes: Attributes;
	setAttributes: ( attrs: Partial< Attributes > ) => void;
}

export function Edit( { attributes, setAttributes }: EditProps ): JSX.Element {
	const { badgeText, badgeColor } = attributes;

	const blockProps = useBlockProps( {
		style: {
			backgroundColor: badgeColor,
			color: '#ffffff',
			padding: '4px 12px',
			borderRadius: '4px',
			display: 'inline-block',
			fontSize: '0.875rem',
			fontWeight: '600',
		},
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Badge Settings', 'wc-clearance' ) }>
					<TextControl
						label={ __( 'Badge Text', 'wc-clearance' ) }
						value={ badgeText }
						onChange={ ( value ) =>
							setAttributes( { badgeText: value } )
						}
					/>
					<BaseControl
						id="wc-clearance-badge-color"
						label={ __( 'Badge Color', 'wc-clearance' ) }
					>
						<input
							id="wc-clearance-badge-color"
							type="color"
							value={ badgeColor }
							onChange={ ( e ) =>
								setAttributes( { badgeColor: e.target.value } )
							}
						/>
					</BaseControl>
				</PanelBody>
			</InspectorControls>
			<span { ...blockProps }>{ badgeText }</span>
		</>
	);
}
