/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

export type RestProduct = {
	id: number;
	name: string;
	sku: string;
	type: string;
	status: string;
	images: { src: string; thumbnail?: string }[];
	price_html: string;
	date_created_gmt: string | null;
	stock_status: string;
	manage_stock: boolean;
	stock_quantity: number | null;
	total_sales: number;
	categories: { id: number; name: string }[];
	outletpro: { is_outlet: boolean };
	_links?: { self?: { href: string; targetHints?: { allow: string[] } }[] };
};
