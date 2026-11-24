/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { act, fireEvent, render, waitFor } from '@testing-library/react';
import apiFetch from '@wordpress/api-fetch';
import { store as coreStore } from '@wordpress/core-data';
import { createRegistry, RegistryProvider } from '@wordpress/data';
import { expect, test, vi } from 'vitest';
import useSettings from '../../use-settings';
import BadgePresets from '../BadgePresets';

vi.mock( '@wordpress/api-fetch', () => ( { default: vi.fn() } ) );

const presets = [
	{
		name: 'Last chance',
		textColor: '#FFFFFF',
		bgColor: '#F81240',
	},
	{
		name: 'Clearance',
		textColor: '#222',
		bgColor: '#FFEE85',
	},
	{
		name: 'Final sale',
		textColor: '#FFFFFF',
		bgColor: '#000000',
	},
	{
		name: 'Outlet',
		textColor: '#000',
		bgColor: '#EFEFEF',
	},
];

function ConnectedPresets() {
	return <BadgePresets settings={ useSettings() } />;
}

function renderPicker(
	overrides: Record< string, string | number | undefined > = {}
) {
	vi.stubGlobal(
		'matchMedia',
		vi.fn( ( query: string ) => ( {
			matches: query.includes( 'prefers-reduced-motion' ),
			addListener: vi.fn(),
		} ) )
	);
	vi.mocked( apiFetch ).mockResolvedValue( {} );
	const registry = createRegistry();
	registry.register( coreStore );
	const data = registry.dispatch( coreStore );
	data.addEntities( [
		{ kind: 'root', name: 'site', key: false, baseURL: '/wp/v2/settings' },
	] );
	const settings = {
		outletpro_badge_label: 'Last chance',
		outletpro_badge_text_color: '#FFFFFF',
		outletpro_badge_bg_color: '#F81240',
		outletpro_badge_font_weight: '700',
		outletpro_badge_border_radius: '8px',
		outletpro_badge_scale: 180,
		outletpro_badge_density: 60,
		outletpro_message: 'Custom message',
		...overrides,
	};
	data.receiveEntityRecords( 'root', 'site', settings );
	const view = render(
		<RegistryProvider value={ registry }>
			<ConnectedPresets />
		</RegistryProvider>
	);
	const trigger = view.getByRole( 'button', { name: /Change design:/ } );
	fireEvent.click( trigger );
	return {
		...view,
		trigger,
		registry,
		settings,
		getSettings: () =>
			registry
				.select( coreStore )
				.getEditedEntityRecord( 'root', 'site' ),
	};
}

test.each( presets )(
	'previews and immediately applies $name while preserving other settings',
	( preset ) => {
		// Arrange.
		const view = renderPicker();

		// Act.
		const choice = view.getByRole( 'button', { name: preset.name } );

		// Assert.
		const badge = choice.querySelector( '.outlet-badge-preset' );
		expect( badge ).toHaveTextContent( preset.name );
		expect( badge ).toHaveStyle( {
			backgroundColor: preset.bgColor,
			color: preset.textColor,
		} );
		expect( view.getByRole( 'group', { name: 'Presets' } ) ).toHaveStyle( {
			'--outletpro-badge-font-weight': '700',
			'--outletpro-badge-border-radius': '8px',
			'--outletpro-badge-scale': '180',
			'--outletpro-badge-density': '60',
		} );

		// Act.
		fireEvent.click( choice );

		// Assert.
		expect( view.queryByRole( 'dialog' ) ).toBeNull();
		expect( view.trigger ).toHaveTextContent( preset.name );
		expect( view.trigger ).toHaveAccessibleName(
			`Change design: ${ preset.name }`
		);
		expect( view.getSettings() ).toEqual( {
			...view.settings,
			outletpro_badge_label: preset.name,
			outletpro_badge_text_color: preset.textColor,
			outletpro_badge_bg_color: preset.bgColor,
		} );

		// Act.
		fireEvent.click(
			view.getByRole( 'button', { name: /Change design:/ } )
		);

		// Assert.
		expect(
			view.getByRole( 'button', { name: preset.name, pressed: true } )
		).toBeVisible();
		expect( view.getAllByRole( 'button', { pressed: true } ) ).toHaveLength(
			1
		);
	}
);

test.each( [
	{ outletpro_badge_label: 'Custom' },
	{ outletpro_badge_bg_color: '#abcdef' },
	{ outletpro_badge_text_color: '#123456' },
	{ outletpro_badge_text_color: '#fff' },
] )( 'leaves previews unselected for unmatched settings: %j', ( settings ) => {
	// Arrange.
	const view = renderPicker( settings );

	// Act.
	const selected = view.queryByRole( 'button', { pressed: true } );

	// Assert.
	expect( view.trigger ).toHaveTextContent( 'Custom' );
	expect( selected ).toBeNull();
	expect( view.getSettings() ).toEqual( view.settings );
} );

test( 'updates the design name when settings change', async () => {
	// Arrange.
	const view = renderPicker();

	// Assert.
	expect(
		view.getByRole( 'button', { name: 'Last chance', pressed: true } )
	).toBeVisible();
	// Initial focus stays off the cards to avoid a second highlighted design.
	await waitFor( () => expect( view.getByRole( 'dialog' ) ).toHaveFocus() );

	// Act.
	fireEvent.click( view.getByRole( 'button', { name: 'Close' } ) );
	act( () => {
		view.registry
			.dispatch( coreStore )
			.editEntityRecord( 'root', 'site', undefined, {
				outletpro_badge_bg_color: '#abcdef',
			} );
	} );

	// Assert.
	expect( view.trigger ).toHaveTextContent( 'Custom' );
} );

test.each( [ 'Close', 'Escape' ] )(
	'closes on %s without changing settings and matches again when reopened',
	async ( action ) => {
		// Arrange.
		const view = renderPicker();

		// Act.
		if ( action === 'Escape' ) {
			fireEvent.keyDown( view.getByRole( 'dialog' ), { key: 'Escape' } );
		} else {
			fireEvent.click( view.getByRole( 'button', { name: action } ) );
		}
		await waitFor( () =>
			expect( view.queryByRole( 'dialog' ) ).toBeNull()
		);
		fireEvent.click(
			view.getByRole( 'button', { name: /Change design:/ } )
		);

		// Assert.
		expect( view.trigger ).toHaveTextContent( 'Last chance' );
		expect(
			view.getByRole( 'button', {
				name: 'Last chance',
				pressed: true,
			} )
		).toBeVisible();
		expect( view.getSettings() ).toEqual( view.settings );
	}
);
