import {
	BaseControl,
	CustomSelectControl,
	FontSizePicker,
	PanelBody,
	TextControl,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalBorderControl as BorderControl,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalBoxControl as BoxControl,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalUnitControl as UnitControl,
} from '@wordpress/components';
import { PanelColorSettings } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';
import { useMemo } from '@wordpress/element';
import { PluginSidebar } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import ClearanceIcon from './icon';

type BoxValue = {
	top?: string;
	right?: string;
	bottom?: string;
	left?: string;
};

type FontWeightOption = {
	name: string;
	key: string;
	style?: {
		fontWeight: string;
	};
};

type EntityProp< T > = [
	T | undefined,
	( value: T | undefined ) => void,
	unknown,
];

const SIDEBAR_NAME = 'wc-clearance-sidebar';

const DEFAULT_LABEL = __( 'Clearance', 'wc-clearance' );
const DEFAULT_BACKGROUND_COLOR = '#FFEE85';
const DEFAULT_TEXT_COLOR = '#222';

const FONT_SIZES = [
	{ name: 'XS', slug: 'xs', size: '0.625rem' },
	{ name: 'S', slug: 's', size: '0.75rem' },
	{ name: 'M', slug: 'm', size: '0.875rem' },
	{ name: 'L', slug: 'l', size: '1rem' },
	{ name: 'XL', slug: 'xl', size: '1.125rem' },
];

const FONT_WEIGHTS: FontWeightOption[] = [
	{ name: __( 'Default', 'wc-clearance' ), key: '' },
	{ name: __( 'Regular', 'wc-clearance' ), key: '400' },
	{ name: __( 'Medium', 'wc-clearance' ), key: '500' },
	{ name: __( 'Semi Bold', 'wc-clearance' ), key: '600' },
	{ name: __( 'Bold', 'wc-clearance' ), key: '700' },
	{ name: __( 'Extra Bold', 'wc-clearance' ), key: '800' },
	{ name: __( 'Black', 'wc-clearance' ), key: '900' },
];

