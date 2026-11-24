/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { useEntityProp } from '@wordpress/core-data';

export function assertArrayUnsignedIntegersValue(
	value: unknown
): asserts value is number[] | undefined {
	if ( value === undefined ) {
		return;
	}
	if ( ! Array.isArray( value ) ) {
		throw new Error( 'Setting value must be an array.' );
	}
	for ( const integer of value ) {
		if ( typeof integer !== 'number' ) {
			throw new Error( 'Array value must be a number.' );
		}
		if ( ! Number.isInteger( integer ) ) {
			throw new Error( 'Array value must be an integer.' );
		}
		if ( integer < 0 ) {
			throw new Error( 'Array value must be unsigned.' );
		}
	}
}

export default function useArrayUnsignedIntegersEntityProp(
	key: string
): [ number[] | undefined, ( value: number[] | undefined ) => void ] {
	const [ value, setValue ] = useEntityProp( 'root', 'site', key );
	const normalizedValue = value === null ? undefined : value;
	assertArrayUnsignedIntegersValue( normalizedValue );
	return [
		normalizedValue,
		( integers ) => {
			assertArrayUnsignedIntegersValue( integers );
			setValue( integers );
		},
	];
}
