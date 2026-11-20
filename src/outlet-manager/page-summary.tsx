/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { Button } from '@wordpress/components';
import type {
	DataFormControlProps,
	Field,
	Form,
} from '@wordpress/dataviews/wp';
import { __ } from '@wordpress/i18n';
import { Icon, published } from '@wordpress/icons';

export type PageSummary = {
	status: string;
	slug: string;
	busy: boolean;
	unpublish: () => void;
};

const statuses: Record< string, string > = {
	publish: __( 'Published', 'outletpro' ),
	draft: __( 'Draft', 'outletpro' ),
	future: __( 'Scheduled', 'outletpro' ),
	private: __( 'Private', 'outletpro' ),
	pending: __( 'Pending review', 'outletpro' ),
};

function StatusEditor( { data }: DataFormControlProps< PageSummary > ) {
	return (
		<div className="outletpro-manager-status-editor">
			<p>{ statuses[ data.status ] ?? data.status }</p>
			{ data.status === 'publish' ? (
				<Button
					variant="secondary"
					disabled={ data.busy }
					onClick={ data.unpublish }
				>
					{ __( 'Unpublish', 'outletpro' ) }
				</Button>
			) : (
				<p>
					{ __(
						'Publish this page using the toolbar when you are ready.',
						'outletpro'
					) }
				</p>
			) }
		</div>
	);
}

export const PAGE_SUMMARY_FIELDS: Field< PageSummary >[] = [
	{
		id: 'status',
		label: __( 'Status', 'outletpro' ),
		isDisabled: ( { item } ) => item.busy,
		Edit: StatusEditor,
		render: ( { item } ) => (
			<span className="outletpro-manager-status-value">
				{ item.status === 'publish' && (
					<Icon icon={ published } size={ 20 } />
				) }
				{ statuses[ item.status ] ?? item.status }
			</span>
		),
	},
	{
		id: 'slug',
		label: __( 'Slug', 'outletpro' ),
		type: 'text',
		isDisabled: ( { item } ) => item.busy,
	},
];

export const PAGE_SUMMARY_FORM: Form = {
	layout: { type: 'panel' },
	fields: [ 'status', 'slug' ],
};
