import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, RichText } from '@wordpress/block-editor';
import { BaseControl, PanelBody, TextControl } from '@wordpress/components';

interface Attributes {
	label: string;
}

interface EditProps {
	attributes: Attributes;
	setAttributes: ( attrs: Partial< Attributes > ) => void;
}

export function Edit( { attributes, setAttributes }: EditProps ): JSX.Element {
	const { label } = attributes;

	const blockProps = useBlockProps( {
		style: {
			display: 'inline-block',
			borderRadius: '4px',
		},
	} );

	return (
		<>
			<RichText
				{ ...blockProps }
				tagName="span"
				value={ label }
				onChange={ ( value ) =>
					setAttributes( { label: value } )
				}
				placeholder={ __( 'Label', 'wc-clearance' ) }
			/>
		</>
	);
}
