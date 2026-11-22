<?php
/**
 * Tests for calculate_facet_delta().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use PHPUnit\Framework\TestCase;
use function OutletPro\calculate_facet_delta;

class Test_Calculate_Facet_Delta extends TestCase {

	// phpcs:ignore Generic.Commenting.DocComment.MissingShort -- PHPUnit data provider metadata.
	/**
	 * @dataProvider facet_deltas
	 * @param int[] $old_values Old facet values
	 * @param int[] $new_values New facet values
	 * @param array{add: int[], update: array<int, int>, delete: int[]} $expected Expected facet changes.
	 */
	public function test_calculates_facet_delta_without_changing_inputs( array $old_values, array $new_values, array $expected ): void {
		// Arrange.
		$original_old = $old_values;
		$original_new = $new_values;

		// Act.
		$delta = calculate_facet_delta( $old_values, $new_values );

		// Assert.
		$this->assertSame( $expected, $delta );
		$this->assertSame( $original_old, $old_values );
		$this->assertSame( $original_new, $new_values );
	}

	/**
	 * Facet changes discussed for the settings editor.
	 *
	 * @return array<string, array{0: int[], 1: int[], 2: array{add: int[], update: array<int, int>, delete: int[]}}>
	 */
	public static function facet_deltas(): array {
		return array(
			'rename the middle facet'                     => array(
				array( 30, 50, 70 ),
				array( 30, 60, 70 ),
				array(
					'add'    => array(),
					'update' => array( 50 => 60 ),
					'delete' => array(),
				),
			),
			'delete the middle facet without renaming the last facet' => array(
				array( 30, 50, 70 ),
				array( 30, 70 ),
				array(
					'add'    => array(),
					'update' => array(),
					'delete' => array( 50 ),
				),
			),
			'keep shifted facets and replace the first with a new last facet' => array(
				array( 30, 50, 70 ),
				array( 50, 70, 90 ),
				array(
					'add'    => array( 90 ),
					'update' => array(),
					'delete' => array( 30 ),
				),
			),
			'rename two facets and delete the last facet' => array(
				array( 30, 50, 70 ),
				array( 10, 20 ),
				array(
					'add'    => array(),
					'update' => array(
						30 => 10,
						50 => 20,
					),
					'delete' => array( 70 ),
				),
			),
			'insert a middle facet without renaming the last facet' => array(
				array( 30, 70 ),
				array( 30, 50, 70 ),
				array(
					'add'    => array( 50 ),
					'update' => array(),
					'delete' => array(),
				),
			),
			'rename two facets and add a last facet'      => array(
				array( 30, 50 ),
				array( 10, 20, 70 ),
				array(
					'add'    => array( 70 ),
					'update' => array(
						30 => 10,
						50 => 20,
					),
					'delete' => array(),
				),
			),
			'unchanged facets'                            => array(
				array( 30, 50, 70 ),
				array( 30, 50, 70 ),
				array(
					'add'    => array(),
					'update' => array(),
					'delete' => array(),
				),
			),
			'reordered facets retain their identities'    => array(
				array( 30, 50, 70 ),
				array( 70, 50, 30 ),
				array(
					'add'    => array(),
					'update' => array(),
					'delete' => array(),
				),
			),
			'delete all facets'                           => array(
				array( 30, 50, 70 ),
				array(),
				array(
					'add'    => array(),
					'update' => array(),
					'delete' => array( 30, 50, 70 ),
				),
			),
			'add all facets'                              => array(
				array(),
				array( 30, 50, 70 ),
				array(
					'add'    => array( 30, 50, 70 ),
					'update' => array(),
					'delete' => array(),
				),
			),
			'empty lists'                                 => array(
				array(),
				array(),
				array(
					'add'    => array(),
					'update' => array(),
					'delete' => array(),
				),
			),
			'keep the zero facet'                         => array(
				array( 0, 50 ),
				array( 0 ),
				array(
					'add'    => array(),
					'update' => array(),
					'delete' => array( 50 ),
				),
			),
		);
	}
}
