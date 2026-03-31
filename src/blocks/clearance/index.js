/* global wcClearance */

const { __experimentalRegisterProductCollection } = window.wc.wcBlocksRegistry;

__experimentalRegisterProductCollection( {
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
