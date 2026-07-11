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
			const response = await window.fetch(
				'https://my-first-worker.adrianduffell.workers.dev/v1/licenses/validate',
				{
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify( { license_key: licenseKey } ),
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
				<p>
					{ __(
						'🎉 Success! Outlet Pro is now set up.',
						'outletpro'
					) }
				</p>
				<p>
					{ __(
						"Get started by including your first product in the store's outlet.",
						'outletpro'
					) }{ ' ' }
					<a href={ outletproWelcomePage.productsUrl }>
						{ __( 'Products', 'outletpro' ) }
					</a>{ ' ' }
					<a href="https://outletpro.zip/">
						{ __( 'Learn more', 'outletpro' ) }
					</a>
				</p>
			</div>
		);
	}

	return (
		<div className="outletpro-welcome-page">
			<p>
				{ __(
					'Thank you for installing Outlet Pro. Enter your premium license key to complete setup.',
					'outletpro'
				) }{ ' ' }
				<a href="https://outletpro.zip/">
					{ __( 'Learn more', 'outletpro' ) }
				</a>
			</p>
			<TextControl
				label={ __( 'Premium license key', 'outletpro' ) }
				value={ licenseKey }
				onChange={ setLicenseKey }
				placeholder="XXXX-XXXX-XXXX"
				// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
				__next40pxDefaultSize
			/>
			{ errorMessage && (
				<p className="outletpro-welcome-page__error">
					{ errorMessage }
				</p>
			) }
			<Button
				variant="primary"
				onClick={ handleContinue }
				isBusy={ isLoading }
				disabled={ isLoading }
			>
				{ __( 'Continue', 'outletpro' ) }
			</Button>
		</div>
	);
}
