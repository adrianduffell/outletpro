/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import {
	InnerBlocks,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';

export function Edit(): JSX.Element {
	const blockProps = useBlockProps();
	const innerBlocksProps = useInnerBlocksProps( blockProps, {
		renderAppender: InnerBlocks.ButtonBlockAppender,
	} );

	return <div { ...innerBlocksProps } />;
}
