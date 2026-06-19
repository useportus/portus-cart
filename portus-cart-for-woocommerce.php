<?php
/**
 * Plugin Name: Portus Cart for WooCommerce
 * Plugin URI: https://useportus.com/cart
 * Description: Carrinho lateral AJAX para WooCommerce com botao flutuante, cupom, controles de quantidade, remocao de itens e ajustes de compatibilidade.
 * Version: 3.5.1
 * Author: Portus
 * Author URI: https://useportus.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: portus-cart-for-woocommerce
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * WC requires at least: 7.0
 *
 * @package PortusCart
 */

defined( 'ABSPATH' ) || exit;

define( 'MEU_SIDE_CART_VERSION', '3.5.1' );
define( 'MEU_SIDE_CART_FILE', __FILE__ );
define( 'MEU_SIDE_CART_PATH', plugin_dir_path( __FILE__ ) );
define( 'MEU_SIDE_CART_URL', plugin_dir_url( __FILE__ ) );
define( 'MEU_SIDE_CART_BASENAME', plugin_basename( __FILE__ ) );

require_once MEU_SIDE_CART_PATH . 'plugin-v3.php';
