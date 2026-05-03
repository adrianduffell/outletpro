import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';
import type { EntityProp } from '../../types';

export function Edit(): JSX.Element {
	const [ label, setLabel ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_label'
	) as EntityProp< string >;

	const blockProps = useBlockProps( {
		className: 'wc-clearance-badge',
	} );

	return (
		<RichText
			{ ...blockProps }
			tagName="span"
			value={ label || __( 'Clearance', 'wc-clearance' ) }
			onChange={ ( value: string ) => setLabel( value ) }
			allowedFormats={ [] }
			placeholder={ __( 'Label', 'wc-clearance' ) }
		/>
	);
}
