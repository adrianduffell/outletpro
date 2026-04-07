import type { ReactNode } from 'react';
import { render, screen } from '@testing-library/react';
import { ClearanceBadgeForOrder, OrderMetaFill } from '../index';

jest.mock( '@wordpress/plugins', () => ( {
	registerPlugin: jest.fn(),
} ) );

jest.mock( '@wordpress/i18n', () => ( {
	__: jest.fn( ( str: string ) => str ),
} ) );

describe( 'ClearanceBadgeForOrder', () => {
	test( 'renders badge when cart contains a clearance item', () => {
		// Arrange.
		const cart = {
			items: [
				{
					id: 1,
					name: 'Product 1',
					extensions: { 'wc-clearance': { is_clearance: true } },
				},
			],
		};

		// Act.
		render( <ClearanceBadgeForOrder cart={ cart } /> );

		// Assert.
		expect( screen.getByText( 'Clearance' ) ).toBeInTheDocument();
	} );

	test( 'renders nothing when cart items are not clearance', () => {
		// Arrange.
		const cart = {
			items: [
				{
					id: 1,
					name: 'Product 1',
					extensions: { 'wc-clearance': { is_clearance: false } },
				},
			],
		};

		// Act.
		const { container } = render(
			<ClearanceBadgeForOrder cart={ cart } />
		);

		// Assert.
		expect( container ).toBeEmptyDOMElement();
	} );

	test( 'renders nothing when cart items have no clearance extension data', () => {
		// Arrange.
		const cart = {
			items: [ { id: 1, name: 'Product 1', extensions: {} } ],
		};

		// Act.
		const { container } = render(
			<ClearanceBadgeForOrder cart={ cart } />
		);

		// Assert.
		expect( container ).toBeEmptyDOMElement();
	} );

	test( 'renders nothing when only some items are not clearance', () => {
		// Arrange.
		const cart = {
			items: [
				{
					id: 1,
					name: 'Product 1',
					extensions: { 'wc-clearance': { is_clearance: false } },
				},
				{
					id: 2,
					name: 'Product 2',
					extensions: { 'wc-clearance': { is_clearance: false } },
				},
			],
		};

		// Act.
		const { container } = render(
			<ClearanceBadgeForOrder cart={ cart } />
		);

		// Assert.
		expect( container ).toBeEmptyDOMElement();
	} );

	test( 'renders badge when at least one item is clearance', () => {
		// Arrange.
		const cart = {
			items: [
				{
					id: 1,
					name: 'Product 1',
					extensions: { 'wc-clearance': { is_clearance: false } },
				},
				{
					id: 2,
					name: 'Product 2',
					extensions: { 'wc-clearance': { is_clearance: true } },
				},
			],
		};

		// Act.
		render( <ClearanceBadgeForOrder cart={ cart } /> );

		// Assert.
		expect( screen.getByText( 'Clearance' ) ).toBeInTheDocument();
	} );

	test( 'renders nothing with default empty cart', () => {
		// Act.
		const { container } = render( <ClearanceBadgeForOrder /> );

		// Assert.
		expect( container ).toBeEmptyDOMElement();
	} );
} );

describe( 'OrderMetaFill', () => {
	test( 'renders null when wc global is not defined', () => {
		// Arrange.
		( global as Record< string, unknown > ).wc = undefined;

		// Act.
		const { container } = render( <OrderMetaFill /> );

		// Assert.
		expect( container ).toBeEmptyDOMElement();
	} );

	test( 'renders null when wc.blocksCheckout is not defined', () => {
		// Arrange.
		( global as Record< string, unknown > ).wc = {};

		// Act.
		const { container } = render( <OrderMetaFill /> );

		// Assert.
		expect( container ).toBeEmptyDOMElement();
	} );

	test( 'renders ExperimentalOrderMeta when wc.blocksCheckout is available', () => {
		// Arrange.
		( global as Record< string, unknown > ).wc = {
			blocksCheckout: {
				ExperimentalOrderMeta: ( {
					children,
				}: {
					children: ReactNode;
				} ) => <div data-testid="order-meta-fill">{ children }</div>,
			},
		};

		// Act.
		render( <OrderMetaFill /> );

		// Assert.
		expect( screen.getByTestId( 'order-meta-fill' ) ).toBeInTheDocument();
	} );
} );
