/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 *
 *
 * Welcome page component for setting up the premium license (and possibly more
 * things in the future).
 *
 * It has two modes:
 *
 * Welcome mode: The user is entering the license for the first time (or more
 * precisely, when the license is blank).
 *
 * Reset mode: The user is recovering from an invalid license state and is
 * resetting the license key on the site.
 */

import apiFetch from '@wordpress/api-fetch';
import { Button, TextControl } from '@wordpress/components';
import { useRef, useState, createInterpolateElement } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { Link } from '@wordpress/ui';
import { dismiss, undoDismiss } from './dismiss';
import { ValidationMessage } from './ValidationMessage';
import { useLicenseValidation } from './useLicenseValidation';

declare const outletproWelcomePage: {
	licenseExpiry: string | null;
	licenseName: string;
	licenseStatus: 'none' | 'active' | 'not_found' | 'error' | 'expired';
	productsUrl: string;
};

export function WelcomePage(): JSX.Element {
	const {
		licenseKey,
		validationState,
		canActivate,
		handleLicenseKeyChange: updateLicenseKey,
	} = useLicenseValidation();
	const isResetMode = [ 'not_found', 'error' ].includes(
		outletproWelcomePage.licenseStatus
	);
	const isExpiredMode = outletproWelcomePage.licenseStatus === 'expired';
	const isWelcomeMode = [ 'none', 'active' ].includes(
		outletproWelcomePage.licenseStatus
	);
	const [ isLoading, setIsLoading ] = useState( false );
	const [ errorMessage, setErrorMessage ] = useState( '' );
	const [ isSuccess, setIsSuccess ] = useState( false );
	const [ isDismissed, setIsDismissed ] = useState( false );
	const pasted = useRef( false );

	function handleLicenseKeyPaste() {
		pasted.current = true;
	}

	function handleLicenseKeyChange( value: string ) {
		const forceValidation = pasted.current;
		pasted.current = false;
		setErrorMessage( '' );
		updateLicenseKey( value, forceValidation );
	}

	async function handleContinue() {
		if ( ! canActivate ) {
			return;
		}

		setIsLoading( true );
		setErrorMessage( '' );
		try {
			await apiFetch( {
				path: '/wp/v2/settings',
				method: 'POST',
				data: { outletpro_license_key: licenseKey },
			} );
		} catch {
			setErrorMessage(
				__(
					'Unable to apply the license. Please try again.',
					'outletpro'
				)
			);
			setIsLoading( false );
			return;
		}

		setIsSuccess( true );
		setIsLoading( false );
	}

	function handleDismiss() {
		dismiss();
		setIsDismissed( true );
	}

	function handleUndoDismiss() {
		undoDismiss();
		setIsDismissed( false );
	}

	if ( isDismissed ) {
		return (
			<div className="outletpro-welcome-page">
				<h1>{ __( 'Setup dismissed', 'outletpro' ) }</h1>
				<p className="outletpro-welcome-page__description">
					{ createInterpolateElement(
						__(
							'Complete setup any time from the setup link on the plugins screen. <learnMore>Learn more</learnMore>',
							'outletpro'
						),
						{
							learnMore: (
								<Link href="https://outletpro.zip/help/license-key" />
							),
						}
					) }
				</p>

				<div className="outletpro-welcome-page__button-row">
					<Button variant="secondary" onClick={ handleUndoDismiss }>
						{ __( 'Undo', 'outletpro' ) }
					</Button>
				</div>
			</div>
		);
	}

	if ( isSuccess ) {
		return (
			<div className="outletpro-welcome-page">
				<h1>
					{ isResetMode || isExpiredMode
						? __( 'License activated', 'outletpro' )
						: __( '🎉 Success!', 'outletpro' ) }
				</h1>
				<p className="outletpro-welcome-page__description">
					{ isResetMode || isExpiredMode
						? __(
								'License activated. Your premium license includes plugin updates and email support.',
								'outletpro'
						  )
						: __(
								"Outlet Pro is now set up. Get started by including your first product in the store's outlet.",
								'outletpro'
						  ) }{ ' ' }
					{ createInterpolateElement(
						isResetMode || isExpiredMode
							? __(
									'<learnMore>Learn more</learnMore>',
									'outletpro'
							  )
							: __(
									'<learnMore>Learn More</learnMore>',
									'outletpro'
							  ),
						{
							learnMore: (
								<Link
									href={
										isResetMode || isExpiredMode
											? 'https://outletpro.zip/help/license'
											: 'https://outletpro.zip/help/get-started/'
									}
								/>
							),
						}
					) }
				</p>
				{ ! ( isResetMode || isExpiredMode ) && (
					<div className="outletpro-welcome-page__button-row">
						<Button
							variant="primary"
							href={ outletproWelcomePage.productsUrl }
						>
							{ __( 'Get Started', 'outletpro' ) }
						</Button>
					</div>
				) }
			</div>
		);
	}

	const validationRole = [ 'invalid', 'expired', 'error' ].includes(
		validationState.status
	)
		? 'alert'
		: 'status';
	return (
		<div className="outletpro-welcome-page">
			<Button
				variant="link"
				className="outletpro-welcome-page__dismiss"
				onClick={ handleDismiss }
			>
				{ __( 'Dismiss', 'outletpro' ) }
			</Button>
			<h1>
				{ isResetMode || isExpiredMode
					? __( 'Outlet Pro Setup', 'outletpro' )
					: __( 'Welcome to Outlet Pro', 'outletpro' ) }
			</h1>

			<p className="outletpro-welcome-page__description">
				{ isResetMode &&
					__(
						'The license could not be verified on this site. Enter your premium license key to continue.',
						'outletpro'
					) }
				{ isExpiredMode &&
					sprintf(
						/* translators: 1: license name, 2: localized license expiry date. */
						__(
							'Your %1$s expired %2$s. Add a new premium license key to continue.',
							'outletpro'
						),
						outletproWelcomePage.licenseName.toLocaleLowerCase(),
						new Date(
							outletproWelcomePage.licenseExpiry ?? ''
						).toLocaleDateString( undefined, {
							dateStyle: 'long',
						} )
					) }
				{ isWelcomeMode &&
					__(
						'Thank you for choosing Outlet Pro! Enter your premium license key to begin setup.',
						'outletpro'
					) }
			</p>

			<div className="outletpro-welcome-page__license-key-input">
				<TextControl
					label={ __( 'Premium license key', 'outletpro' ) }
					hideLabelFromVision={ true }
					value={ licenseKey }
					onChange={ handleLicenseKeyChange }
					onPaste={ handleLicenseKeyPaste }
					disabled={ isLoading }
					autoComplete="off"
					spellCheck={ false }
					autoCorrect="off"
					autoCapitalize="off"
					__next40pxDefaultSize
				/>
			</div>
			<p
				className={ `outletpro-welcome-page__validation outletpro-welcome-page__validation--${ validationState.status }` }
				role={ validationRole }
				aria-live="polite"
			>
				<span key={ validationState.status }>
					<ValidationMessage validationState={ validationState } />
				</span>
			</p>
			<p className="outletpro-welcome-page__notice">
				{ createInterpolateElement(
					__(
						'By continuing, you agree to the <tos>terms of service</tos> and have read the <privacy>privacy policy</privacy>.',
						'outletpro'
					),
					{
						tos: (
							<a
								href="https://adrianduffell.com/tos.html"
								target="_blank"
								rel="noopener noreferrer"
							>
								terms of service
							</a>
						),
						privacy: (
							<a
								href="https://adrianduffell.com/privacy.html"
								target="_blank"
								rel="noopener noreferrer"
							>
								privacy policy
							</a>
						),
					}
				) }
			</p>
			<div className="outletpro-welcome-page__button-row">
				<Button
					variant="primary"
					onClick={ handleContinue }
					isBusy={ isLoading }
					disabled={ isLoading || ! canActivate }
				>
					{ __( 'Activate site', 'outletpro' ) }
				</Button>
			</div>
			{ errorMessage && (
				<p className="outletpro-welcome-page__error" role="alert">
					{ errorMessage }
				</p>
			) }
		</div>
	);
}
