/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import apiFetch from '@wordpress/api-fetch';
import { Button, TextControl } from '@wordpress/components';
import { useRef, useState, createInterpolateElement } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { ValidationMessage } from './ValidationMessage';
import { useLicenseValidation } from './useLicenseValidation';

declare const outletproWelcomePage: {
	hostname: string;
	isLocalHost: string;
	licenseKey: string;
	productsUrl: string;
};

export function WelcomePage(): JSX.Element {
	const {
		licenseKey,
		validationState,
		canActivate: hasAvailableActivation,
		handleLicenseKeyChange: updateLicenseKey,
	} = useLicenseValidation( outletproWelcomePage.licenseKey );
	const isLocalHost = outletproWelcomePage.isLocalHost === '1';
	const canActivate =
		hasAvailableActivation ||
		( isLocalHost && validationState.status === 'unavailable' );
	const [ isLoading, setIsLoading ] = useState( false );
	const [ errorMessage, setErrorMessage ] = useState( '' );
	const [ isSuccess, setIsSuccess ] = useState( false );
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

	if ( isSuccess ) {
		return (
			<div className="outletpro-welcome-page">
				<h1>{ __( '🎉 Success!', 'outletpro' ) }</h1>
				<p className="outletpro-welcome-page__description">
					{ __(
						"Outlet Pro is now set up. Get started by including your first product in the store's outlet.",
						'outletpro'
					) }{ ' ' }
					<a
						href="https://outletpro.zip/help/get-started/"
						target="_blank"
						rel="noopener noreferrer"
					>
						{ __( 'Learn More', 'outletpro' ) }
					</a>
				</p>
				<div className="outletpro-welcome-page__button-row">
					<Button
						variant="primary"
						href={ outletproWelcomePage.productsUrl }
					>
						{ __( 'Get Started', 'outletpro' ) }
					</Button>
				</div>
			</div>
		);
	}

	const validationRole = [ 'invalid', 'error' ].includes(
		validationState.status
	)
		? 'alert'
		: 'status';
	return (
		<div className="outletpro-welcome-page">
			<h1>{ __( 'Welcome to Outlet Pro', 'outletpro' ) }</h1>

			<p className="outletpro-welcome-page__description">
				{ __(
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
					<ValidationMessage
						hostname={ outletproWelcomePage.hostname }
						isLocalHost={ isLocalHost }
						validationState={ validationState }
					/>
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
