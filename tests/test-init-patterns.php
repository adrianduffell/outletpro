<?php
/**
 * Test the init_patterns function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_blocks;
use function OutletPro\deinit_patterns;
use function OutletPro\get_pattern_content;
use function OutletPro\init_blocks;
use function OutletPro\init_patterns;
use function OutletPro\init_taxonomies;
use const OutletPro\OUTLET_PAGE_OPTION;
class Test_Init_Patterns extends WP_UnitTestCase {

	public function test_navigation_patterns_preserve_menu_styles(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Verify menu styles across all four navigation patterns.
		// Arrange.
		switch_theme( 'storefront' );
		deinit_blocks();
		init_blocks();
		init_taxonomies();
		deinit_patterns();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$pattern_names = array(
			'outletpro/outlet-navigation-3',
			'outletpro/outlet-navigation-4',
			'outletpro/outlet-navigation-6',
			'outletpro/outlet-navigation-price-tiers',
		);

		// Act.
		init_patterns();
		$contents = array_map( static fn( string $pattern_name ): string => get_pattern_content( $pattern_name ), $pattern_names );

		// Assert.
		foreach ( $contents as $content ) {
			$group  = parse_blocks( $content )[0];
			$menu   = $group['innerBlocks'][1];
			$output = render_block( $menu );
			$this->assertSame( 'outletpro/outlet-menu', $menu['blockName'] );
			$this->assertSame(
				array(
					'spacing'    => array(
						'blockGap' => '1em',
						'margin'   => array(
							'top'    => '2.5rem',
							'bottom' => '2.5rem',
						),
						'padding'  => array(
							'top'    => '0',
							'right'  => '0',
							'bottom' => '0',
							'left'   => '0',
						),
					),
					'typography' => array( 'textDecoration' => 'none' ),
				),
				$menu['attrs']['style']
			);
			$this->assertArrayNotHasKey( 'margin', $group['attrs']['style']['spacing'] ?? array() );
			$this->assertStringNotContainsString( 'margin-top:', $group['innerHTML'] );
			$this->assertStringNotContainsString( 'margin-bottom:', $group['innerHTML'] );
			$this->assertStringContainsString( 'margin-top:2.5rem', $output );
			$this->assertStringContainsString( 'margin-bottom:2.5rem', $output );
			$this->assertStringContainsString( 'padding-top:0', $output );
			$this->assertStringContainsString( 'padding-right:0', $output );
			$this->assertStringContainsString( 'padding-bottom:0', $output );
			$this->assertStringContainsString( 'padding-left:0', $output );
			$this->assertStringContainsString( 'text-decoration:none', $output );
		}
	}

	public function test_registers_outlet_block_pattern_category(): void {
		// Arrange.
		deinit_patterns();

		// Act.
		init_patterns();

		// Assert.
		$categories = \WP_Block_Pattern_Categories_Registry::get_instance()->get_all_registered();
		$this->assertContainsEquals(
			array(
				'name'  => 'outletpro',
				'label' => 'Outlet',
			),
			$categories,
		);
	}

	public function test_registers_navigation_patterns_without_filter_tiles(): void {
		// Arrange.
		deinit_patterns();

		// Act.
		init_patterns();

		// Assert.
		$patterns = \WP_Block_Patterns_Registry::get_instance();
		$this->assertFalse( $patterns->is_registered( 'outletpro/outlet-filter-tiles' ) );
		$this->assertTrue( $patterns->is_registered( 'outletpro/outlet-navigation-3' ) );
		$this->assertTrue( $patterns->is_registered( 'outletpro/outlet-navigation-4' ) );
		$this->assertTrue( $patterns->is_registered( 'outletpro/outlet-navigation-6' ) );
		$this->assertTrue( $patterns->is_registered( 'outletpro/outlet-navigation-price-tiers' ) );
	}

	public function test_registers_outlet_sort_filter_pattern_only_for_wp_7_plus(): void {
		// Arrange.
		! \WP_Block_Patterns_Registry::get_instance()->is_registered( 'outletpro/outlet-sort-filter' )
			|| \WP_Block_Patterns_Registry::get_instance()->unregister( 'outletpro/outlet-sort-filter' );

		version_compare( get_bloginfo( 'version' ), '7.1', '<' )
			|| $this->fail( 'With WP 7.1 released, remove the version gate in init_patterns().' );

		// Act.
		init_patterns();

		// Assert.
		$this->assertSame(
			version_compare( get_bloginfo( 'version' ), '7.0', '>=' ),
			\WP_Block_Patterns_Registry::get_instance()->is_registered( 'outletpro/outlet-sort-filter' )
		);
	}
}
