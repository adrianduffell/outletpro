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
			setErrorMessage( __( 'Invalid license key', 'outletpro' ) );
			setIsLoading( false );
			return;
		}

		if ( ! isValid ) {
			setErrorMessage( __( 'Invalid license key', 'outletpro' ) );
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
			setErrorMessage( __( 'Invalid license key', 'outletpro' ) );
			setIsLoading( false );
			return;
		}

		setIsSuccess( true );
		setIsLoading( false );
	}

	if ( isSuccess ) {
		return (
			<div className="outletpro-welcome-page">
				<h1
					style={ {
						fontSize: '2.8rem',
						fontWeight: 600,
						textAlign: 'center',
					} }
				>
					{ __( '🎉 Success!', 'outletpro' ) }
				</h1>
				<p
					style={ {
						fontSize: '1.1rem',
						fontWeight: 300,
						margin: '1rem auto',
						maxWidth: '90%',
						textAlign: 'center',
					} }
				>
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
				<div style={ { marginTop: '2rem', textAlign: 'center' } }>
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
			<h1
				style={ {
					fontSize: '2.8rem',
					fontWeight: 600,
					textAlign: 'center',
				} }
			>
				{ __( 'Welcome to Outlet Pro!', 'outletpro' ) }
			</h1>
			<p
				style={ {
					fontSize: '1.1rem',
					fontWeight: 300,
					margin: '1rem auto',
					maxWidth: '80%',
					textAlign: 'center',
				} }
			>
				{ __(
					'Thank you for installing Outlet Pro. Please enter your premium license key to begin setup.',
					'outletpro'
				) }
			</p>

			<div style={ { margin: '2rem auto', maxWidth: '90%' } }>
				<TextControl
					label={ __( 'Premium license key', 'outletpro' ) }
					hideLabelFromVision={ true }
					value={ licenseKey }
					help={ __(
						'The license key can be found in the email receipt for purchasing Outlet Pro.',
						'outletpro'
					) }
					onChange={ setLicenseKey }
					placeholder="XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX"
					__next40pxDefaultSize
				/>
			</div>
			{ errorMessage && (
				<p className="outletpro-welcome-page__error">
					{ errorMessage }
				</p>
			) }
			<div style={ { textAlign: 'center' } }>
				<p
					style={ {
						margin: '0 auto 3em auto',
						textAlign: 'center',
					} }
				>
					<a
						href="https://outletpro.zip/help/license-key/"
						target="_blank"
						rel="noopener noreferrer"
					>
						{ __( 'I don’t have a license key', 'outletpro' ) }
					</a>
				</p>
				<Button
					variant="primary"
					onClick={ handleContinue }
					isBusy={ isLoading }
					disabled={ isLoading }
					style={ { marginLeft: '1em' } }
				>
					{ __( 'Continue', 'outletpro' ) }
				</Button>
			</div>
		</div>
	);
}
