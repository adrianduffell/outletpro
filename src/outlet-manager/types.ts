/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

export type OutletTier = { id: number; name: string };

export type ManagerConfig = {
	pageId: number;
	canPublish: boolean;
	canManageProducts: boolean;
	settingsUrl: string | null;
	categories: { value: number; label: string }[];
	discountTiers: OutletTier[] | null;
	priceBands: OutletTier[] | null;
};

export type Product = {
	id: number;
	name: string;
	image: string;
	sku: string;
	/** WooCommerce price markup sanitized by the REST endpoint. */
	price: string;
	stock_status: string;
	manage_stock: boolean;
	stock_quantity: number | null;
	/** Total units sold reported by WooCommerce. */
	total_sales: number;
	/** Completed 24-hour periods since product creation; null when unavailable. */
	age_days: number | null;
	status: string;
	outlet: boolean;
	canEdit: boolean;
	editUrl: string | null;
	categories: { id: number; name: string }[];
};

export type ProductsResponse = {
	items: Product[];
	totalItems: number;
	totalPages: number;
	totalOutletItems: number;
};

export type BatchResponse = {
	results: { id: number; outlet?: boolean; error?: string }[];
};

export type PageRecord = {
	id: number;
	title: { raw: string };
	slug: string;
	status: string;
	link: string;
};
