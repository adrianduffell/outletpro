/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';

type EntityProp< T > = [
	T | undefined,
	( value: T | undefined ) => void,
	unknown,
];

export function Edit(): JSX.Element {
	const [ label, setLabel ] = useEntityProp(
		'root',
		'site',
		'outletpro_badge_label'
	) as EntityProp< string >;

	const blockProps = useBlockProps( {
		className: 'outletpro-badge',
	} );

	return (
		<RichText
			{ ...blockProps }
			tagName="div"
			value={ label ?? '' }
			onChange={ ( value: string ) => setLabel( value ) }
			allowedFormats={ [] }
			placeholder={ __( 'Outlet badge', 'outletpro' ) }
		/>
	);
}
