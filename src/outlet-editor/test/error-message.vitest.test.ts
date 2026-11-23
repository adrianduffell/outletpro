import { expect, test } from 'vitest';
import { getErrorMessage } from '../error-message';

test( 'reads a REST error message', () => {
	// Arrange.
	const error = { message: 'Permission denied.' };
	// Act.
	const message = getErrorMessage( error, 'Request failed.' );
	// Assert.
	expect( message ).toBe( 'Permission denied.' );
} );

test.each( [ null, undefined, 'Failure', { message: 42 } ] )(
	'uses a safe fallback for an unexpected error: %j',
	( error ) => {
		// Act.
		const message = getErrorMessage( error, 'Request failed.' );
		// Assert.
		expect( message ).toBe( 'Request failed.' );
	}
);
