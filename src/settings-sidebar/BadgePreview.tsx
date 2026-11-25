/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import type { CSSProperties } from 'react';
import { decodeEntities } from '@wordpress/html-entities';
import { __ } from '@wordpress/i18n';
import type { Settings } from '../use-settings';
import '../blocks/outlet-badge/style.css';
import './style.css';

export default function BadgePreview( {
	settings,
}: {
	settings: Settings;
} ): JSX.Element {
	const style: CSSProperties & Record< `--${ string }`, string | number > = {
		'--outletpro-context-font-size': '16px',
		// Prevent cleared settings from inheriting saved site-wide badge styles.
		'--outletpro-badge-bg-color': settings.bgColor || 'initial',
		'--outletpro-badge-text-color': settings.textColor || 'initial',
		'--outletpro-badge-font-weight': settings.fontWeight || 'initial',
		'--outletpro-badge-border-color': settings.borderColor || 'initial',
		'--outletpro-badge-border-style': settings.borderStyle || 'initial',
		'--outletpro-badge-border-width': settings.borderWidth || 'initial',
		'--outletpro-badge-border-radius': settings.borderRadius || 'initial',
		'--outletpro-badge-scale': settings.scale ?? 166,
		'--outletpro-badge-density': settings.density ?? 50,
	};

	return (
		<div
			className="outletpro-badge-preview"
			role="group"
			aria-label={ __( 'Badge preview', 'outletpro' ) }
		>
			<span className="outletpro-badge" style={ style }>
				{ decodeEntities( settings.label ?? '' ) }
			</span>
		</div>
	);
}
