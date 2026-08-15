/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */
/* eslint-disable @wordpress/i18n-ellipsis -- Acceptance copy requires three periods in “Validating...”. */
import type { ReactNode } from 'react';
import { createInterpolateElement } from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';
import { Link } from '@wordpress/ui';
type StaticValidationStatus =
	| 'idle'
	| 'validating'
	| 'invalid'
	| 'error'
	| 'unlimited'
	| 'local';
export type ValidationState =
	| { status: StaticValidationStatus }
	| { status: 'available'; remaining: number; total: number }
	| { status: 'exhausted'; total: number };
const HELP_URL = 'https://outletpro.zip/help/license-key';
const BUY_URL = 'https://outletpro.zip/buy';
export function ValidationMessage( {
	validationState,
	hostname,
}: {
	validationState: ValidationState;
	hostname: string;
} ): ReactNode {
	switch ( validationState.status ) {
		case 'validating':
			return __( 'Validating...', 'outletpro' );
		case 'invalid':
			return __(
				'Please check your premium license key and try again.',
				'outletpro'
			);
		case 'error':
			return __(
				'Unable to contact the licensing service. Please try again.',
				'outletpro'
			);
		case 'available': {
			/* translators: 1: remaining activations, 2: total activations. */
			const availableMessage = _n(
				'✅ %1$d site activation available',
				'✅ %1$d of %2$d site activations available',
				validationState.remaining,
				'outletpro'
			);
			return sprintf(
				availableMessage,
				validationState.remaining,
				validationState.total
			);
		}
		case 'unlimited':
			return __( '✅ Unlimited site activations available', 'outletpro' );
		case 'exhausted': {
			/* translators: 1: total activation limit, 2: reserved placeholder. */
			const exhaustedMessage = _n(
				'❌ License has reached the site activation limit%2$s. Purchase another license or deactivate the existing site to use this license. <help>Learn more</help>',
				'❌ License has reached the %1$d-site activation limit%2$s. Purchase another license or deactivate a site to use this license. <help>Learn more</help>',
				validationState.total,
				'outletpro'
			);
			return createInterpolateElement(
				sprintf( exhaustedMessage, validationState.total, '' ),
				{
					help: <Link href={ HELP_URL } />,
				}
			);
		}
		case 'local':
			return createInterpolateElement(
				sprintf(
					/* translators: %s: site hostname. */
					__(
						'<code>🌐 %s</code> License includes unlimited local sites. <help>Learn more</help>',
						'outletpro'
					),
					hostname
				),
				{
					code: <code />,
					help: <Link href={ HELP_URL } />,
				}
			);
		default:
			return createInterpolateElement(
				__(
					'Need a premium license? <purchase>Purchase a license</purchase> or <help>find your license key</help>',
					'outletpro'
				),
				{
					purchase: <Link href={ BUY_URL } />,
					help: <Link href={ HELP_URL } />,
				}
			);
	}
}
