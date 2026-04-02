import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText } from '@wordpress/block-editor';

interface Attributes {
	message: string;
}

interface EditProps {
	attributes: Attributes;
	setAttributes: ( attrs: Partial< Attributes > ) => void;
}

export function Edit( { attributes, setAttributes }: EditProps ): JSX.Element {
	const { message } = attributes;

	const blockProps = useBlockProps();

	return (
		<RichText
			{ ...blockProps }
			tagName="p"
			value={ message }
			onChange={ ( value: string ) =>
				setAttributes( { message: value } )
			}
			placeholder={ __(
				'Choose carefully! Clearance products are ineligible for returns',
				'wc-clearance'
			) }
		/>
	);
}
