/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export type ConditionType = 'product' | 'page';

export function ConditionControl( {
	conditionType = 'product',
	onChange,
}: {
	conditionType?: ConditionType;
	onChange: ( value: ConditionType ) => void;
} ): JSX.Element {
	return (
		<SelectControl
			label={ __( 'Show content for', 'outletpro' ) }
			value={ conditionType }
			options={ [
				{
					label: __( 'Outlet products', 'outletpro' ),
					value: 'product',
				},
				{ label: __( 'Outlet page', 'outletpro' ), value: 'page' },
			] }
			onChange={ ( value ) => {
				if ( value === 'product' || value === 'page' ) {
					onChange( value );
				}
			} }
		/>
	);
}
