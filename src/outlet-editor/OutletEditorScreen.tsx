/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { Notice, Spinner } from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import OutletEditor from './OutletEditor';
import Products from './products/Products';
import type { EditorConfig } from './types';

export default function OutletEditorScreen( {
	config,
}: {
	config: EditorConfig;
} ) {
	const permissions = useSelect( ( select ) => {
		const data = select( coreStore );
		// WordPress maps product creation permission to edit_products.
		const canManageProducts = data.canUser( 'create', 'product' );
		const finished = data.hasFinishedResolution( 'canUser', [
			'create',
			'product',
		] );
		return {
			canManageProducts: canManageProducts === true,
			loading: ! finished,
			error: finished && canManageProducts === undefined,
		};
	}, [] );
	return (
		<OutletEditor
			config={ config }
			workspace={ ( state ) => {
				if ( permissions.error ) {
					return (
						<Notice status="error" isDismissible={ false }>
							{ __(
								'Product editing permission could not be loaded.',
								'outletpro'
							) }
						</Notice>
					);
				}
				if ( permissions.loading ) {
					return <Spinner />;
				}
				if ( ! permissions.canManageProducts ) {
					return (
						<Notice isDismissible={ false }>
							{ __(
								'You do not have permission to manage products.',
								'outletpro'
							) }
						</Notice>
					);
				}
				return <Products disabled={ state.disabled } />;
			} }
		/>
	);
}
