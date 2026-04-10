import ClearanceIcon from './icon';
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
	name: 'wc-clearance/product-collection/clearance',
	title: 'Clearance Section',
	description: 'Show products in the clearance section.',
	icon: ClearanceIcon,
} );
