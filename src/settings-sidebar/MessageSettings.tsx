/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { BaseControl, PanelBody, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import type { Settings } from '../use-settings';

export default function MessageSettings( {
	settings,
}: {
	settings: Settings;
} ) {
	const { message, setMessage } = settings;

	return (
		<>
			<PanelBody>
				<p
					data-testid="outletpro-message-tab-description"
					style={ { marginBottom: 0 } }
				>
					{ __(
						'Customize the outlet message. Changes apply to the whole site.',
						'outletpro'
					) }
				</p>
			</PanelBody>

			<PanelBody title={ __( 'Message', 'outletpro' ) } initialOpen>
				<BaseControl __nextHasNoMarginBottom={ true }>
					<TextareaControl
						label={ __( 'Message', 'outletpro' ) }
						hideLabelFromVision={ true }
						value={ message ?? '' }
						onChange={ ( value ) => setMessage( value ) }
						rows={ 2 }
						__nextHasNoMarginBottom={ true }
						help={ __(
							'Displayed for outlet products.',
							'outletpro'
						) }
					/>
				</BaseControl>
			</PanelBody>
		</>
	);
}
