/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import {
	Button,
	Notice,
	SnackbarList,
	Toolbar,
	ToolbarButton,
	ToolbarGroup,
	ToolbarItem,
} from '@wordpress/components';
import { store as commandsStore } from '@wordpress/commands';
import { useDispatch } from '@wordpress/data';
import { useCallback, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	Icon,
	chevronLeft,
	drawerRight,
	external,
	layout,
} from '@wordpress/icons';
import PageDetails from './PageDetails';
import Products from './Products';
import ReviewChanges from './ReviewChanges';
import useProductChanges from './useProductChanges';
import type { ManagerConfig } from './types';
import useOutletPage from './useOutletPage';

export default function Manager( { config }: { config: ManagerConfig } ) {
	const products = useProductChanges();
	const details = useOutletPage( config.pageId, products.changes.length > 0 );
	const saveButton = useRef< HTMLButtonElement | null >( null );
	const saving = useRef( false );
	const [ busy, setBusy ] = useState( false );
	const [ reviewing, setReviewing ] = useState( false );
	const [ status, setStatus ] = useState< 'publish' | 'draft' | undefined >();
	const [ notices, setNotices ] = useState<
		{ id: string; content: string; explicitDismiss: boolean }[]
	>( [] );
	const noticeId = useRef( 0 );
	const showSaveError = useCallback( ( content: string ) => {
		noticeId.current += 1;
		setNotices( ( current ) => [
			...current,
			{ id: noticeId.current.toString(), content, explicitDismiss: true },
		] );
	}, [] );
	function review( nextStatus?: 'publish' | 'draft' ) {
		setStatus( nextStatus );
		setReviewing( true );
	}
	function closeReview() {
		setReviewing( false );
		saveButton.current?.focus();
	}
	async function save( productIds: number[], includePage: boolean ) {
		if ( saving.current ) {
			return;
		}
		saving.current = true;
		setBusy( true );
		try {
			const productsSaved = await products.save(
				showSaveError,
				productIds
			);
			if ( ! productsSaved ) {
				return;
			}
			if ( includePage && ( details.dirty || status !== undefined ) ) {
				const pageSaved = await details.save( status, showSaveError );
				if ( ! pageSaved ) {
					return;
				}
			}
			closeReview();
		} finally {
			saving.current = false;
			setBusy( false );
		}
	}
	const { open: openCommandCenter } = useDispatch( commandsStore );
	const sidebarToggle = useRef< HTMLButtonElement | null >( null );
	const [ sidebarOpen, setSidebarOpen ] = useState(
		() => window.matchMedia( '(min-width: 960px)' ).matches
	);
	return (
		<div className="outletpro-manager">
			<header className="outletpro-manager-header">
				<Toolbar
					label={ __( 'Outlet navigation', 'outletpro' ) }
					className="outletpro-manager-navigation"
				>
					<ToolbarItem>
						{ ( itemProps ) => (
							<Button
								{ ...itemProps }
								size="compact"
								href={
									new URL(
										'edit.php?post_type=page',
										window.location.href
									).href
								}
								icon={ chevronLeft }
								label={ __( 'Back to pages', 'outletpro' ) }
							/>
						) }
					</ToolbarItem>
				</Toolbar>
				<Button
					className="outletpro-manager-document"
					size="compact"
					onClick={ openCommandCenter }
					label={ __( 'Search commands and settings', 'outletpro' ) }
					aria-haspopup="dialog"
				>
					<Icon
						icon={ layout }
						className="outletpro-manager-document-icon"
						size={ 24 }
					/>
					<span className="outletpro-manager-document-title">
						{ details.title || __( 'Outlet', 'outletpro' ) }
						<span className="outletpro-manager-document-type">
							{ __( '· Page', 'outletpro' ) }
						</span>
					</span>
				</Button>
				<Toolbar
					label={ __( 'Page actions', 'outletpro' ) }
					className="outletpro-manager-header-actions"
				>
					<ToolbarGroup>
						{ details.page !== null && (
							<ToolbarItem>
								{ ( itemProps ) => (
									<Button
										{ ...itemProps }
										size="compact"
										icon={ external }
										href={ details.page?.link }
										target="_blank"
										rel="noopener noreferrer"
										label={ __(
											'View outlet',
											'outletpro'
										) }
									/>
								) }
							</ToolbarItem>
						) }
						<ToolbarButton
							ref={ sidebarToggle }
							size="compact"
							icon={ drawerRight }
							label={ __( 'Page settings', 'outletpro' ) }
							isPressed={ sidebarOpen && ! reviewing }
							aria-expanded={ sidebarOpen && ! reviewing }
							aria-controls="outletpro-manager-sidebar"
							onClick={ () => {
								setReviewing( false );
								setSidebarOpen( reviewing || ! sidebarOpen );
							} }
							disabled={ busy }
						/>
						<ToolbarButton
							ref={ saveButton }
							size="compact"
							variant="primary"
							disabled={
								details.page === null ||
								busy ||
								( ! details.dirty &&
									products.changes.length === 0 )
							}
							isBusy={ busy }
							onClick={ () => review() }
						>
							{ __( 'Save', 'outletpro' ) }
						</ToolbarButton>
						{ details.page !== null &&
							details.page.status !== 'publish' &&
							config.canPublish && (
								<ToolbarButton
									size="compact"
									variant="secondary"
									disabled={ busy }
									onClick={ () => review( 'publish' ) }
								>
									{ __( 'Publish', 'outletpro' ) }
								</ToolbarButton>
							) }
					</ToolbarGroup>
				</Toolbar>
			</header>
			<div
				className={ `outletpro-manager-layout${ sidebarOpen || reviewing ? ' is-sidebar-open' : '' }` }
			>
				<main className="outletpro-manager-workspace">
					{ details.page === null && details.error && (
						<Notice status="error" isDismissible={ false }>
							{ details.error }
						</Notice>
					) }
					{ config.canManageProducts ? (
						<Products
							categories={ config.categories }
							changes={ products }
							disabled={ busy }
						/>
					) : (
						<Notice status="info" isDismissible={ false }>
							{ __(
								'You do not have permission to manage products.',
								'outletpro'
							) }
						</Notice>
					) }
				</main>
				{ reviewing ? (
					<ReviewChanges
						details={ details }
						changes={ products.changes }
						status={ status }
						busy={ busy }
						onSave={ save }
						onClose={ closeReview }
					/>
				) : (
					sidebarOpen && (
						<PageDetails
							details={ details }
							config={ config }
							onUnpublish={ () => review( 'draft' ) }
							onClose={ () => {
								setSidebarOpen( false );
								sidebarToggle.current?.focus();
							} }
						/>
					)
				) }
			</div>
			<SnackbarList
				className="outletpro-manager-snackbars"
				notices={ notices }
				onRemove={ ( id ) =>
					setNotices( ( current ) =>
						current.filter( ( notice ) => notice.id !== id )
					)
				}
			/>
		</div>
	);
}
