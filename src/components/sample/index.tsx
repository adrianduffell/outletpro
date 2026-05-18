import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from '@wordpress/element';

export function Sample(): JSX.Element {
	const [ label, setLabel ] = useState( '' );

	useEffect( () => {
		apiFetch< { name: string }[] >( {
			path: '/wc/v3/products?per_page=1',
		} ).then( ( products ) => {
			setLabel( products[ 0 ]?.name ?? '' );
		} );
	}, [] );

	return <p className="wc-outlet-sample">{ label }</p>;
}
