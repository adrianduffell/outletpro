import { unregisterBlockType } from '@wordpress/blocks';
import { select } from '@wordpress/data';

jest.mock( '@wordpress/blocks', () => ( {
	unregisterBlockType: jest.fn(),
} ) );

jest.mock( '@wordpress/data', () => ( {
	select: jest.fn(),
} ) );

const mockUnregisterBlockType = unregisterBlockType as jest.Mock;
const mockSelect = select as jest.Mock;

describe( 'restrict-post-page-inserter', () => {
	beforeEach( () => {
		mockUnregisterBlockType.mockClear();
		mockSelect.mockReset();
		window.wp = {
			domReady: ( callback ) => callback(),
		};
	} );

	test.each( [ 'post', 'page' ] )(
		'unregisters clearance blocks for %s post type',
		( postType ) => {
			// Arrange.
			mockSelect.mockReturnValue( {
				getCurrentPostType: () => postType,
			} );

			// Act.
			jest.isolateModules( () => {
				require( '../restrict-post-page-inserter' );
			} );

			// Assert.
			expect( mockUnregisterBlockType ).toHaveBeenNthCalledWith(
				1,
				'wc-clearance/clearance-badge'
			);
			expect( mockUnregisterBlockType ).toHaveBeenNthCalledWith(
				2,
				'wc-clearance/clearance-message'
			);
		}
	);

	test( 'does not unregister clearance blocks for non-post/page editors', () => {
		// Arrange.
		mockSelect.mockReturnValue( {
			getCurrentPostType: () => 'wp_template',
		} );

		// Act.
		jest.isolateModules( () => {
			require( '../restrict-post-page-inserter' );
		} );

		// Assert.
		expect( mockUnregisterBlockType ).not.toHaveBeenCalled();
	} );
} );
