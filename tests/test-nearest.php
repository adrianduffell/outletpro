<?php
/**
 * Tests for nearest().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use PHPUnit\Framework\TestCase;
use function OutletPro\nearest;

require_once dirname( __DIR__ ) . '/includes/vanilla.php';

class Test_Nearest extends TestCase {

	public function test_rounds_down(): void {
		// Act.
		$value = nearest( 32.5, 20, 30, 40 );

		// Assert.
		$this->assertSame( 30, $value );
	}

	public function test_rounds_up(): void {
		// Act.
		$value = nearest( 37, 20, 30, 40 );

		// Assert.
		$this->assertSame( 40, $value );
	}

	public function test_rounds_ties_up(): void {
		// Act.
		$value = nearest( 17.5, 20, 15 );

		// Assert.
		$this->assertSame( 20, $value );
	}

	public function test_rounds_large_values(): void {
		// Act.
		$value = nearest( 800, 500, 1000, 2000 );

		// Assert.
		$this->assertSame( 1000, $value );
	}

	public function test_preserves_exact_matches(): void {
		// Act.
		$value = nearest( 30, 20, 30, 40 );

		// Assert.
		$this->assertSame( 30, $value );
	}

	public function test_uses_the_lowest_value_below_the_range(): void {
		// Act.
		$value = nearest( 1, 10, 20, 30 );

		// Assert.
		$this->assertSame( 10, $value );
	}

	public function test_uses_the_highest_value_above_the_range(): void {
		// Act.
		$value = nearest( 100, 10, 20, 30 );

		// Assert.
		$this->assertSame( 30, $value );
	}

	public function test_rejects_empty_candidates(): void {
		// Expect.
		$this->expectException( InvalidArgumentException::class );

		// Act.
		nearest( 10 );
	}
}
