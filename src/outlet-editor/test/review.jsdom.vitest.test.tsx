/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { fireEvent, render } from '@testing-library/react';
import { expect, test, vi } from 'vitest';
import ReviewChanges from '../ReviewChanges';

test( 'excludes unticked changes without removing their review rows', () => {
	// Arrange.
	const save = vi.fn();
	const view = render(
		<ReviewChanges
			sources={ [
				{
					id: 'feature',
					label: 'Feature changes',
					changes: [
						{ id: 'one', label: 'First' },
						{
							id: 'two',
							label: 'Second',
							description: 'Update setting',
						},
					],
					save: async () => {},
				},
			] }
			busy={ false }
			failed={ false }
			publishing={ false }
			onSave={ save }
			onClose={ vi.fn() }
		/>
	);

	// Act.
	fireEvent.click( view.getByRole( 'checkbox', { name: 'First' } ) );
	fireEvent.click( view.getByRole( 'button', { name: 'Save' } ) );

	// Assert.
	expect( save ).toHaveBeenCalledWith( { feature: [ 'two' ] } );
	expect( view.getByRole( 'checkbox', { name: 'First' } ) ).not.toBeChecked();
	expect(
		view.getByText( 'There are 2 changes waiting to be saved.' )
	).toBeVisible();
} );

test( 'locks save and selection after failure while allowing cancellation', () => {
	// Arrange.
	const close = vi.fn();
	const view = render(
		<ReviewChanges
			sources={ [
				{
					id: 'page',
					label: 'Page details',
					changes: [ { id: 'details', label: 'Page details' } ],
					save: async () => {},
				},
			] }
			busy={ false }
			failed
			publishing
			onSave={ vi.fn() }
			onClose={ close }
		/>
	);

	// Act.
	fireEvent.keyDown( view.getByRole( 'button', { name: 'Cancel' } ), {
		key: 'Escape',
	} );

	// Assert.
	expect( view.getByRole( 'checkbox' ) ).toBeDisabled();
	expect( view.getByRole( 'button', { name: 'Publish' } ) ).toBeDisabled();
	expect( close ).toHaveBeenCalledOnce();
} );
