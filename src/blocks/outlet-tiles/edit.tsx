/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import metadata from './block.json';

export function Edit(): JSX.Element {
	const blockProps = useBlockProps();
	const innerBlocksProps = useInnerBlocksProps( blockProps, {
		allowedBlocks: metadata.allowedBlocks,
		defaultBlock: { name: 'outletpro/outlet-tile' },
		directInsert: true,
	} );
	return <div { ...innerBlocksProps } />;
}
