/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */
import { createInterpolateElement } from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';
import { Link } from '@wordpress/ui';
import type { ValidationState } from './useLicenseValidation';
export type { ValidationState } from './useLicenseValidation';
const HELP_URL = 'https://outletpro.zip/help/license-key';
const EXPIRY_HELP_URL = 'https://outletpro.zip/help/license-expiry';
export function ValidationMessage( {
	validationState,
}: {
	validationState: ValidationState;
} ) {
	switch ( validationState.status ) {
		case 'validating':
			return __( 'Validating…', 'outletpro' );
		case 'invalid':
			return __(
				'Please check your premium license key and try again.',
				'outletpro'
			);
		case 'expired': {
			const expiryDate = new Date(
				validationState.expiresAt
			).toLocaleDateString( undefined, {
				day: 'numeric',
				month: 'long',
				year: 'numeric',
			} );
			return createInterpolateElement(
				sprintf(
					/* translators: %s: localized license expiry date. */
					__(
						'❌ License expired on %s. <help>Learn more</help>',
						'outletpro'
					),
					expiryDate
				),
				{ help: <Link href={ EXPIRY_HELP_URL } /> }
			);
		}
		case 'error':
			return __(
				'Unable to contact the licensing service. Please try again.',
				'outletpro'
			);
		case 'available': {
			const expiryMessage = validationState.expiresAt
				? sprintf(
						/* translators: %s: localized license expiry date. */
						__( '. Expires %s', 'outletpro' ),
						new Intl.DateTimeFormat( undefined, {
							dateStyle: 'long',
						} ).format( new Date( validationState.expiresAt ) )
				  )
				: '';
			if ( validationState.remaining === Infinity ) {
				return `${ __(
					'✅ Unlimited site activations available',
					'outletpro'
				) }${ expiryMessage }`;
			}
			/* translators: 1: remaining activations, 2: total activations. */
			const availableMessage = _n(
				'✅ %1$d site activation available',
				'✅ %1$d of %2$d site activations available',
				validationState.remaining,
				'outletpro'
			);
			return `${ sprintf(
				availableMessage,
				validationState.remaining,
				validationState.total
			) }${ expiryMessage }`;
		}
		case 'unavailable': {
			/* translators: 1: total activation limit, 2: reserved placeholder. */
			const unavailableMessage = _n(
				'❌ License has reached the site activation limit%2$s. Purchase another license or deactivate the existing site to use this license. <help>Learn more</help>',
				'❌ License has reached the %1$d-site activation limit%2$s. Purchase another license or deactivate a site to use this license. <help>Learn more</help>',
				validationState.total,
				'outletpro'
			);
			return createInterpolateElement(
				sprintf( unavailableMessage, validationState.total, '' ),
				{ help: <Link href={ HELP_URL } /> }
			);
		}
		default:
			return createInterpolateElement(
				__(
					'Need a premium license? <purchase>Purchase a license</purchase> or <help>find your license key</help>',
					'outletpro'
				),
				{
					purchase: <Link href="https://outletpro.zip/buy" />,
					help: <Link href={ HELP_URL } />,
				}
			);
	}
}
