import { useEntityProp } from '@wordpress/core-data';

export default function useUnsignedIntegerEntityProp(
	key: string
): [ number | undefined, ( value: number | undefined ) => void ] {
	const [ value, setValue ] = useEntityProp( 'root', 'site', key );

	if ( value !== undefined ) {
		if ( typeof value !== 'number' || ! Number.isInteger( value ) || value < 0 ) {
			throw new Error(
				`wc_clearance setting "${ key }" must be an integer >= 0`
			);
		}
	}

	return [ value as number | undefined, ( v: number | undefined ) => setValue( v ) ];
}
