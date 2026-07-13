import apiFetch from '@wordpress/api-fetch';
import { Button, TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

declare const outletproWelcomePage: {
	licenseKey: string;
	productsUrl: string;
};

type ValidationResponse = {
	success: boolean;
};

export function WelcomePage(): JSX.Element {
	const [ licenseKey, setLicenseKey ] = useState(
		outletproWelcomePage.licenseKey
	);
	const [ isLoading, setIsLoading ] = useState( false );
	const [ errorMessage, setErrorMessage ] = useState( '' );
	const [ isSuccess, setIsSuccess ] = useState( false );

	async function handleContinue() {
		setIsLoading( true );
		setErrorMessage( '' );

		let isValid = false;
		try {
			const response = await fetch(
				'https://my-first-worker.adrianduffell.workers.dev/v1/licenses/validate',
				{
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify( {
						license_key: licenseKey,
						product: 'outletpro',
					} ),
				}
			);
			const data: ValidationResponse = await response.json();
			isValid = data.success === true;
		} catch {
			setErrorMessage(
				__(
					'Unable to contact the licensing service. Please try again.',
					'outletpro'
				)
			);
			setIsLoading( false );
			return;
		}

		if ( ! isValid ) {
			setErrorMessage(
				__(
					'Invalid license key. Please check it and try again.',
					'outletpro'
				)
			);
			setIsLoading( false );
			return;
		}

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

	return (
		<div className="outletpro-welcome-page">
			<h1>{ __( 'Welcome to Outlet Pro!', 'outletpro' ) }</h1>

			<p className="outletpro-welcome-page__description">
				{ __(
					'Thank you for installing Outlet Pro. Enter your premium license key to begin setup.',
					'outletpro'
				) }
			</p>

			<div className="outletpro-welcome-page__license-key-input">
				<TextControl
					label={ __( 'Premium license key', 'outletpro' ) }
					hideLabelFromVision={ true }
					value={ licenseKey }
					help={ __(
						'The license key can be found in the email receipt for purchasing Outlet Pro.',
						'outletpro'
					) }
					onChange={ setLicenseKey }
					__next40pxDefaultSize
				/>
			</div>
			<p>
				<a
					href="https://outletpro.zip/help/license-key/"
					target="_blank"
					rel="noopener noreferrer"
				>
					{ __( 'I don’t have a license key', 'outletpro' ) }
				</a>
			</p>
			<div className="outletpro-welcome-page__button-row">
				<Button
					variant="primary"
					onClick={ handleContinue }
					isBusy={ isLoading }
					disabled={ isLoading }
				>
					{ __( 'Continue', 'outletpro' ) }
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
