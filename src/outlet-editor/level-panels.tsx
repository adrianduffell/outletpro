/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { __, sprintf } from '@wordpress/i18n';
import LevelsPanel, { type LevelsProps } from './LevelsPanel';
import { formatPrice } from './currency';

export function MarkdownLevels( props: LevelsProps ) {
	return (
		<section aria-label={ __( 'Markdown Levels', 'outletpro' ) }>
			<LevelsPanel
				{ ...props }
				labels={ {
					title: __( 'Markdown Levels', 'outletpro' ),
					add: __( 'Add markdown level', 'outletpro' ),
					edit: __( 'Markdown level', 'outletpro' ),
					input: __( 'Percentage', 'outletpro' ),
					duplicate: __(
						'This markdown level already exists.',
						'outletpro'
					),
				} }
				min={ 1 }
				max={ 100 }
				formatLevel={ ( level ) =>
					sprintf(
						/* translators: %d: markdown percentage. */
						__( '%d%% off', 'outletpro' ),
						level
					)
				}
				formatRemoveLabel={ ( level ) =>
					sprintf(
						/* translators: %d: markdown percentage. */
						__( 'Remove %d%% off', 'outletpro' ),
						level
					)
				}
			/>
		</section>
	);
}

export function PriceLevels( props: LevelsProps ) {
	function formatLevel( level: number ) {
		return sprintf(
			/* translators: %s: formatted price. */
			__( 'Under %s', 'outletpro' ),
			formatPrice( level, window.wc.wcSettings.CURRENCY )
		);
	}
	return (
		<section aria-label={ __( 'Price Levels', 'outletpro' ) }>
			<LevelsPanel
				{ ...props }
				labels={ {
					title: __( 'Price Levels', 'outletpro' ),
					add: __( 'Add price level', 'outletpro' ),
					edit: __( 'Price level', 'outletpro' ),
					input: __( 'Price', 'outletpro' ),
					duplicate: __(
						'This price level already exists.',
						'outletpro'
					),
				} }
				min={ 1 }
				formatLevel={ formatLevel }
				formatRemoveLabel={ ( level ) =>
					sprintf(
						/* translators: %s: price level label. */
						__( 'Remove %s', 'outletpro' ),
						formatLevel( level )
					)
				}
			/>
		</section>
	);
}
