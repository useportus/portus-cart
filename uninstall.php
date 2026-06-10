<?php
/**
 * Plugin uninstall cleanup.
 *
 * @package PortusCart
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$meu_side_cart_settings = get_option( 'meu_side_cart_settings', array() );

if ( is_array( $meu_side_cart_settings ) && isset( $meu_side_cart_settings['delete_settings_on_uninstall'] ) && 'yes' === $meu_side_cart_settings['delete_settings_on_uninstall'] ) {
	delete_option( 'meu_side_cart_settings' );
}
