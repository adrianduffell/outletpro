/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import type { ReactNode } from 'react';

export type EditorChange = {
	id: string;
	label: string;
	description?: string;
	details?: ReactNode;
};

export type ChangeSource = {
	id: string;
	label: string;
	changes: EditorChange[];
	save: ( selectedIds: string[] ) => Promise< void >;
};

export type ChangeSelection = Record< string, string[] >;
