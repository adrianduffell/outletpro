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
import {
	ValidationMessage,
	type ValidationState,
} from './ValidationMessage';

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

function normalizeLicenseKey( value: string ): string {
	return value.trim().toUpperCase();
}

function canActivateLicense( validationState: ValidationState ): boolean {
	if ( validationState.status === 'available' ) {
		return true;
	}

	if ( validationState.status === 'local' ) {
		return true;
	}

	if ( validationState.status === 'unlimited' ) {
		return true;
	}

	return false;
}

export function WelcomePage(): JSX.Element {
	const [ licenseKey, setLicenseKey ] = useState(
		normalizeLicenseKey( outletproWelcomePage.licenseKey )
	);
	const [ validationState, setValidationState ] = useState< ValidationState >(
		{ status: 'idle' }
	);
	const [ isSaving, setIsSaving ] = useState( false );
	const [ errorMessage, setErrorMessage ] = useState( '' );
	const [ isSuccess, setIsSuccess ] = useState( false );
	const initialLicenseKey = useRef( licenseKey );
	const pasted = useRef( false );
	const validationRequestId = useRef( 0 );

	const validateLicense = useCallback(
		async ( value: string, requestId: number ) => {
			setValidationState( { status: 'validating' } );

			try {
				const response = await fetch(
					'https://api.lemonsqueezy.com/v1/licenses/validate',
					{
						method: 'POST',
						headers: {
							Accept: 'application/json',
							'Content-Type': 'application/x-www-form-urlencoded',
						},
						body: new URLSearchParams( {
							license_key: value,
						} ),
					}
				);
				const data: ValidationResponse = await response.json();

				if ( validationRequestId.current !== requestId ) {
					return;
				}

				if ( data.valid === false ) {
					setValidationState( { status: 'invalid' } );
					return;
				}

				if ( data.valid !== true ) {
					throw new Error( 'Unexpected license validation response' );
				}

				if ( typeof data.meta?.product_id !== 'number' ) {
					throw new Error( 'Unexpected license validation response' );
				}

				if (
					! ALLOWED_LICENSE_PRODUCT_IDS.includes(
						data.meta.product_id
					)
				) {
					setValidationState( { status: 'invalid' } );
					return;
				}

				if ( outletproWelcomePage.isLocalEnvironment === '1' ) {
					setValidationState( { status: 'local' } );
					return;
				}

				const activationLimit = data.license_key?.activation_limit;
				const activationUsage = data.license_key?.activation_usage;

				if ( activationLimit === null ) {
					setValidationState( { status: 'unlimited' } );
					return;
				}

				if ( typeof activationLimit !== 'number' ) {
					throw new Error( 'Unexpected license validation response' );
				}

				if ( ! Number.isInteger( activationLimit ) ) {
					throw new Error( 'Unexpected license validation response' );
				}

				if ( typeof activationUsage !== 'number' ) {
					throw new Error( 'Unexpected license validation response' );
				}

				if ( ! Number.isInteger( activationUsage ) ) {
					throw new Error( 'Unexpected license validation response' );
				}

				if ( activationLimit < 0 ) {
					throw new Error( 'Unexpected license validation response' );
				}

				if ( activationUsage < 0 ) {
					throw new Error( 'Unexpected license validation response' );
				}

				const remaining = Math.max(
					0,
					activationLimit - activationUsage
				);

				if ( remaining === 0 ) {
					setValidationState( {
						status: 'exhausted',
						total: activationLimit,
					} );
					return;
				}

				setValidationState( {
					status: 'available',
					remaining,
					total: activationLimit,
				} );
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

		const requestId = validationRequestId.current + 1;

		validationRequestId.current = requestId;
		void validateLicense( initialLicenseKey.current, requestId );
	}, [ validateLicense ] );

	function handleLicenseKeyPaste() {
		pasted.current = true;
	}

	function handleLicenseKeyChange( value: string ) {
		const normalizedValue = normalizeLicenseKey( value );
		const requestId = validationRequestId.current + 1;
		const validateRegardlessOfLength = pasted.current;

		pasted.current = false;
		validationRequestId.current = requestId;
		setLicenseKey( normalizedValue );
		setErrorMessage( '' );

		if ( ! validateRegardlessOfLength ) {
			if ( normalizedValue.length !== LICENSE_KEY_LENGTH ) {
				setValidationState( { status: 'idle' } );
				return;
			}
		}

		void validateLicense( normalizedValue, requestId );
	}

	async function handleActivate() {
		if ( licenseKey === '' ) {
			return;
		}

		if ( ! canActivateLicense( validationState ) ) {
			return;
		}

		setIsSaving( true );
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
			setIsSaving( false );
			return;
		}

		setIsSuccess( true );
		setIsSaving( false );
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
	const isValidating = displayedValidationState.status === 'validating';
	const validationRole =
		displayedValidationState.status === 'invalid' ||
		displayedValidationState.status === 'error'
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
					disabled={ isSaving }
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
								className="outletpro-button-link"
								href="https://adrianduffell.com/tos.html"
								target="_blank"
								rel="noopener noreferrer"
							>
								terms of service
							</a>
						),
						privacy: (
							<a
								className="outletpro-button-link"
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
					onClick={ handleActivate }
					isBusy={ isSaving }
					disabled={ isValidating || isSaving || ! canActivate }
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
