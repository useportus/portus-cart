<?php
/**
 * Plugin uninstall cleanup.
 *
 * @package PortusCart
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

if ( ! function_exists( 'meu_side_cart_should_delete_settings_on_uninstall' ) ) {
	/**
	 * Checks whether plugin settings should be removed during uninstall.
	 *
	 * @return bool
	 */
	function meu_side_cart_should_delete_settings_on_uninstall() {
		$meu_side_cart_settings = get_option( 'meu_side_cart_settings', array() );

		return is_array( $meu_side_cart_settings )
			&& isset( $meu_side_cart_settings['delete_settings_on_uninstall'] )
			&& 'yes' === $meu_side_cart_settings['delete_settings_on_uninstall'];
	}
}

if ( meu_side_cart_should_delete_settings_on_uninstall() ) {
	delete_option( 'meu_side_cart_settings' );
}
