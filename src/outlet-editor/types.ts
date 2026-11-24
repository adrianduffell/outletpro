/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

export type EditorConfig = { pageId: number; canPublish: boolean };

export type EditablePageStatus = 'draft' | 'publish';

export type PageRecord = {
	id: number;
	title: { raw: string };
	slug: string;
	status: string;
	link: string;
};
