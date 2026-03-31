/* global wcClearance */

const { registerProductCollection } = window.wc.blocksRegistry;

registerProductCollection( {
	name: 'wc-clearance/clearance',
	title: 'Clearance',
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
