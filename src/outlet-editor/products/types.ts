/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import type { RestProduct } from '../../data/products/types';

export type Product = RestProduct & {
	image: string | null;
	price: string;
	age_days: number | null;
	canEdit: boolean;
};
