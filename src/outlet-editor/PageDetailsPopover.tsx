/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import type { ComponentProps, ReactNode } from 'react';
import {
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis -- Match WordPress's inspector popovers.
	__experimentalInspectorPopoverHeader as InspectorPopoverHeader,
} from '@wordpress/block-editor';
import { Button, Dropdown } from '@wordpress/components';

export default function PageDetailsPopover( {
	title,
	label,
	value,
	icon,
	anchor,
	offset = 8,
	contentClassName = '',
	disabled,
	children,
}: {
	title: string;
	label?: string;
	value?: string;
	icon?: ComponentProps< typeof Button >[ 'icon' ];
	anchor?: HTMLElement | null;
	offset?: number;
	contentClassName?: string;
	disabled: boolean;
	children: ( onClose: () => void ) => ReactNode;
} ) {
	return (
		<Dropdown
			className="outletpro-editor-page-dropdown"
			contentClassName={ `outletpro-editor-page-popover ${ contentClassName }` }
			focusOnMount="firstInputElement"
			popoverProps={ {
				...( anchor ? { anchor } : {} ),
				placement: 'left-start',
				offset,
				shift: true,
				resize: false,
				noArrow: true,
				role: 'dialog',
				'aria-label': title,
			} }
			renderToggle={ ( { isOpen, onToggle } ) => (
				<Button
					className="outletpro-editor-page-value"
					variant="tertiary"
					size="compact"
					icon={ icon }
					label={ label }
					disabled={ disabled }
					aria-haspopup="dialog"
					aria-expanded={ isOpen }
					onClick={ onToggle }
				>
					{ value }
				</Button>
			) }
			renderContent={ ( { onClose } ) => (
				<>
					<InspectorPopoverHeader
						title={ title }
						onClose={ onClose }
					/>
					<form
						className="outletpro-editor-page-popover-form"
						onSubmit={ ( event ) => {
							event.preventDefault();
							onClose();
						} }
					>
						{ children( onClose ) }
					</form>
				</>
			) }
		/>
	);
}
