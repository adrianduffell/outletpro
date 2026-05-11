import {
	BaseControl,
	BorderControl,
	BoxControl,
	CustomSelectControl,
	FontSizePicker,
	PanelBody,
	RangeControl,
	TabPanel,
	TextareaControl,
	TextControl,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalUnitControl as UnitControl,
} from '@wordpress/components';
import { PanelColorSettings } from '@wordpress/block-editor';
import { store as coreStore, useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
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

const FONT_SIZES = [
	{ name: 'S', slug: 'xs', size: '0.66em' },
	{ name: 'M', slug: 's', size: '0.75em' },
	{ name: 'L', slug: 'm', size: '0.83em' },
	{ name: 'XL', slug: 'l', size: '1em' },
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

const bordersEnabled = ( () => {
	try {
		return (
			window.localStorage.getItem( 'wc_clearance_borders_enabled' ) ===
			'1'
		);
	} catch {
		return false;
	}
} )();

const withSiteRecord = ( Component: React.ComponentType ) => () => {
	const hasSiteRecord = useSelect(
		( select ) => !! select( coreStore ).getEntityRecord( 'root', 'site' ),
		[]
	);

	return hasSiteRecord ? <Component /> : null;
};

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

	const [ message, setMessage ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_message'
	) as EntityProp< string >;

	const [ scale, setScale ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_scale'
	) as EntityProp< number >;

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

	const renderBadgeSettings = () => (
		<>
			<PanelBody>
				<p style={ { marginBottom: 0 } }>
					{ __(
						'Customize the appearance of the clearance badge. Changes apply to the whole site.',
						'wc-clearance'
					) }
				</p>
			</PanelBody>

			<PanelBody title={ __( 'Label', 'wc-clearance' ) } initialOpen>
				<BaseControl __nextHasNoMarginBottom={ true }>
					<TextControl
						label={ __( 'Label', 'wc-clearance' ) }
						value={ label ?? '' }
						onChange={ ( value ) => setLabel( value ) }
						hideLabelFromVision={ true }
						__next40pxDefaultSize
						__nextHasNoMarginBottom={ true }
					/>
				</BaseControl>
			</PanelBody>

			<PanelBody title={ __( 'Typography', 'wc-clearance' ) }>
				<BaseControl __nextHasNoMarginBottom={ true }>
					<div style={ { marginBottom: '16px' } }>
						<FontSizePicker
							fontSizes={ FONT_SIZES }
							value={ fontSize || undefined }
							onChange={ ( value ) =>
								setFontSize( value as string | undefined )
							}
							withReset={ false }
							withSlider
							__next40pxDefaultSize
						/>
					</div>
				</BaseControl>

				<BaseControl __nextHasNoMarginBottom={ true }>
					<CustomSelectControl
						label={ __( 'Font weight', 'wc-clearance' ) }
						options={ fontWeightOptions }
						value={ selectedFontWeight }
						onChange={ ( { selectedItem } ) => {
							setFontWeight( selectedItem?.key || '' );
						} }
						__next40pxDefaultSize
					/>
				</BaseControl>
			</PanelBody>

			<PanelColorSettings
				title={ __( 'Color', 'wc-clearance' ) }
				initialOpen={ false }
				colorSettings={ [
					{
						value: textColor,
						label: __( 'Text', 'wc-clearance' ),
						onChange: ( color: string | undefined ) =>
							setTextColor( color || undefined ),
					},
					{
						value: bgColor,
						label: __( 'Background', 'wc-clearance' ),
						onChange: ( backgroundColor: string | undefined ) =>
							setBgColor( backgroundColor || undefined ),
					},
				] }
			/>

			<PanelBody title={ __( 'Dimensions', 'wc-clearance' ) }>
				<BaseControl __nextHasNoMarginBottom={ true }>
					<RangeControl
						label={ __( 'Scale', 'wc-clearance' ) }
						value={ scale }
						onChange={ ( value ) => {
							if ( typeof value !== 'number' ) {
								return;
							}
							setScale( value );
						} }
						min={ 50 }
						max={ 200 }
						step={ 5 }
						allowReset={ true }
						resetFallbackValue={ 140 }
						withInputField={ false }
						__next40pxDefaultSize
					/>
				</BaseControl>

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
					allowReset={ false }
					__next40pxDefaultSize
				/>
			</PanelBody>

			<PanelBody title={ __( 'Border', 'wc-clearance' ) }>
				{ bordersEnabled && (
					<div style={ { marginBottom: 16 } }>
						<BorderControl
							label={ __( 'Border', 'wc-clearance' ) }
							hideLabelFromVision={ true }
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
				) }

				<BaseControl
					id="wc-clearance-border-radius"
					label={ __( 'Radius', 'wc-clearance' ) }
					__nextHasNoMarginBottom={ true }
				>
					<UnitControl
						id="wc-clearance-border-radius"
						value={ borderRadius || undefined }
						onChange={ ( value: string | undefined ) =>
							setBorderRadius( value || undefined )
						}
						min={ 0 }
						__next40pxDefaultSize
					/>
				</BaseControl>
			</PanelBody>
		</>
	);

	const renderMessageSettings = () => (
		<>
			<PanelBody>
				<p
					data-testid="wc-clearance-message-tab-description"
					style={ { marginBottom: 0 } }
				>
					{ __(
						'Customize the clearance message. Changes apply to the whole site.',
						'wc-clearance'
					) }
				</p>
			</PanelBody>

			<PanelBody title={ __( 'Message', 'wc-clearance' ) } initialOpen>
				<BaseControl __nextHasNoMarginBottom={ true }>
					<TextareaControl
						label={ __( 'Message', 'wc-clearance' ) }
						hideLabelFromVision={ true }
						value={ message ?? '' }
						onChange={ ( value ) => setMessage( value ) }
						rows={ 2 }
						__nextHasNoMarginBottom={ true }
						help={ __(
							'Displayed for products included in the clearance section.',
							'wc-clearance'
						) }
					/>
				</BaseControl>
			</PanelBody>
		</>
	);

	return (
		<PluginSidebar
			name={ SIDEBAR_NAME }
			title={ __( 'Clearance section', 'wc-clearance' ) }
			isPinnable={ true }
			icon={ ClearanceIcon }
			className="wc-clearance-sidebar"
		>
			<TabPanel
				className="wc-clearance-sidebar__tabs"
				activeClass="is-active"
				tabs={ [
					{
						name: 'badge',
						title: __( 'Badge', 'wc-clearance' ),
						className: 'wc-clearance-sidebar__tab',
					},
					{
						name: 'message',
						title: __( 'Message', 'wc-clearance' ),
						className: 'wc-clearance-sidebar__tab',
					},
				] }
			>
				{ ( tab ) => {
					if ( tab.name === 'message' ) {
						return renderMessageSettings();
					}
					return renderBadgeSettings();
				} }
			</TabPanel>
		</PluginSidebar>
	);
};

const isBlockEditor =
	window.location.pathname.includes( 'post.php' ) ||
	window.location.pathname.includes( 'post-new.php' );

// Exclude from the block editor (post/pages editor).
if ( ! isBlockEditor ) {
	registerPlugin( SIDEBAR_NAME, {
		render: withSiteRecord( ClearanceSectionSidebar ),
	} );
}
