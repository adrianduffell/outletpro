type BuildPreviewStylesParams = {
	label?: string;
	bgColor?: string;
	textColor?: string;
	fontWeight?: string;
	borderColor?: string;
	borderStyle?: string;
	borderWidth?: string;
	borderRadius?: string;
	scale?: number;
	density?: number;
};

export function buildPreviewStyles(
	settings: BuildPreviewStylesParams
): string {
	const entries = {
		'--wc-outlet-badge-bg-color': settings.bgColor,
		'--wc-outlet-badge-text-color': settings.textColor,
		'--wc-outlet-badge-font-weight': settings.fontWeight,
		'--wc-outlet-badge-border-color': settings.borderColor,
		'--wc-outlet-badge-border-style': settings.borderStyle,
		'--wc-outlet-badge-border-width': settings.borderWidth,
		'--wc-outlet-badge-border-radius': settings.borderRadius,
	};

	const declarations = [
		`--wc-outlet-badge-label: ${
			settings.label ? JSON.stringify( settings.label ) : 'none'
		}`,
		`--wc-outlet-badge-scale: ${ settings.scale ?? 'unset' }`,
		`--wc-outlet-badge-density: ${ settings.density ?? 'unset' }`,
		...Object.entries( entries ).map(
			( [ key, value ] ) => `${ key }: ${ value || 'unset' }`
		),
	].join( '; ' );

	return `:root { ${ declarations }` + ' }';
}
