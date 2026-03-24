<?php
/**
 * Page editor notice functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Returns the data to pass to the block editor for the page editor notice.
 *
 * Checks whether the post currently being edited is the clearance page and,
 * if so, whether the clearance section has any products. The result is used
 * by the editor JavaScript to show a warning notice when no products exist.
 *
 * @internal
 * @return array{noProductsNotice: bool}
 */
function get_page_editor_notice_data(): array {
	$data = array(
		'noProductsNotice' => false,
	);

	try {
		$clearance_page_id = get_clearance_page_id();
	} catch ( \UnexpectedValueException $e ) {
		return $data;
	}

	if ( null === $clearance_page_id || get_the_ID() !== $clearance_page_id ) {
		return $data;
	}

	try {
		if ( clearance_section_empty() ) {
			$data['noProductsNotice'] = true;
		}
	} catch ( \RuntimeException $e ) {
		return $data;
	}

	return $data;
}
