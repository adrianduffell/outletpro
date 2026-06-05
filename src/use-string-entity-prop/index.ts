import { useEntityProp } from '@wordpress/core-data';

export default function useStringEntityProp(
	key: string
): [ string | undefined, ( value: string | undefined ) => void ] {
	const [ value, setValue ] = useEntityProp( 'root', 'site', key );
	const normalizedValue = value === null ? undefined : value;

	if (
		normalizedValue !== undefined &&
		typeof normalizedValue !== 'string'
	) {
		throw new Error( `outletpro setting "${ key }" must be a string` );
	}

	return [ normalizedValue, ( v: string | undefined ) => setValue( v ) ];
}
