/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { act, renderHook, waitFor } from '@testing-library/react';
import type { LicenseStatus } from '../validateLicense';
import { validateLicense } from '../validateLicense';
import { useLicenseValidation } from '../useLicenseValidation';

jest.mock( '../validateLicense', () => ( {
	validateLicense: jest.fn(),
} ) );

const LICENSE_KEY = '38B1460A-5104-4067-A91D-77B872934D51';
const OTHER_LICENSE_KEY = '28B1460A-5104-4067-A91D-77B872934D51';
const LICENSE_AVAILABLE: LicenseStatus = {
	valid: true,
	remaining: 3,
	total: 5,
};
const mockValidateLicense = jest.mocked( validateLicense );

test( 'does not validate a short prefilled license key', () => {
	// Arrange.
	mockValidateLicense.mockReset();

	// Act.
	const { result } = renderHook( () => useLicenseValidation( 'ABCD-1234' ) );

	// Assert.
	expect( mockValidateLicense ).not.toHaveBeenCalled();
	expect( result.current.validationState ).toEqual( { status: 'idle' } );
} );

test( 'validates a 36-character prefilled license key', async () => {
	// Arrange.
	mockValidateLicense.mockReset();
	mockValidateLicense.mockReturnValue( new Promise( () => undefined ) );

	// Act.
	const { result } = renderHook( () => useLicenseValidation( LICENSE_KEY ) );

	// Assert.
	await waitFor( () =>
		expect( mockValidateLicense ).toHaveBeenCalledWith( LICENSE_KEY )
	);
	expect( result.current.validationState ).toEqual( {
		status: 'validating',
	} );
} );

test( 'normalizes the license key', () => {
	// Arrange.
	mockValidateLicense.mockReset();
	const { result } = renderHook( () => useLicenseValidation( '' ) );

	// Act.
	act( () => result.current.handleLicenseKeyChange( 'abcd-1234' ) );

	// Assert.
	expect( result.current.licenseKey ).toBe( 'ABCD-1234' );
} );

test( 'trims the license key', () => {
	// Arrange.
	mockValidateLicense.mockReset();
	const { result } = renderHook( () => useLicenseValidation( '' ) );

	// Act.
	act( () => result.current.handleLicenseKeyChange( '  ABCD-1234  ' ) );

	// Assert.
	expect( result.current.licenseKey ).toBe( 'ABCD-1234' );
} );

test( 'validates a changed license key at exactly 36 characters', () => {
	// Arrange.
	mockValidateLicense.mockReset();
	mockValidateLicense.mockReturnValue( new Promise( () => undefined ) );
	const { result } = renderHook( () => useLicenseValidation( '' ) );

	// Act.
	act( () => result.current.handleLicenseKeyChange( LICENSE_KEY ) );

	// Assert.
	expect( mockValidateLicense ).toHaveBeenCalledWith( LICENSE_KEY );
	expect( result.current.validationState ).toEqual( {
		status: 'validating',
	} );
} );

test( 'forces validation regardless of license key length', () => {
	// Arrange.
	mockValidateLicense.mockReset();
	mockValidateLicense.mockReturnValue( new Promise( () => undefined ) );
	const { result } = renderHook( () => useLicenseValidation( '' ) );

	// Act.
	act( () => result.current.handleLicenseKeyChange( 'abcd-1234', true ) );

	// Assert.
	expect( mockValidateLicense ).toHaveBeenCalledWith( 'ABCD-1234' );
	expect( result.current.validationState ).toEqual( {
		status: 'validating',
	} );
} );

test( 'transitions to invalid for an invalid license', async () => {
	// Arrange.
	mockValidateLicense.mockReset();
	mockValidateLicense.mockResolvedValue( { valid: false } );
	const { result } = renderHook( () => useLicenseValidation( '' ) );

	// Act.
	act( () => result.current.handleLicenseKeyChange( LICENSE_KEY ) );

	// Assert.
	await waitFor( () =>
		expect( result.current.validationState ).toEqual( {
			status: 'invalid',
		} )
	);
	expect( result.current.canActivate ).toBe( false );
} );

test( 'transitions to error when validation fails', async () => {
	// Arrange.
	mockValidateLicense.mockReset();
	mockValidateLicense.mockRejectedValue( new Error( 'Network error' ) );
	const { result } = renderHook( () => useLicenseValidation( '' ) );

	// Act.
	act( () => result.current.handleLicenseKeyChange( LICENSE_KEY ) );

	// Assert.
	await waitFor( () =>
		expect( result.current.validationState ).toEqual( { status: 'error' } )
	);
	expect( result.current.canActivate ).toBe( false );
} );

