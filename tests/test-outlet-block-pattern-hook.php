<?php
/**
 * Tests for outlet_block_pattern_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_blocks;
use function OutletPro\deinit_patterns;
use function OutletPro\init_blocks;
use function OutletPro\init_patterns;

class Test_Outlet_Block_Pattern_Hook extends WP_UnitTestCase {

	public function test_outlet_badge_row_pattern_is_inserted_by_default(): void {
		// Arrange.
		deinit_blocks();
		deinit_patterns();
		init_patterns();
		init_blocks();
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';
		$content        = '<!-- wp:post-title /-->';

		// Act.
		$result = apply_block_hooks_to_content( $content, $template );
		$blocks = parse_blocks( $result );

		// Assert.
		$this->assertSame( 'outletpro/outlet-group', $blocks[0]['blockName'] );
		$this->assertSame(
			array(
				'type'           => 'flex',
				'flexWrap'       => 'nowrap',
				'justifyContent' => 'left',
			),
			$blocks[0]['attrs']['layout']
		);
		$this->assertSame( 'outlet-badge-row', $blocks[0]['attrs']['className'] );
		$this->assertSame( 'outletpro/outlet-badge', $blocks[0]['innerBlocks'][0]['blockName'] );
		$this->assertSame( 'core/post-title', $blocks[1]['blockName'] );

		$expected_pattern_name = version_compare( get_bloginfo( 'version' ), '7.0', '>=' )
			? 'outletpro/outlet-badge-row'
			: null;
		$this->assertSame( $expected_pattern_name, $blocks[0]['attrs']['metadata']['patternName'] ?? null );
	}

	public function test_hooked_badge_outside_single_product_template_is_not_replaced_with_pattern(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$badge         = array(
			'blockName'    => 'outletpro/outlet-badge',
			'attrs'        => array(),
			'innerBlocks'  => array(),
			'innerHTML'    => '',
			'innerContent' => array(),
		);
		$anchor        = array( 'blockName' => 'core/post-title' );
		$context       = new WP_Block_Template();
		$context->slug = 'single';

		// Act.
		$result = apply_filters(
			'hooked_block_outletpro/outlet-badge', // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Dynamic hook includes the block namespace.
			$badge,
			'outletpro/outlet-badge',
			'before',
			$anchor,
			$context
		);

		// Assert.
		$this->assertSame( $badge, $result );
	}
}
