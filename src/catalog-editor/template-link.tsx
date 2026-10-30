/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { getCatalogEditorUrl } from './settings';

export function CatalogTemplateLink( {
	templateId,
	currentUrl,
}: {
	templateId: string;
	currentUrl: string;
} ): JSX.Element {
	return (
		<div className="outletpro-catalog-template">
			<p>
				{ __(
					'Update the outlet page layout by editing the outlet template.',
					'outletpro'
				) }
			</p>
			<Button
				variant="secondary"
				href={ getCatalogEditorUrl( currentUrl, templateId ) }
			>
				{ __( 'Edit outlet template', 'outletpro' ) }
			</Button>
		</div>
	);
}
