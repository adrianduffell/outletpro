/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { fireEvent, render, within } from '@testing-library/react';
import apiFetch from '@wordpress/api-fetch';
import { store as blockEditorStore } from '@wordpress/block-editor';
import { store as coreStore } from '@wordpress/core-data';
import { createRegistry, RegistryProvider } from '@wordpress/data';
import { expect, test, vi } from 'vitest';
import useSettings from '../../use-settings';
import BadgeSettings from '../BadgeSettings';

vi.mock( '@wordpress/api-fetch', () => ( { default: vi.fn() } ) );

function ConnectedSettings() {
	return <BadgeSettings settings={ useSettings() } />;
}

function renderSettings(
	overrides: Record< string, string | number | undefined > = {}
) {
	vi.mocked( apiFetch ).mockResolvedValue( {} );
	const registry = createRegistry();
	registry.register( coreStore );
	registry.register( blockEditorStore );
	const data = registry.dispatch( coreStore );
	data.addEntities( [
		{ kind: 'root', name: 'site', key: false, baseURL: '/wp/v2/settings' },
	] );
	data.receiveEntityRecords( 'root', 'site', {
		outletpro_badge_label: 'Clearance',
		outletpro_badge_text_color: '#222',
		outletpro_badge_bg_color: '#FFEE85',
		outletpro_badge_font_weight: '700',
		outletpro_badge_border_color: '#123456',
		outletpro_badge_border_style: 'solid',
		outletpro_badge_border_width: '2px',
		outletpro_badge_border_radius: '8px',
		outletpro_badge_scale: 180,
		outletpro_badge_density: 60,
		...overrides,
	} );
	return render(
		<RegistryProvider value={ registry }>
			<ConnectedSettings />
		</RegistryProvider>
	);
}

test( 'previews the current badge between the description and design picker', () => {
	// Arrange.
	const view = renderSettings();

	// Act.
	const preview = view.getByRole( 'group', { name: 'Badge preview' } );
	const badge = within( preview ).getByText( 'Clearance' );
	const description = view.getByText( /Customize the appearance/ );
	const design = view.getByRole( 'button', {
		name: 'Change design: Clearance',
	} );

	// Assert.
	expect( description.compareDocumentPosition( preview ) ).toBe(
		Node.DOCUMENT_POSITION_FOLLOWING
	);
	expect( preview.compareDocumentPosition( design ) ).toBe(
		Node.DOCUMENT_POSITION_FOLLOWING
	);
	expect( badge ).toHaveClass( 'outletpro-badge' );
	expect( badge ).toHaveStyle( {
		'--outletpro-context-font-size': '16px',
		'--outletpro-badge-bg-color': '#FFEE85',
		'--outletpro-badge-text-color': '#222',
		'--outletpro-badge-font-weight': '700',
		'--outletpro-badge-border-color': '#123456',
		'--outletpro-badge-border-style': 'solid',
		'--outletpro-badge-border-width': '2px',
		'--outletpro-badge-border-radius': '8px',
		'--outletpro-badge-scale': '180',
		'--outletpro-badge-density': '60',
	} );
} );

test( 'updates the preview immediately when choosing a design or editing its label', () => {
	// Arrange.
	const view = renderSettings();
	const preview = view.getByRole( 'group', { name: 'Badge preview' } );

	// Act.
	fireEvent.click(
		view.getByRole( 'button', { name: 'Change design: Clearance' } )
	);
	fireEvent.click( view.getByRole( 'button', { name: 'Final sale' } ) );

	// Assert.
	expect( within( preview ).getByText( 'Final sale' ) ).toHaveStyle( {
		'--outletpro-badge-bg-color': '#000000',
		'--outletpro-badge-text-color': '#FFFFFF',
	} );

	// Act.
	fireEvent.change( view.getByRole( 'textbox', { name: 'Label' } ), {
		target: { value: 'Final reductions' },
	} );

	// Assert.
	expect( preview ).toHaveTextContent( 'Final reductions' );
	expect( within( preview ).queryByText( 'Final sale' ) ).toBeNull();
} );

test( 'uses default preview styles when badge settings are unset', () => {
	// Arrange.
	const view = renderSettings( {
		outletpro_badge_label: undefined,
		outletpro_badge_text_color: '',
		outletpro_badge_bg_color: undefined,
		outletpro_badge_font_weight: '',
		outletpro_badge_border_color: undefined,
		outletpro_badge_border_style: '',
		outletpro_badge_border_width: undefined,
		outletpro_badge_border_radius: '',
		outletpro_badge_scale: undefined,
		outletpro_badge_density: undefined,
	} );

	// Act.
	const preview = view.getByRole( 'group', { name: 'Badge preview' } );
	const badge = preview.querySelector< HTMLElement >( '.outletpro-badge' );

	// Assert.
	expect( badge ).toBeEmptyDOMElement();
	for ( const property of [
		'--outletpro-badge-bg-color',
		'--outletpro-badge-text-color',
		'--outletpro-badge-font-weight',
		'--outletpro-badge-border-color',
		'--outletpro-badge-border-style',
		'--outletpro-badge-border-width',
		'--outletpro-badge-border-radius',
	] ) {
		expect( badge?.style.getPropertyValue( property ) ).toBe( 'initial' );
	}
	expect( badge ).toHaveStyle( {
		'--outletpro-badge-scale': '166',
		'--outletpro-badge-density': '50',
	} );
} );
