<?php
/**
 * Membership value validation tests.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\validate_outletpro_product_field;
class Test_Validate_Outletpro_Product_Field extends WP_UnitTestCase {
	public function test_accepts_only_an_object_with_an_optional_boolean(): void {
		// Arrange.
		$valid   = array( array(), array( 'is_outlet' => true ), array( 'is_outlet' => false ) );
		$invalid = array( null, true, 'false', 0, array( true ), array( 'is_outlet' => null ), array( 'is_outlet' => 'false' ), array( 'is_outlet' => 0 ), array( 'is_outlet' => 1 ) );
		// Act.
		$accepted = array_map( 'OutletPro\validate_outletpro_product_field', $valid );
		$rejected = array_map( 'OutletPro\validate_outletpro_product_field', $invalid );
		// Assert.
		$this->assertFalse( validate_outletpro_product_field( 'true' ) );
		$this->assertSame( array_fill( 0, count( $valid ), true ), $accepted );
		$this->assertSame( array_fill( 0, count( $invalid ), false ), $rejected );
	}
}
