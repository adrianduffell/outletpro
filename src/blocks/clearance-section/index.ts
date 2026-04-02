export {};

declare const wcClearance: { clearanceTermId: number };

declare global {
	interface Window {
		wc: {
			wcBlocksRegistry: {
				__experimentalRegisterProductCollection: ( config: {
					name: string;
					title: string;
					description: string;
					icon: string;
					attributes: {
						query: {
							taxQuery: Record< string, number[] >;
						};
					};
				} ) => void;
			};
		};
	}
}

window.wc.wcBlocksRegistry.__experimentalRegisterProductCollection( {
	name: 'wc-clearance/clearance',
	title: 'Clearance Section',
	description: 'Show products in the clearance section.',
	icon: 'tag',
	attributes: {
		query: {
			taxQuery: {
				wc_clearance_status: [ wcClearance.clearanceTermId ],
			},
		},
	},
} );
