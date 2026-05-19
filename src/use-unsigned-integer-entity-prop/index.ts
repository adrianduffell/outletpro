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
			`wc_outlet setting "${ key }" must be an integer >= 0`
		);
	}
}

export default function useUnsignedIntegerEntityProp(
	key: string
): [ number | undefined, ( value: number | undefined ) => void ] {
	const [ value, setValue ] = useEntityProp( 'root', 'site', key );
	const normalizedValue = value === null ? undefined : value;

	assertUnsignedIntegerValue( normalizedValue, key );

	return [
		normalizedValue,
		( v: number | undefined ) => {
			assertUnsignedIntegerValue( v, key );
			setValue( v );
		},
	];
}
