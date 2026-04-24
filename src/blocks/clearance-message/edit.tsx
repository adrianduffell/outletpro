import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';

type EntityProp< T > = [
	T | undefined,
	( value: T | undefined ) => void,
	unknown,
];

export function Edit(): JSX.Element {
	const [ message, setMessage ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_message'
	) as EntityProp< string >;

	const blockProps = useBlockProps();

	return (
		<RichText
			{ ...blockProps }
			tagName="p"
			value={ message || '' }
			onChange={ ( value: string ) => setMessage( value ) }
			placeholder={ __(
				'Enter clearance message text.',
				'wc-clearance'
			) }
		/>
	);
}
