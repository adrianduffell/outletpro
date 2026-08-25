/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { useCallback, useRef, useState } from '@wordpress/element';
import { validateLicense } from './validateLicense';

export type ValidationState =
	| { status: 'idle' | 'validating' | 'invalid' | 'error' }
	| { status: 'expired'; expiresAt: string }
	| {
			status: 'available';
			remaining: number;
			total: number;
			expiresAt?: string;
	  }
	| { status: 'unavailable'; total: number };

const LICENSE_KEY_LENGTH = 36;

export function useLicenseValidation() {
	const [ licenseKey, setLicenseKey ] = useState( '' );
	const [ validationState, setValidationState ] = useState< ValidationState >(
		{ status: 'idle' }
	);
	const validationRequestId = useRef( 0 );

	const validateCurrentLicense = useCallback(
		async ( currentLicenseKey: string, requestId: number ) => {
			setValidationState( { status: 'validating' } );
			let result: ValidationState;
			try {
				const validation = await validateLicense( currentLicenseKey );
				if ( ! validation.valid ) {
					result = validation.expiresAt
						? {
								status: 'expired',
								expiresAt: validation.expiresAt,
						  }
						: { status: 'invalid' };
				} else if ( validation.remaining === 0 ) {
					result = {
						status: 'unavailable',
						total: validation.total,
					};
				} else {
					const expiry = validation.expiresAt
						? { expiresAt: validation.expiresAt }
						: {};
					result = {
						status: 'available',
						remaining: validation.remaining,
						total: validation.total,
						...expiry,
					};
				}
			} catch {
				result = { status: 'error' };
			}
			if ( validationRequestId.current !== requestId ) {
				return;
			}
			setValidationState( result );
		},
		[]
	);

	function handleLicenseKeyChange(
		currentLicenseKey: string,
		forceValidation = false
	) {
		const normalizedValue = currentLicenseKey.trim().toUpperCase();
		const requestId = ++validationRequestId.current;
		setLicenseKey( normalizedValue );
		if ( ! forceValidation ) {
			if ( normalizedValue.length !== LICENSE_KEY_LENGTH ) {
				setValidationState( { status: 'idle' } );
				return;
			}
		}
		void validateCurrentLicense( normalizedValue, requestId );
	}

	return {
		licenseKey,
		validationState,
		canActivate: validationState.status === 'available',
		handleLicenseKeyChange,
	};
}
