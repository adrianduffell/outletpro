<?php
/**
 * Tests for get_pattern_content().
 *
 * @package WC_Outlet
 */

use function WC_Outlet\get_pattern_content;

class Test_Get_Pattern_Content extends WP_UnitTestCase {

	private const TEST_PATTERN = 'wc-outlet/test-pattern-content';

	public function test_throws_when_pattern_name_is_empty(): void {
		// Expect.
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Pattern name cannot be empty.' );

		// Act.
		get_pattern_content( '' );
	}

	public function test_throws_when_pattern_is_not_registered(): void {
		// Arrange.
		$pattern_name = 'wc-outlet/not-registered';

		// Expect.
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( sprintf( 'Block pattern "%s" is not registered.', $pattern_name ) );

		// Act.
		get_pattern_content( $pattern_name );
	}

	public function test_returns_resolved_registered_pattern_content(): void {
		// Arrange.
		register_block_pattern(
			self::TEST_PATTERN,
			array(
				'title'   => 'Test pattern content',
				'content' => '<!-- wp:paragraph --><p>Pattern helper test content.</p><!-- /wp:paragraph -->',
			)
		);

		// Act.
		$content = get_pattern_content( self::TEST_PATTERN );

		// Assert.
		$this->assertStringContainsString( '<!-- wp:paragraph -->', $content );
		$this->assertStringContainsString( 'Pattern helper test content.', $content );
		$this->assertStringNotContainsString( '"slug":"' . self::TEST_PATTERN . '"', $content );
		version_compare( get_bloginfo( 'version' ), '7.0', '>=' )
			&& $this->assertStringContainsString( '"metadata"', $content );

		// Cleanup.
		unregister_block_pattern( self::TEST_PATTERN );
	}
}
