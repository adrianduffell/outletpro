/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import type { CSSProperties } from 'react';
import { Button, Dropdown, Flex, FlexBlock } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { closeSmall } from '@wordpress/icons';
import type { Settings } from '../use-settings';
import './style.css';

const PRESETS = [
	{
		id: 'last-chance',
		name: __( 'Last Chance', 'outletpro' ),
		textColor: '#FFFFFF',
		bgColor: '#F81240',
	},
	{
		id: 'clearance',
		name: __( 'Clearance', 'outletpro' ),
		textColor: '#222',
		bgColor: '#FFEE85',
	},
	{
		id: 'final-sale',
		name: __( 'Final sale', 'outletpro' ),
		textColor: '#FFFFFF',
		bgColor: '#000000',
	},
	{
		id: 'outlet',
		name: __( 'Outlet', 'outletpro' ),
		textColor: '#000',
		bgColor: '#EFEFEF',
	},
];

export default function BadgePresets( {
	settings,
}: {
	settings: Settings;
} ): JSX.Element {
	const [ popoverAnchor, setPopoverAnchor ] =
		useState< HTMLDivElement | null >( null );
	const currentPreset = PRESETS.find(
		( preset ) =>
			preset.name === settings.label &&
			preset.textColor === settings.textColor &&
			preset.bgColor === settings.bgColor
	);
	const designName = currentPreset?.name ?? __( 'Custom', 'outletpro' );
	const previewStyle: CSSProperties &
		Record< `--${ string }`, string | number > = {
		'--outletpro-context-font-size': '16px',
		'--outletpro-badge-font-weight': settings.fontWeight || 'unset',
		'--outletpro-badge-border-radius': settings.borderRadius || 'unset',
		'--outletpro-badge-scale': settings.scale ?? 166,
		'--outletpro-badge-density': settings.density ?? 50,
	};

	return (
		<Flex ref={ setPopoverAnchor }>
			<FlexBlock>{ __( 'Design', 'outletpro' ) }</FlexBlock>
			<FlexBlock>
				<Dropdown
					contentClassName="outletpro-badge-presets-popover"
					focusOnMount
					expandOnMobile
					headerTitle={ __( 'Choose a design', 'outletpro' ) }
					popoverProps={ {
						anchor: popoverAnchor,
						role: 'dialog',
						'aria-label': __( 'Choose a design', 'outletpro' ),
						placement: 'left-start',
						offset: 36,
						shift: true,
						resize: false,
						noArrow: true,
					} }
					renderToggle={ ( { isOpen, onToggle } ) => (
						<Button
							variant="tertiary"
							size="compact"
							className="outletpro-badge-presets__trigger"
							aria-label={ sprintf(
								// translators: %s: Current badge design name.
								__( 'Change design: %s', 'outletpro' ),
								designName
							) }
							aria-haspopup="dialog"
							aria-expanded={ isOpen }
							onClick={ onToggle }
						>
							{ designName }
						</Button>
					) }
					renderContent={ ( { onClose } ) => (
						<>
							<Flex className="outletpro-badge-presets__header">
								<h2>
									{ __( 'Choose a design', 'outletpro' ) }
								</h2>
								<Button
									size="small"
									label={ __( 'Close', 'outletpro' ) }
									icon={ closeSmall }
									onClick={ onClose }
								/>
							</Flex>
							<div
								className="outletpro-badge-presets__grid"
								style={ previewStyle }
								role="group"
								aria-label={ __( 'Presets', 'outletpro' ) }
							>
								{ PRESETS.map( ( preset ) => (
									<Button
										key={ preset.id }
										className="outletpro-badge-presets__choice"
										aria-label={ preset.name }
										isPressed={
											currentPreset?.id === preset.id
										}
										onClick={ () => {
											settings.setLabel( preset.name );
											settings.setTextColor(
												preset.textColor
											);
											settings.setBgColor(
												preset.bgColor
											);
											onClose();
										} }
									>
										<span
											className="outlet-badge-preset"
											style={ {
												backgroundColor: preset.bgColor,
												color: preset.textColor,
											} }
										>
											{ preset.name }
										</span>
									</Button>
								) ) }
							</div>
						</>
					) }
				/>
			</FlexBlock>
		</Flex>
	);
}
