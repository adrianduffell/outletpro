<?php
/**
 * Tests for get_pattern_content().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_pattern_content;

class Test_Get_Pattern_Content extends WP_UnitTestCase {

	public function test_throws_when_pattern_name_is_empty(): void {
		// Expect.
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Pattern name cannot be empty.' );

		// Act.
		get_pattern_content( '' );
	}

	public function test_throws_when_pattern_name_contains_unsupported_characters(): void {
		// Arrange.
		$pattern_name = 'outletpro/invalid_pattern';

		// Expect.
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Pattern name contains unsupported characters.' );

		// Act.
		get_pattern_content( $pattern_name );
	}

	public function test_throws_when_pattern_is_not_registered(): void {
		// Arrange.
		$pattern_name = 'outletpro/not-registered';

		// Expect.
		$this->expectException( \InvalidArgumentException::class );

		// Act.
		get_pattern_content( $pattern_name );
	}

	public function test_returns_resolved_registered_pattern_content(): void {
		// Arrange.
		register_block_pattern(
			'outletpro/foo-pattern',
			array(
				'title'   => 'Test pattern content',
				'content' => '<!-- wp:paragraph --><p>Pattern helper test content.</p><!-- /wp:paragraph -->',
			)
		);

		// Act.
		$content = get_pattern_content( 'outletpro/foo-pattern' );

		// Assert.
		$this->assertMatchesRegularExpression( '/<!-- wp:paragraph(?: \{[^\r\n]*\})? -->/', $content );
		$this->assertStringContainsString( '<p>Pattern helper test content.</p>', $content );
		$this->assertStringNotContainsString( '"slug":"outletpro/foo-pattern"', $content );
		$this->assertStringContainsString( '"metadata"', $content );
	}
}
