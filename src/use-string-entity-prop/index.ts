import { useEntityProp } from '@wordpress/core-data';

export default function useStringEntityProp(
	key: string
): [ string | undefined, ( value: string | undefined ) => void ] {
	const [ value, setValue ] = useEntityProp( 'root', 'site', key );

	if ( value !== undefined && typeof value !== 'string' ) {
		throw new Error( `wc_clearance setting "${ key }" must be a string` );
	}

	return [ value, ( v: string | undefined ) => setValue( v ) ];
}
