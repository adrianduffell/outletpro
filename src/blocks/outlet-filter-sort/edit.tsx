import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export function Edit(): JSX.Element {
	const blockProps = useBlockProps();

	return (
		<div { ...blockProps }>
			<select disabled>
				<option>{ __( 'Default sorting', 'wc-outlet' ) }</option>
			</select>
		</div>
	);
}
