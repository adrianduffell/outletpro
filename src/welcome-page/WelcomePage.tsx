/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */
import apiFetch from '@wordpress/api-fetch';
import { Button, TextControl } from '@wordpress/components';
import {
	useCallback,
	useEffect,
	useRef,
	useState,
	createInterpolateElement,
} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { ValidationMessage, type ValidationState } from './ValidationMessage';
declare const outletproWelcomePage: {
	hostname: string;
	isLocalEnvironment: string;
	licenseKey: string;
	productsUrl: string;
};
type ValidationResponse = {
	valid: boolean;
	license_key?: {
		activation_limit?: number | null;
		activation_usage?: number;
	};
	meta?: {
		product_id?: number;
	};
};
const ALLOWED_LICENSE_PRODUCT_IDS = [ 1279790 ];
const LICENSE_KEY_LENGTH = 36;
function canActivateLicense( validationState: ValidationState ): boolean {
	return [ 'available', 'local', 'unlimited' ].includes(
		validationState.status
	);
}
function isNonNegativeInteger( value: unknown ): value is number {
	if ( typeof value !== 'number' ) {
		return false;
	}
	if ( ! Number.isInteger( value ) ) {
		return false;
	}
	return value >= 0;
}
async function validateLicense(
	licenseKey: string,
	isLocalEnvironment: boolean
): Promise< ValidationState > {
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
		return { status: 'invalid' };
	}
	if ( data.valid !== true ) {
		throw new Error( 'Unexpected license validation response' );
	}
	if ( typeof data.meta?.product_id !== 'number' ) {
		throw new Error( 'Unexpected license validation response' );
	}
	if ( ! ALLOWED_LICENSE_PRODUCT_IDS.includes( data.meta.product_id ) ) {
		return { status: 'invalid' };
	}
	if ( isLocalEnvironment ) {
		return { status: 'local' };
	}
	const activationLimit = data.license_key?.activation_limit;
	const activationUsage = data.license_key?.activation_usage;
	if ( activationLimit === null ) {
		return { status: 'unlimited' };
	}
	if ( ! isNonNegativeInteger( activationLimit ) ) {
		throw new Error( 'Unexpected license validation response' );
	}
	if ( ! isNonNegativeInteger( activationUsage ) ) {
		throw new Error( 'Unexpected license validation response' );
	}
	const remaining = Math.max( 0, activationLimit - activationUsage );
	if ( remaining === 0 ) {
		return { status: 'exhausted', total: activationLimit };
	}
	return { status: 'available', remaining, total: activationLimit };
}
export function WelcomePage(): JSX.Element {
	const [ licenseKey, setLicenseKey ] = useState(
		outletproWelcomePage.licenseKey.trim().toUpperCase()
	);
	const [ validationState, setValidationState ] = useState< ValidationState >(
		{ status: 'idle' }
	);
	const [ isLoading, setIsLoading ] = useState( false );
	const [ errorMessage, setErrorMessage ] = useState( '' );
	const [ isSuccess, setIsSuccess ] = useState( false );
	const initialLicenseKey = useRef( licenseKey );
	const pasted = useRef( false );
	const validationRequestId = useRef( 0 );
	const validateCurrentLicense = useCallback(
		async ( value: string, requestId: number ) => {
			setValidationState( { status: 'validating' } );
			try {
				const result = await validateLicense(
					value,
					outletproWelcomePage.isLocalEnvironment === '1'
				);
				if ( validationRequestId.current !== requestId ) {
					return;
				}
				setValidationState( result );
			} catch {
				if ( validationRequestId.current !== requestId ) {
					return;
				}
				setValidationState( { status: 'error' } );
			}
		},
		[]
	);
	useEffect( () => {
		if ( initialLicenseKey.current.length !== LICENSE_KEY_LENGTH ) {
			return;
		}
		const requestId = ++validationRequestId.current;
		void validateCurrentLicense( initialLicenseKey.current, requestId );
	}, [ validateCurrentLicense ] );
	function handleLicenseKeyPaste() {
		pasted.current = true;
	}
	function handleLicenseKeyChange( value: string ) {
		const normalizedValue = value.trim().toUpperCase();
		const requestId = ++validationRequestId.current;
		const validateRegardlessOfLength = pasted.current;
		pasted.current = false;
		setLicenseKey( normalizedValue );
		setErrorMessage( '' );
		if ( ! validateRegardlessOfLength ) {
			if ( normalizedValue.length !== LICENSE_KEY_LENGTH ) {
				setValidationState( { status: 'idle' } );
				return;
			}
		}
		void validateCurrentLicense( normalizedValue, requestId );
	}
	async function handleContinue() {
		if ( ! canActivateLicense( validationState ) ) {
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
	const displayedValidationState: ValidationState =
		licenseKey === '' ? { status: 'idle' } : validationState;
	const canActivate = canActivateLicense( displayedValidationState );
	const validationRole = [ 'invalid', 'error' ].includes(
		displayedValidationState.status
	)
		? 'alert'
		: 'status';
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
				className={ `outletpro-welcome-page__validation outletpro-welcome-page__validation--${ displayedValidationState.status }` }
				role={ validationRole }
				aria-live="polite"
			>
				<span key={ displayedValidationState.status }>
					<ValidationMessage
						validationState={ displayedValidationState }
						hostname={ outletproWelcomePage.hostname }
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