const ClearanceSectionSidebar = () => {
	const [ label, setLabel ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_label'
	) as EntityProp< string >;

	const [ textColor, setTextColor ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_text_color'
	) as EntityProp< string >;

	const [ bgColor, setBgColor ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_bg_color'
	) as EntityProp< string >;

	const [ fontSize, setFontSize ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_font_size'
	) as EntityProp< string >;

	const [ fontWeight, setFontWeight ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_font_weight'
	) as EntityProp< string >;

	const [ borderColor, setBorderColor ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_border_color'
	) as EntityProp< string >;

	const [ borderStyle, setBorderStyle ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_border_style'
	) as EntityProp< string >;

	const [ borderWidth, setBorderWidth ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_border_width'
	) as EntityProp< string >;

	const [ borderRadius, setBorderRadius ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_border_radius'
	) as EntityProp< string >;

	const [ paddingTop, setPaddingTop ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_padding_top'
	) as EntityProp< string >;

	const [ paddingRight, setPaddingRight ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_padding_right'
	) as EntityProp< string >;

	const [ paddingBottom, setPaddingBottom ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_padding_bottom'
	) as EntityProp< string >;

	const [ paddingLeft, setPaddingLeft ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_padding_left'
	) as EntityProp< string >;

	const border = {
		color: borderColor || undefined,
		style: borderStyle || undefined,
		width: borderWidth || undefined,
	};

	const padding: BoxValue = {
		top: paddingTop || undefined,
		right: paddingRight || undefined,
		bottom: paddingBottom || undefined,
		left: paddingLeft || undefined,
	};

	const fontWeightOptions = useMemo(
		() =>
			FONT_WEIGHTS.map( ( option ) => ( {
				...option,
				style: option.key ? { fontWeight: option.key } : undefined,
			} ) ),
		[]
	);

	const selectedFontWeight =
		fontWeightOptions.find(
			( option ) => option.key === ( fontWeight || '' )
		) || fontWeightOptions[ 0 ];

	return (
		<PluginSidebar
			name={ SIDEBAR_NAME }
			title={ __( 'Clearance section', 'wc-clearance' ) }
			isPinnable={ true }
			icon={ ClearanceIcon }
			className="wc-clearance-sidebar"
		>
			<PanelBody>
				<p style={ { marginBottom: 0 } }>
					{ __(
						'Customize the appearance of the clearance section. Changes apply to the whole site.',
						'wc-clearance'
					) }
				</p>
			</PanelBody>

			<PanelBody title={ __( 'Badge', 'wc-clearance' ) } initialOpen>
				<BaseControl __nextHasNoMarginBottom={ false }>
					<TextControl
						label={ __( 'Badge label', 'wc-clearance' ) }
						value={ label || DEFAULT_LABEL }
						onChange={ ( value ) => setLabel( value ) }
						__nextHasNoMarginBottom
					/>
				</BaseControl>
			</PanelBody>

			<PanelBody title={ __( 'Typography', 'wc-clearance' ) }>
				<BaseControl __nextHasNoMarginBottom={ false }>
					<FontSizePicker
						fontSizes={ FONT_SIZES }
						value={ fontSize || undefined }
						onChange={ ( value ) =>
							setFontSize( value as string | undefined )
						}
						withReset={ false }
						withSlider
					/>
				</BaseControl>

				<BaseControl __nextHasNoMarginBottom={ false }>
					<CustomSelectControl
						label={ __( 'Font weight', 'wc-clearance' ) }
						options={ fontWeightOptions }
						value={ selectedFontWeight }
						onChange={ ( { selectedItem } ) => {
							setFontWeight( selectedItem?.key || '' );
						} }
						__nextUnconstrainedWidth
					/>
				</BaseControl>
			</PanelBody>

			<PanelColorSettings
				title={ __( 'Badge color', 'wc-clearance' ) }
				initialOpen={ false }
				colorSettings={ [
					{
						value: textColor || DEFAULT_TEXT_COLOR,
						label: __( 'Text', 'wc-clearance' ),
						onChange: ( color: string | undefined ) =>
							setTextColor( color || undefined ),
					},
					{
						value: bgColor || DEFAULT_BACKGROUND_COLOR,
						label: __( 'Background', 'wc-clearance' ),
						onChange: ( backgroundColor: string | undefined ) =>
							setBgColor( backgroundColor || undefined ),
					},
				] }
			/>

			<PanelBody title={ __( 'Dimensions', 'wc-clearance' ) }>
				<BoxControl
					values={ padding }
					label={ __( 'Padding', 'wc-clearance' ) }
					onChange={ ( value: BoxValue ) => {
						const nextTop = value?.top || undefined;
						const nextRight = value?.right || undefined;
						const nextBottom = value?.bottom || undefined;
						const nextLeft = value?.left || undefined;

						if ( nextTop !== padding.top ) {
							setPaddingTop( nextTop );
						}

						if ( nextRight !== padding.right ) {
							setPaddingRight( nextRight );
						}

						if ( nextBottom !== padding.bottom ) {
							setPaddingBottom( nextBottom );
						}

						if ( nextLeft !== padding.left ) {
							setPaddingLeft( nextLeft );
						}
					} }
					sides={ [ 'vertical', 'horizontal' ] }
					splitOnAxis
				/>
			</PanelBody>

			<PanelBody title={ __( 'Border', 'wc-clearance' ) }>
				<div style={ { marginBottom: 16 } }>
					<BorderControl
						value={ border }
						onChange={ ( value ) => {
							const nextWidth =
								value?.width !== undefined
									? String( value.width )
									: undefined;
							const nextStyle = value?.style || undefined;

							// Auto-apply 'solid' when width > 0 and the user
							// hasn't explicitly set a style yet.
							const effectiveStyle =
								parseFloat( nextWidth || '0' ) > 0 &&
								borderStyle === ''
									? 'solid'
									: nextStyle;

							setBorderColor( value?.color || undefined );
							setBorderStyle( effectiveStyle );
							setBorderWidth( nextWidth );
						} }
					/>
				</div>

				<BaseControl
					id="wc-clearance-border-radius"
					label={ __( 'Radius', 'wc-clearance' ) }
					__nextHasNoMarginBottom={ false }
				>
					<UnitControl
						id="wc-clearance-border-radius"
						value={ borderRadius || undefined }
						onChange={ ( value: string | undefined ) =>
							setBorderRadius( value || undefined )
						}
						min={ 0 }
					/>
				</BaseControl>
			</PanelBody>
		</PluginSidebar>
	);
};

registerPlugin( SIDEBAR_NAME, {
	render: ClearanceSectionSidebar,
} );
