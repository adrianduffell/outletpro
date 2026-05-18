import OutletIcon from './icon';
export {};

declare global {
	interface Window {
		wc: {
			wcBlocksRegistry: {
				__experimentalRegisterProductCollection: ( config: {
					name: string;
					title: string;
					description: string;
					icon?: string | JSX.Element;
				} ) => void;
			};
		};
	}
}

window.wc.wcBlocksRegistry.__experimentalRegisterProductCollection( {
	name: 'wc-outlet/product-collection/outlet',
	title: 'Outlet',
	description: 'Show outlet products.',
	icon: OutletIcon,
} );
