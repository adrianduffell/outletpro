/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

export function getErrorMessage( error: unknown, fallback: string ): string {
	if ( typeof error !== 'object' ) {
		return fallback;
	}
	if ( error === null ) {
		return fallback;
	}
	if ( 'message' in error && typeof error.message === 'string' ) {
		return error.message;
	}
	return fallback;
}
