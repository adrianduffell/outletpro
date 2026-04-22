import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';

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

	const defaultMessage = useSelect( ( select ) => {
		const settings = (
			select( 'core/block-editor' ) as {
				getSettings: () => Record< string, unknown >;
			}
		 ).getSettings();
		return (
			( settings.wcClearanceDefaultMessage as string ) ||
			__( 'Only while stocks last', 'wc-clearance' )
		);
	}, [] );

	const blockProps = useBlockProps();

	return (
		<RichText
			{ ...blockProps }
			tagName="p"
			value={ message || defaultMessage }
			onChange={ ( value: string ) => setMessage( value ) }
			placeholder={ __(
				'Enter clearance message text.',
				'wc-clearance'
			) }
		/>
	);
}
