/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

const DISMISS_COOKIE = 'OUTLETPRO_DISMISS_SETUP';

export function dismiss(): void {
	document.cookie = `${ DISMISS_COOKIE }=1; Max-Age=2147483647; path=/; SameSite=Lax`;
}

export function undoDismiss(): void {
	document.cookie = `${ DISMISS_COOKIE }=; max-age=0; path=/; SameSite=Lax`;
}
