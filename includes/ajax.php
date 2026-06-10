<?php
/**
 * AJAX handlers.
 *
 * @package PortusCart
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Meu_Side_Cart_Ajax' ) ) {
	/**
	 * Handles secure AJAX cart actions.
	 */
	class Meu_Side_Cart_Ajax {
		/**
		 * Registers AJAX actions for visitors and logged-in users.
		 */
		public static function init() {
			$actions = array(
				'refresh'         => 'refresh',
				'remove_item'     => 'remove_item',
				'update_quantity' => 'update_quantity',
				'apply_coupon'    => 'apply_coupon',
				'remove_coupon'   => 'remove_coupon',
			);

			foreach ( $actions as $action => $method ) {
				add_action( 'wp_ajax_meu_side_cart_' . $action, array( __CLASS__, $method ) );
				add_action( 'wp_ajax_nopriv_meu_side_cart_' . $action, array( __CLASS__, $method ) );
			}
		}

		/**
		 * Refreshes the side cart.
		 */
		public static function refresh() {
			self::verify_request();
			self::send_success();
		}


		/**
		 * Removes a cart item.
		 */
		public static function remove_item() {
			self::verify_request();

			$cart_key = self::post_text( 'cart_key' );

			if ( '' === $cart_key || ! WC()->cart->get_cart_item( $cart_key ) ) {
				self::send_error( __( 'Item não encontrado no carrinho.', 'portus-cart-for-woocommerce' ) );
			}

			WC()->cart->remove_cart_item( $cart_key );
			wc_add_notice( __( 'Produto removido do carrinho.', 'portus-cart-for-woocommerce' ), 'success' );

			self::send_success();
		}

		/**
		 * Updates item quantity.
		 */
		public static function update_quantity() {
			self::verify_request();

			$cart_key = self::post_text( 'cart_key' );
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is verified at the start of this AJAX handler.
			$quantity = isset( $_POST['quantity'] ) ? wc_stock_amount( sanitize_text_field( wp_unslash( $_POST['quantity'] ) ) ) : 0;
			$quantity = max( 0, $quantity );

			if ( '' === $cart_key || ! WC()->cart->get_cart_item( $cart_key ) ) {
				self::send_error( __( 'Item não encontrado no carrinho.', 'portus-cart-for-woocommerce' ) );
			}

			$cart_item = WC()->cart->get_cart_item( $cart_key );
			$product   = isset( $cart_item['data'] ) ? $cart_item['data'] : null;

			if ( $product ) {
				$max_quantity = Meu_Side_Cart_Cart::get_max_purchase_quantity( $product );

				if ( $max_quantity >= 0 && $quantity > $max_quantity ) {
					$quantity = $max_quantity;
					wc_add_notice(
						sprintf(
							/* translators: %d: maximum purchase quantity. */
							__( 'Quantidade ajustada ao limite disponível: %d.', 'portus-cart-for-woocommerce' ),
							$max_quantity
						),
						'notice'
					);
				}
			}

			WC()->cart->set_quantity( $cart_key, $quantity, true );
			wc_add_notice( __( 'Carrinho atualizado.', 'portus-cart-for-woocommerce' ), 'success' );

			self::send_success();
		}

		/**
		 * Applies a coupon code.
		 */
		public static function apply_coupon() {
			self::verify_request();

			$coupon_code = wc_format_coupon_code( self::post_text( 'coupon' ) );

			if ( '' === $coupon_code ) {
				self::send_error( __( 'Informe um cupom válido.', 'portus-cart-for-woocommerce' ) );
			}

			if ( WC()->cart->has_discount( $coupon_code ) ) {
				wc_add_notice( __( 'Este cupom já está aplicado.', 'portus-cart-for-woocommerce' ), 'notice' );
				self::send_success();
			}

			WC()->cart->apply_coupon( $coupon_code );
			self::send_success();
		}

		/**
		 * Removes a coupon code.
		 */
		public static function remove_coupon() {
			self::verify_request();

			$coupon_code = wc_format_coupon_code( self::post_text( 'coupon' ) );

			if ( '' === $coupon_code || ! WC()->cart->has_discount( $coupon_code ) ) {
				self::send_error( __( 'Cupom não encontrado.', 'portus-cart-for-woocommerce' ) );
			}

			WC()->cart->remove_coupon( $coupon_code );
			wc_add_notice( __( 'Cupom removido.', 'portus-cart-for-woocommerce' ), 'success' );

			self::send_success();
		}




		/**
		 * Verifies nonce and cart availability.
		 */
		private static function verify_request() {
			check_ajax_referer( 'meu_side_cart_nonce', 'nonce' );

			if ( ! function_exists( 'WC' ) ) {
				self::send_error( __( 'WooCommerce indisponível.', 'portus-cart-for-woocommerce' ) );
			}

			if ( null === WC()->cart && function_exists( 'wc_load_cart' ) ) {
				wc_load_cart();
			}

			if ( ! WC()->cart ) {
				self::send_error( __( 'Carrinho indisponível.', 'portus-cart-for-woocommerce' ) );
			}
		}

		/**
		 * Reads a sanitized text value from POST.
		 *
		 * @param string $key POST key.
		 * @return string
		 */
		private static function post_text( $key ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is verified before AJAX helpers read request fields.
			return isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
		}

		/**
		 * Reads and validates posted variation attributes for a parent product.
		 *
		 * @param WC_Product_Variable $parent_product Parent product.
		 * @return array
		 */
		private static function get_posted_variation_attributes( $parent_product ) {
			$attributes = array();

			foreach ( $parent_product->get_variation_attributes() as $attribute_name => $options ) {
				$field_name = function_exists( 'wc_variation_attribute_name' ) ? wc_variation_attribute_name( $attribute_name ) : 'attribute_' . sanitize_title( $attribute_name );
				$value      = self::post_text( $field_name );

				if ( '' === $value ) {
					return array();
				}

				$attributes[ $field_name ] = $value;
			}

			return $attributes;
		}

		/**
		 * Preserves custom cart item data while replacing a variation.
		 *
		 * @param array $cart_item Cart item data.
		 * @return array
		 */
		private static function get_preserved_cart_item_data( $cart_item ) {
			$excluded = array(
				'data',
				'key',
				'product_id',
				'variation_id',
				'variation',
				'quantity',
				'line_total',
				'line_subtotal',
				'line_tax',
				'line_subtotal_tax',
				'line_tax_data',
			);

			foreach ( $excluded as $key ) {
				unset( $cart_item[ $key ] );
			}

			return $cart_item;
		}

		/**
		 * Counts cart quantity for the same stock-managed product.
		 *
		 * @param WC_Product $product Product object.
		 * @param string     $excluded_cart_key Cart item key to ignore while replacing a variation.
		 * @return int
		 */
		private static function get_cart_quantity_for_product( $product, $excluded_cart_key = '' ) {
			if ( ! WC()->cart || ! $product ) {
				return 0;
			}

			$target_id = method_exists( $product, 'get_stock_managed_by_id' ) ? $product->get_stock_managed_by_id() : $product->get_id();
			$quantity  = 0;

			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				if ( '' !== $excluded_cart_key && $cart_item_key === $excluded_cart_key ) {
					continue;
				}

				$item_product = isset( $cart_item['data'] ) ? $cart_item['data'] : null;

				if ( ! $item_product ) {
					continue;
				}

				$item_id = method_exists( $item_product, 'get_stock_managed_by_id' ) ? $item_product->get_stock_managed_by_id() : $item_product->get_id();

				if ( (int) $item_id === (int) $target_id ) {
					$quantity += isset( $cart_item['quantity'] ) ? absint( $cart_item['quantity'] ) : 0;
				}
			}

			return $quantity;
		}

		/**
		 * Sends a success response with fresh cart HTML.
		 */
		private static function send_success( $prefer_free_shipping = true ) {

			WC()->cart->calculate_totals();

			wp_send_json_success( Meu_Side_Cart_Cart::get_payload( self::get_notices() ) );
		}

		/**
		 * Sends an error response with notices.
		 *
		 * @param string $message Error message.
		 */
		private static function send_error( $message ) {
			if ( function_exists( 'wc_add_notice' ) ) {
				wc_add_notice( $message, 'error' );
			}

			$data = array(
				'message' => esc_html( $message ),
			);

			if ( function_exists( 'WC' ) && WC()->cart ) {
				WC()->cart->calculate_totals();
				$data = array_merge( $data, Meu_Side_Cart_Cart::get_payload( self::get_notices() ) );
			}

			wp_send_json_error( $data, 400 );
		}

		/**
		 * Captures WooCommerce notices as HTML.
		 *
		 * @return string
		 */
		private static function get_notices() {
			if ( ! function_exists( 'wc_print_notices' ) ) {
				return '';
			}

			ob_start();
			wc_print_notices();

			return ob_get_clean();
		}
	}
}
