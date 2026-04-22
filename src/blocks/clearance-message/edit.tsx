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
		type BlockEditorStore = {
			getSettings: () => Record< string, unknown >;
		};
		const settings = (
			select( 'core/block-editor' ) as BlockEditorStore
		 ).getSettings();
		const serverDefault = settings.wcClearanceDefaultMessage;
		return typeof serverDefault === 'string' && serverDefault
			? serverDefault
			: __( 'Only while stocks last', 'wc-clearance' );
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
