import { escapeHTML } from '@wordpress/escape-html';

export function Sample(): JSX.Element {
	return (
		<p className="wc-clearance-sample">{ escapeHTML( 'WC Clearance' ) }</p>
	);
}
