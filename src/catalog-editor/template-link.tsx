/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { getCatalogEditorUrl } from './settings';

export function CatalogTemplateLink( {
	templateId,
	templatePartId,
	currentUrl,
}: {
	templateId: string;
	templatePartId: string;
	currentUrl: string;
} ): JSX.Element {
	return (
		<div className="outletpro-catalog-template">
			<p>
				{ __(
					'Edit the Outlet section for its introduction and navigation. Product Catalog layout changes also apply to the Shop.',
					'outletpro'
				) }
			</p>
			<Button
				variant="secondary"
				href={ getCatalogEditorUrl(
					currentUrl,
					templatePartId,
					'wp_template_part'
				) }
			>
				{ __( 'Edit Outlet section', 'outletpro' ) }
			</Button>
			<Button
				variant="secondary"
				href={ getCatalogEditorUrl( currentUrl, templateId ) }
			>
				{ __( 'Edit Product Catalog', 'outletpro' ) }
			</Button>
			<p>
				{ __(
					'If the section is missing, insert the Outlet page section pattern in the Product Catalog template.',
					'outletpro'
				) }
			</p>
		</div>
	);
}
