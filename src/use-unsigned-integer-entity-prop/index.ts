import { useEntityProp } from '@wordpress/core-data';

function assertUnsignedIntegerValue(
	value: unknown,
	key: string
): asserts value is number | undefined {
	if (
		value !== undefined &&
		( typeof value !== 'number' ||
			! Number.isInteger( value ) ||
			value < 0 )
	) {
		throw new Error(
			`wc_clearance setting "${ key }" must be an integer >= 0`
		);
	}
}

export default function useUnsignedIntegerEntityProp(
	key: string
): [ number | undefined, ( value: number | undefined ) => void ] {
	const [ value, setValue ] = useEntityProp( 'root', 'site', key );

	assertUnsignedIntegerValue( value, key );

	return [
		value,
		( v: number | undefined ) => {
			assertUnsignedIntegerValue( v, key );
			setValue( v );
		},
	];
}
