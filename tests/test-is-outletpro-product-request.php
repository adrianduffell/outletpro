<?php
/**
 * Route scope tests.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\is_outletpro_product_request;

class Test_Is_Outletpro_Product_Request extends WP_UnitTestCase {
	public function test_limits_membership_to_v3_product_reads_and_writes(): void {
		// Arrange.
		$cases = array(
			'/wc/v3/products'                    => true,
			'/wc/v3/products/12'                 => true,
			'/wc/v3/products/batch'              => true,
			'/wc/v2/products'                    => false,
			'/wp/v2/product'                     => false,
			'/wc/v3/products/12/variations'      => false,
			'/wc/v3/products/suggested-products' => false,
		);
		// Act.
		$actual = array_map( static fn( string $route ): bool => is_outletpro_product_request( new WP_REST_Request( 'POST', $route ) ), array_keys( $cases ) );
		// Assert.
		$this->assertSame( array_values( $cases ), $actual );
		$this->assertFalse( is_outletpro_product_request( new WP_REST_Request( 'DELETE', '/wc/v3/products/12' ) ) );
	}
}