test( 'transitions to available for a license with capacity', async () => {
	// Arrange.
	mockValidateLicense.mockReset();
	mockValidateLicense.mockResolvedValue( LICENSE_AVAILABLE );
	const { result } = renderHook( () => useLicenseValidation( '' ) );

	// Act.
	act( () => result.current.handleLicenseKeyChange( LICENSE_KEY ) );

	// Assert.
	await waitFor( () =>
		expect( result.current.validationState ).toEqual( {
			status: 'available',
			remaining: 3,
			total: 5,
		} )
	);
	expect( result.current.canActivate ).toBe( true );
} );

test( 'transitions to available for a license with unlimited capacity', async () => {
	// Arrange.
	mockValidateLicense.mockReset();
	mockValidateLicense.mockResolvedValue( {
		valid: true,
		remaining: Infinity,
		total: Infinity,
	} );
	const { result } = renderHook( () => useLicenseValidation( '' ) );

	// Act.
	act( () => result.current.handleLicenseKeyChange( LICENSE_KEY ) );

	// Assert.
	await waitFor( () =>
		expect( result.current.validationState ).toEqual( {
			status: 'available',
			remaining: Infinity,
			total: Infinity,
		} )
	);
	expect( result.current.canActivate ).toBe( true );
} );

test( 'transitions to unavailable for a license without capacity', async () => {
	// Arrange.
	mockValidateLicense.mockReset();
	mockValidateLicense.mockResolvedValue( {
		valid: true,
		remaining: 0,
		total: 5,
	} );
	const { result } = renderHook( () => useLicenseValidation( '' ) );

	// Act.
	act( () => result.current.handleLicenseKeyChange( LICENSE_KEY ) );

	// Assert.
	await waitFor( () =>
		expect( result.current.validationState ).toEqual( {
			status: 'unavailable',
			total: 5,
		} )
	);
	expect( result.current.canActivate ).toBe( false );
} );

test( 'resets validation after editing a validated license key', async () => {
	// Arrange.
	mockValidateLicense.mockReset();
	mockValidateLicense.mockResolvedValue( LICENSE_AVAILABLE );
	const { result } = renderHook( () => useLicenseValidation( '' ) );
	act( () => result.current.handleLicenseKeyChange( LICENSE_KEY ) );
	await waitFor( () => expect( result.current.canActivate ).toBe( true ) );

	// Act.
	act( () => result.current.handleLicenseKeyChange( 'ABCD' ) );

	// Assert.
	expect( result.current.validationState ).toEqual( { status: 'idle' } );
	expect( result.current.canActivate ).toBe( false );
} );

test( 'disables activation while revalidating a license key', async () => {
	// Arrange.
	mockValidateLicense.mockReset();
	mockValidateLicense.mockResolvedValueOnce( LICENSE_AVAILABLE );
	const { result } = renderHook( () => useLicenseValidation( '' ) );
	act( () => result.current.handleLicenseKeyChange( LICENSE_KEY ) );
	await waitFor( () => expect( result.current.canActivate ).toBe( true ) );
	mockValidateLicense.mockReturnValueOnce( new Promise( () => undefined ) );

	// Act.
	act( () => result.current.handleLicenseKeyChange( OTHER_LICENSE_KEY ) );

	// Assert.
	expect( result.current.validationState ).toEqual( {
		status: 'validating',
	} );
	expect( result.current.canActivate ).toBe( false );
} );

test( 'ignores a stale validation response', async () => {
	// Arrange.
	mockValidateLicense.mockReset();
	let resolveFirstRequest: ( value: LicenseStatus ) => void = () => undefined;
	mockValidateLicense.mockReturnValueOnce(
		new Promise( ( resolve ) => {
			resolveFirstRequest = resolve;
		} )
	);
	mockValidateLicense.mockResolvedValueOnce( { valid: false } );
	const { result } = renderHook( () => useLicenseValidation( '' ) );

	// Act.
	act( () => result.current.handleLicenseKeyChange( LICENSE_KEY ) );
	act( () => result.current.handleLicenseKeyChange( OTHER_LICENSE_KEY ) );
	await waitFor( () =>
		expect( result.current.validationState ).toEqual( {
			status: 'invalid',
		} )
	);
	await act( async () => resolveFirstRequest( LICENSE_AVAILABLE ) );

	// Assert.
	expect( result.current.validationState ).toEqual( { status: 'invalid' } );
} );
