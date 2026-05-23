import OutletIcon from './icon';
import './edit';
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
	title: 'Outlet Products',
	description: 'Show outlet products.',
	icon: OutletIcon,
} );
