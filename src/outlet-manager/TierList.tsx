/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { Button, PanelBody } from '@wordpress/components';
import { decodeEntities } from '@wordpress/html-entities';
import { __ } from '@wordpress/i18n';
import { Icon, plus, tag } from '@wordpress/icons';
import type { OutletTier } from './types';

export default function TierList( {
	kind,
	tiers,
}: {
	kind: 'discount' | 'price';
	tiers: OutletTier[] | null;
} ) {
	const labels =
		kind === 'discount'
			? {
					title: __( 'Discount levels', 'outletpro' ),
					add: __( 'Add discount level', 'outletpro' ),
					empty: __( 'No discount levels configured.', 'outletpro' ),
					error: __(
						'Discount levels could not be loaded. Refresh the page to try again.',
						'outletpro'
					),
				}
			: {
					title: __( 'Price bands', 'outletpro' ),
					add: __( 'Add price band', 'outletpro' ),
					empty: __( 'No price bands configured.', 'outletpro' ),
					error: __(
						'Price bands could not be loaded. Refresh the page to try again.',
						'outletpro'
					),
				};
	return (
		<PanelBody
			className={ `outletpro-manager-tier-panel outletpro-manager-${ kind }-tiers` }
			title={ labels.title }
			initialOpen
		>
			{ tiers === null && <p>{ labels.error }</p> }
			{ tiers !== null && tiers.length === 0 && <p>{ labels.empty }</p> }
			{ tiers !== null && tiers.length > 0 && (
				<ul
					className="outletpro-manager-tier-list"
					aria-label={ labels.title }
				>
					{ tiers.map( ( tier ) => (
						<li key={ tier.id }>
							<Icon icon={ tag } size={ 24 } />
							<span>{ decodeEntities( tier.name ) }</span>
						</li>
					) ) }
				</ul>
			) }
			<Button
				className="outletpro-manager-add-tier"
				icon={ plus }
				label={ labels.add }
				size="compact"
			/>
		</PanelBody>
	);
}
