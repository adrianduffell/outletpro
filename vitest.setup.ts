import '@testing-library/jest-dom/vitest';
import { cleanup } from '@testing-library/react';
import { afterEach, vi } from 'vitest';

// WordPress's viewport store subscribes at import time; jsdom has no matchMedia.
vi.stubGlobal(
	'matchMedia',
	vi.fn( ( media: string ) => ( {
		media,
		matches: false,
		onchange: null,
		addListener: vi.fn(),
		removeListener: vi.fn(),
		addEventListener: vi.fn(),
		removeEventListener: vi.fn(),
		dispatchEvent: vi.fn(),
	} ) )
);

afterEach( cleanup );
