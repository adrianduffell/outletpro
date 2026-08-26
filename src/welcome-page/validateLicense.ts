/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

type ValidationResponse = {
	valid: boolean;
	license_key?: {
		activation_limit?: number | null;
		activation_usage?: number;
		expires_at?: string | null;
		status?: string;
	};
	meta?: { product_id?: number };
};

export type LicenseStatus =
	| { valid: false; expiresAt?: string }
	| { valid: true; remaining: number; total: number; expiresAt?: string };

const ALLOWED_LICENSE_PRODUCT_IDS = [ 1279790 ];

function isNonNegativeInteger( value: unknown ): value is number {
	if ( typeof value !== 'number' ) {
		return false;
	}
	if ( ! Number.isInteger( value ) ) {
		return false;
	}
	return value >= 0;
}

export async function validateLicense(
	licenseKey: string
): Promise< LicenseStatus > {
	const response = await fetch(
		'https://api.lemonsqueezy.com/v1/licenses/validate',
		{
			method: 'POST',
			headers: {
				Accept: 'application/json',
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: new URLSearchParams( {
				license_key: licenseKey,
			} ),
		}
	);
	const data: ValidationResponse = await response.json();
	if ( data.valid === false ) {
		if ( data.license_key?.status === 'expired' ) {
			const expiresAt = data.license_key.expires_at;
			if ( typeof expiresAt !== 'string' ) {
				throw new Error( 'Unexpected license validation response' );
			}
			if ( Number.isNaN( Date.parse( expiresAt ) ) ) {
				throw new Error( 'Unexpected license validation response' );
			}
			return { valid: false, expiresAt };
		}
		return { valid: false };
	}
	if ( data.valid !== true ) {
		throw new Error( 'Unexpected license validation response' );
	}
	if ( typeof data.meta?.product_id !== 'number' ) {
		throw new Error( 'Unexpected license validation response' );
	}
	if ( ! ALLOWED_LICENSE_PRODUCT_IDS.includes( data.meta.product_id ) ) {
		return { valid: false };
	}
	const expiresAt = data.license_key?.expires_at ?? undefined;
	if ( typeof expiresAt !== 'undefined' ) {
		if ( typeof expiresAt !== 'string' ) {
			throw new Error( 'Unexpected license validation response' );
		}
		if ( Number.isNaN( Date.parse( expiresAt ) ) ) {
			throw new Error( 'Unexpected license validation response' );
		}
	}
	const activationLimit = data.license_key?.activation_limit;
	const activationUsage = data.license_key?.activation_usage;
	const expiry = typeof expiresAt === 'string' ? { expiresAt } : {};
	if ( activationLimit === null ) {
		return {
			valid: true,
			remaining: Infinity,
			total: Infinity,
			...expiry,
		};
	}
	if ( ! isNonNegativeInteger( activationLimit ) ) {
		throw new Error( 'Unexpected license validation response' );
	}
	if ( ! isNonNegativeInteger( activationUsage ) ) {
		throw new Error( 'Unexpected license validation response' );
	}
	const remaining = Math.max( 0, activationLimit - activationUsage );
	return { valid: true, remaining, total: activationLimit, ...expiry };
}
