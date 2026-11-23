/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { Notice, Spinner } from '@wordpress/components';
import { Icon, page as pageIcon } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';
import type { PageRecord } from './types';
import './page-details.css';

const statuses: Record< string, string > = {
	publish: __( 'Published', 'outletpro' ),
	draft: __( 'Draft', 'outletpro' ),
	future: __( 'Scheduled', 'outletpro' ),
	private: __( 'Private', 'outletpro' ),
	pending: __( 'Pending review', 'outletpro' ),
};

export default function PageDetails( {
	page,
	error,
}: {
	page: PageRecord | null;
	error: string;
} ) {
	if ( error ) {
		return (
			<Notice status="error" isDismissible={ false }>
				{ error }
			</Notice>
		);
	}
	if ( page === null ) {
		return <Spinner />;
	}
	return (
		<div className="outletpro-editor-page-details">
			<div className="outletpro-editor-page-card">
				<Icon icon={ pageIcon } size={ 24 } />
				<h2>{ page.title.raw || __( 'Outlet', 'outletpro' ) }</h2>
			</div>
			<p>{ __( 'You are customizing the outlet page.', 'outletpro' ) }</p>
			<dl>
				<dt>{ __( 'Status', 'outletpro' ) }</dt>
				<dd>{ statuses[ page.status ] ?? page.status }</dd>
				<dt>{ __( 'Slug', 'outletpro' ) }</dt>
				<dd>{ page.slug }</dd>
			</dl>
		</div>
	);
}
