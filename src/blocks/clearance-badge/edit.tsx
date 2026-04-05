import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';

interface SiteSettings {
	wc_clearance_badge_label?: string;
}

export function Edit(): JSX.Element {
	const [ settings, setSettings ] = useEntityProp(
		'root',
		'site',
		'settings'
	) as unknown as [ SiteSettings, ( value: SiteSettings ) => void ];

	const label =
		settings?.wc_clearance_badge_label &&
		'' !== settings.wc_clearance_badge_label
			? settings.wc_clearance_badge_label
			: 'Clearance';

	const blockProps = useBlockProps( {
		style: {
			display: 'inline-block',
			borderRadius: '4px',
		},
	} );

	return (
		<RichText
			{ ...blockProps }
			tagName="span"
			value={ label }
			onChange={ ( value: string ) =>
				setSettings( {
					...settings,
					wc_clearance_badge_label: value,
				} )
			}
			allowedFormats={ [] }
			placeholder={ __( 'Label', 'wc-clearance' ) }
		/>
	);
}
