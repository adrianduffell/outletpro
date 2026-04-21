import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
	PanelColorSettings,
} from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';

type EntityProp< T > = [
	T | undefined,
	( value: T | undefined ) => void,
	unknown,
];

export function Edit(): JSX.Element {
	const [ label, setLabel ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_label'
	) as EntityProp< string >;

	const [ bgColor, setBgColor ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_bg_color'
	) as EntityProp< string >;

	const [ textColor, setTextColor ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_text_color'
	) as EntityProp< string >;

	const blockProps = useBlockProps( {
		className: 'wc-clearance-badge',
		style: {
			backgroundColor: bgColor,
			color: textColor,
		},
	} );

	return (
		<>
			<InspectorControls>
				<PanelColorSettings
					title={ __( 'Color', 'wc-clearance' ) }
					colorSettings={ [
						{
							value: bgColor,
							onChange: setBgColor,
							label: __( 'Background color', 'wc-clearance' ),
						},
						{
							value: textColor,
							onChange: setTextColor,
							label: __( 'Text color', 'wc-clearance' ),
						},
					] }
				/>
			</InspectorControls>
			<RichText
				{ ...blockProps }
				tagName="span"
				value={ label || __( 'Clearance', 'wc-clearance' ) }
				onChange={ ( value: string ) => setLabel( value ) }
				allowedFormats={ [] }
				placeholder={ __( 'Label', 'wc-clearance' ) }
			/>
		</>
	);
}
