<?php
/**
 * License functions.
 *
 * @package OutletPro
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * WordPress option key used to store the license key.
 *
 * @internal
 */
const LICENSE_KEY_OPTION = 'outletpro_license_key';

/**
 * WordPress transient key used to cache license validity.
 *
 * @internal
 */
const HAS_LICENSE_TRANSIENT = 'outletpro_has_license';

/**
 * Validate a license key.
 *
 * @internal
 *
 * @param mixed $license_key The license key to validate.
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
 */
function validate_license( $license_key ): bool {
	return is_string( $license_key ) && strlen( $license_key ) > 1;
}

/**
 * Check whether the current site has a valid license.
 *
 * @internal
 */
function has_license(): bool {
	$cached_value = get_transient( HAS_LICENSE_TRANSIENT );

	if ( false !== $cached_value ) {
		return 1 === $cached_value;
	}

	$has_license = validate_license( get_option( LICENSE_KEY_OPTION ) );

	set_transient( HAS_LICENSE_TRANSIENT, $has_license ? 1 : 0, WEEK_IN_SECONDS );

	return $has_license;
}
