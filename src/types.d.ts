/**
 * Type declarations for WordPress external packages used by wp-scripts.
 *
 * These packages are treated as webpack externals by @wordpress/dependency-extraction-webpack-plugin
 * and are provided at runtime by WordPress. Type declarations are required here for TypeScript.
 */

declare module '@wordpress/element' {
	export { useEffect } from 'react';
}

declare module '@wordpress/data' {
	// eslint-disable-next-line @typescript-eslint/no-explicit-any
	export function dispatch( storeNameOrDescriptor: string ): any;
}

declare module '@wordpress/plugins' {
	import type { ComponentType } from 'react';
	export function registerPlugin(
		name: string,
		options: { render: ComponentType }
	): void;
}
