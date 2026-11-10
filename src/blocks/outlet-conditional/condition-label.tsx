/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { __ } from '@wordpress/i18n';
import type { ConditionType } from './condition-control';

export function ConditionLabel( {
	conditionType = 'product',
}: {
	conditionType?: ConditionType;
} ): JSX.Element {
	return (
		<span className="outletpro-conditional-label" contentEditable={ false }>
			{ conditionType === 'page'
				? __( 'Displays on outlet page only', 'outletpro' )
				: __( 'Displays on outlet product only', 'outletpro' ) }
		</span>
	);
}
