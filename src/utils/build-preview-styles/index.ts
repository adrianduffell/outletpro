import useSettings from '../../use-settings';

type SettingsStyles = Partial<
	Pick<
		ReturnType<typeof useSettings>,
		| 'label'
		| 'textColor'
		| 'bgColor'
		| 'fontSize'
		| 'fontWeight'
		| 'borderColor'
		| 'borderStyle'
		| 'borderWidth'
		| 'borderRadius'
		| 'paddingTop'
		| 'paddingRight'
		| 'paddingBottom'
		| 'paddingLeft'
	>
>;

export function buildPreviewStyles(
	settings: SettingsStyles
): string {
	const entries = {
		'--wc-clearance-badge-bg-color': settings.bgColor,
		'--wc-clearance-badge-text-color': settings.textColor,
		'--wc-clearance-badge-font-size': settings.fontSize,
		'--wc-clearance-badge-font-weight': settings.fontWeight,
		'--wc-clearance-badge-border-color': settings.borderColor,
		'--wc-clearance-badge-border-style': settings.borderStyle,
		'--wc-clearance-badge-border-width': settings.borderWidth,
		'--wc-clearance-badge-border-radius': settings.borderRadius,
		'--wc-clearance-badge-padding-top': settings.paddingTop,
		'--wc-clearance-badge-padding-right': settings.paddingRight,
		'--wc-clearance-badge-padding-bottom': settings.paddingBottom,
		'--wc-clearance-badge-padding-left': settings.paddingLeft,
	};

	return `:root { ${ [
		`--wc-clearance-badge-label: ${ JSON.stringify(
			settings.label ?? ''
		) } !important`,
		...Object.entries( entries ).map(
			( [ key, value ] ) =>
				`${ key }: ${ value ?? 'unset' } !important`
		),
	].join( '; ' ) } }`;
}
