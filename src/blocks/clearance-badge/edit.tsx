import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText } from '@wordpress/block-editor';

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
				onChange={ ( value: string ) =>
					setAttributes( { label: value } )
				}
				placeholder={ __( 'Label', 'wc-clearance' ) }
			/>
		</>
	);
}
