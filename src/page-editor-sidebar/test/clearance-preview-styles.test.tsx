import { render } from '@testing-library/react';
import { useEntityProp } from '@wordpress/core-data';
import ClearancePreviewStyles from '../clearance-preview-styles';

jest.mock( '@wordpress/core-data', () => ( {
	useEntityProp: jest.fn(),
} ) );

jest.mock( '@wordpress/element', () => ( {
	...jest.requireActual( '@wordpress/element' ),
	useEffect: jest.fn(),
} ) );

const mockUseEntityProp = useEntityProp as jest.Mock;

function setupEntityPropMock(
	overrides: Record< string, [ string | undefined, jest.Mock ] > = {}
) {
	mockUseEntityProp.mockImplementation(
		( _kind: string, _name: string, key: string ) => {
			if ( overrides[ key ] ) {
				return [ ...overrides[ key ], undefined ];
			}
			return [ undefined, jest.fn(), undefined ];
		}
	);
}

describe( 'ClearancePreviewStyles', () => {
	test( 'renders nothing (returns null)', () => {
		// Arrange.
		setupEntityPropMock();

		// Act.
		const { container } = render( <ClearancePreviewStyles /> );

		// Assert.
		expect( container ).toBeEmptyDOMElement();
	} );

	test( 'preview effect injects CSS vars into the document when called', () => {
		// Arrange.
		const mockUseEffect = jest.mocked(
			(
				jest.requireMock( '@wordpress/element' ) as {
					useEffect: jest.Mock;
				}
			 ).useEffect
		);

		let capturedEffect: ( () => void ) | undefined;
		mockUseEffect.mockImplementationOnce( ( fn: () => void ) => {
			capturedEffect = fn;
		} );

		setupEntityPropMock( {
			wc_clearance_badge_label: [ 'Sale', jest.fn() ],
			wc_clearance_badge_bg_color: [ '#ff0000', jest.fn() ],
			wc_clearance_badge_text_color: [ '#ffffff', jest.fn() ],
		} );

		// Act.
		render( <ClearancePreviewStyles /> );
		capturedEffect?.();

		// Assert.
		const styleEl = document.getElementById( 'wc-clearance-preview-vars' );
		expect( styleEl ).not.toBeNull();
		expect( styleEl?.textContent ).toContain(
			'--wc-clearance-badge-label: "Sale"'
		);
		expect( styleEl?.textContent ).toContain(
			'--wc-clearance-badge-bg-color: #ff0000'
		);
		expect( styleEl?.textContent ).toContain(
			'--wc-clearance-badge-text-color: #ffffff'
		);

		// Cleanup.
		styleEl?.remove();
	} );
} );
