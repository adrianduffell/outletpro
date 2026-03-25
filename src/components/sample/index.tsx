import { useState } from '@wordpress/element';

export function Sample(): JSX.Element {
	const [ label ] = useState( 'WC Clearance' );
	return <p className="wc-clearance-sample">{ label }</p>;
}
