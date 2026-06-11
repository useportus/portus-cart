<?php
/**
 * Cart rendering.
 *
 * @package PortusCart
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Meu_Side_Cart_Cart' ) ) {
	/**
	 * Renders side cart markup and fragments.
	 */
	class Meu_Side_Cart_Cart {
		/**
		 * Registers WooCommerce fragment support.
		 */
		public static function init() {
			add_filter( 'woocommerce_add_to_cart_fragments', array( __CLASS__, 'add_fragments' ) );
		}

		/**
		 * Adds fragments used by WooCommerce AJAX.
		 *
		 * @param array $fragments WooCommerce fragments.
		 * @return array
		 */
		public static function add_fragments( $fragments ) {
			$fragments['span.msc-floating-count'] = self::get_count_badge_html();
			$fragments['div.msc-panel-content']  = '<div class="msc-panel-content" data-msc-content>' . self::get_panel_content_html() . '</div>';

			return $fragments;
		}

		/**
		 * Renders the fixed root, floating trigger and side panel.
		 */
		public static function render_shell() {
			$count                = self::get_count();
			$css_variables        = class_exists( 'Meu_Side_Cart_Settings' ) ? Meu_Side_Cart_Settings::get_css_variables() : '';
			$show_floating_button = class_exists( 'Meu_Side_Cart_Settings' ) ? Meu_Side_Cart_Settings::is_enabled( 'enabled_floating_button' ) : true;
			$hide_on_product      = class_exists( 'Meu_Side_Cart_Settings' ) ? Meu_Side_Cart_Settings::is_enabled( 'hide_floating_on_product' ) : true;
			$hide_floating_button = self::is_floating_button_hidden_context( $hide_on_product );
			$floating_offset      = class_exists( 'Meu_Side_Cart_Settings' ) ? Meu_Side_Cart_Settings::get_int( 'floating_bottom_offset', 70, 240 ) : 116;
			$floating_side        = class_exists( 'Meu_Side_Cart_Settings' ) ? Meu_Side_Cart_Settings::get( 'floating_side' ) : 'right';
			$floating_side        = in_array( $floating_side, array( 'left', 'right' ), true ) ? $floating_side : 'right';
			$button_style         = $css_variables . '--msc-floating-offset:' . $floating_offset . 'px;';
			?>
			<div class="msc-side-cart" id="meu-side-cart" data-msc-root data-count="<?php echo esc_attr( $count ); ?>" style="<?php echo esc_attr( $css_variables ); ?>">
				<?php if ( $show_floating_button && ! $hide_floating_button ) : ?>
					<button class="msc-floating-button msc-floating-<?php echo esc_attr( $floating_side ); ?>" type="button" data-msc-open data-msc-floating-offset="<?php echo esc_attr( $floating_offset ); ?>" style="<?php echo esc_attr( $button_style ); ?>" aria-expanded="false" aria-controls="msc-side-panel" aria-label="<?php esc_attr_e( 'Abrir carrinho', 'portus-cart-for-woocommerce' ); ?>">
						<span class="msc-floating-icon" aria-hidden="true"><?php self::render_bag_icon(); ?></span>
						<?php echo wp_kses_post( self::get_count_badge_html() ); ?>
					</button>
				<?php endif; ?>

				<div class="msc-overlay" data-msc-close hidden></div>

				<aside class="msc-panel" id="msc-side-panel" role="dialog" aria-modal="true" aria-labelledby="msc-title" aria-hidden="true" tabindex="-1">
					<div class="msc-panel-content" data-msc-content>
						<?php self::render_panel_content(); ?>
					</div>
				</aside>
			</div>
			<?php
		}

		/**
		 * Renders the local shopping bag icon used by the cart trigger and header.
		 */
		private static function render_bag_icon() {
			?>
			<span class="msc-bag-icon" aria-hidden="true"></span>
			<?php
		}

		/**
		 * Checks whether the floating cart button should be hidden on the current page.
		 *
		 * @param bool $hide_on_product Whether product pages should hide the floating trigger.
		 * @return bool
		 */
		private static function is_floating_button_hidden_context( $hide_on_product ) {
			if ( $hide_on_product && function_exists( 'is_product' ) && is_product() ) {
				return true;
			}

			if ( function_exists( 'is_cart' ) && is_cart() ) {
				return true;
			}

			if ( function_exists( 'is_checkout' ) && is_checkout() ) {
				return true;
			}

			if ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) {
				return true;
			}

			if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-received' ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Returns the current cart payload for AJAX responses.
		 *
		 * @param string $notices Notices HTML.
		 * @return array
		 */
		public static function get_payload( $notices = '' ) {
			return array(
				'count'    => self::get_count(),
				'html'     => self::get_panel_content_html(),
				'notices'  => $notices,
				'isEmpty'  => self::is_empty(),
				'checkout' => function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : '',
			);
		}

		/**
		 * Gets the maximum purchase quantity for a product, or -1 when unlimited.
		 *
		 * @param WC_Product $product Product object.
		 * @return int
		 */
		public static function get_max_purchase_quantity( $product ) {
			if ( ! $product || ! method_exists( $product, 'get_max_purchase_quantity' ) ) {
				return -1;
			}

			$max_quantity = $product->get_max_purchase_quantity();

			if ( '' === $max_quantity || $max_quantity < 0 ) {
				return -1;
			}

			return wc_stock_amount( $max_quantity );
		}

		/**
		 * Returns a low-stock customer notice for managed-stock products.
		 *
		 * @param WC_Product $product Product object.
		 * @return string
		 */
		public static function get_low_stock_notice( $product ) {
			if ( class_exists( 'Meu_Side_Cart_Settings' ) && ! Meu_Side_Cart_Settings::is_enabled( 'show_low_stock_alerts' ) ) {
				return '';
			}

			if ( ! $product || ! $product->managing_stock() || $product->backorders_allowed() ) {
				return '';
			}

			$stock_quantity = $product->get_stock_quantity();

			if ( null === $stock_quantity ) {
				return '';
			}

			$stock_quantity = wc_stock_amount( $stock_quantity );
			$threshold      = class_exists( 'Meu_Side_Cart_Settings' ) ? Meu_Side_Cart_Settings::get_int( 'low_stock_threshold', 1, 10 ) : 2;

			if ( $stock_quantity <= 0 || $stock_quantity > $threshold ) {
				return '';
			}

			if ( 1 === $stock_quantity ) {
				return __( 'Última unidade em estoque.', 'portus-cart-for-woocommerce' );
			}

			return sprintf(
				/* translators: %d: stock quantity. */
				__( 'Estoque baixo: restam %d unidades.', 'portus-cart-for-woocommerce' ),
				$stock_quantity
			);
		}

		/**
		 * Returns the cart item count.
		 *
		 * @return int
		 */
		public static function get_count() {
			if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
				return 0;
			}

			return (int) WC()->cart->get_cart_contents_count();
		}

		/**
		 * Checks if the cart is empty.
		 *
		 * @return bool
		 */
		public static function is_empty() {
			return ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty();
		}

		/**
		 * Returns the count badge.
		 *
		 * @return string
		 */
		public static function get_count_badge_html() {
			return '<span class="msc-floating-count" data-msc-count>' . esc_html( self::get_count() ) . '</span>';
		}

		/**
		 * Captures panel HTML.
		 *
		 * @return string
		 */
		public static function get_panel_content_html() {
			ob_start();
			self::render_panel_content();

			return ob_get_clean();
		}

		/**
		 * Renders panel contents.
		 */
		public static function render_panel_content() {
			$count      = self::get_count();
			$cart_title = class_exists( 'Meu_Side_Cart_Settings' ) ? Meu_Side_Cart_Settings::get( 'cart_title' ) : __( 'Meu carrinho', 'portus-cart-for-woocommerce' );

			?>
			<header class="msc-header">
				<div class="msc-header-title">
					<span class="msc-header-cart-icon" aria-hidden="true">
						<?php self::render_bag_icon(); ?>
						<span class="msc-header-count" data-msc-count><?php echo esc_html( $count ); ?></span>
					</span>
					<div class="msc-header-copy">
						<h2 id="msc-title"><?php echo esc_html( $cart_title ); ?></h2>
						<span class="msc-header-count-label">
							<?php
							printf(
								/* translators: %d: number of items in the cart. */
								esc_html( _n( '%d item', '%d itens', $count, 'portus-cart-for-woocommerce' ) ),
								esc_html( $count )
							);
							?>
						</span>
					</div>
				</div>
				<button class="msc-close" type="button" data-msc-close aria-label="<?php esc_attr_e( 'Fechar carrinho', 'portus-cart-for-woocommerce' ); ?>">
					<i class="fa-solid fa-xmark" aria-hidden="true"></i>
				</button>
			</header>

			<div class="msc-notices" data-msc-notices aria-live="polite"></div>

			<div class="msc-body">
				<?php if ( self::is_empty() ) : ?>
					<?php self::render_empty_state(); ?>
				<?php else : ?>
					<?php self::render_items(); ?>
					<?php Meu_Side_Cart_Coupon::render(); ?>
				<?php endif; ?>
			</div>

			<?php self::render_footer(); ?>
			<div class="msc-loading-cover" data-msc-loading aria-hidden="true">
				<span class="msc-spinner" aria-hidden="true"></span>
			</div>
			<?php
		}

		/**
		 * Renders cart items.
		 */
		private static function render_items() {
			?>
			<section class="msc-section msc-items-section" aria-label="<?php esc_attr_e( 'Produtos no carrinho', 'portus-cart-for-woocommerce' ); ?>">
				<ul class="msc-items">
					<?php
					$cart_items = array_reverse( WC()->cart->get_cart(), true );

					foreach ( $cart_items as $cart_item_key => $cart_item ) :
						$product = isset( $cart_item['data'] ) ? $cart_item['data'] : null;

						if ( ! $product || ! $product->exists() || $cart_item['quantity'] <= 0 ) {
							continue;
						}

						$product_permalink = $product->is_visible() ? $product->get_permalink( $cart_item ) : '';
						$product_name      = $product->get_name();
						$favorite_id       = isset( $cart_item['product_id'] ) ? absint( $cart_item['product_id'] ) : $product->get_id();
						$max_quantity      = self::get_max_purchase_quantity( $product );
						$has_max_quantity  = $max_quantity >= 0;
						$plus_disabled     = $has_max_quantity && $cart_item['quantity'] >= $max_quantity;
						$stock_notice      = self::get_low_stock_notice( $product );
						$limit_notice      = '';

						if ( ! $stock_notice && $plus_disabled && $max_quantity > 0 ) {
							if ( $product->managing_stock() ) {
								/* translators: %d: maximum available stock quantity. */
								$limit_notice_text = __( 'Limite de estoque: %d disponível.', 'portus-cart-for-woocommerce' );
							} else {
								/* translators: %d: maximum purchase quantity. */
								$limit_notice_text = __( 'Limite de compra: %d unidade(s).', 'portus-cart-for-woocommerce' );
							}

							$limit_notice = sprintf( $limit_notice_text, $max_quantity );
						}

						/* translators: %s: product name. */
						$favorite_label_add = sprintf( __( 'Adicionar %s aos favoritos', 'portus-cart-for-woocommerce' ), $product_name );
						/* translators: %s: product name. */
						$favorite_label_remove = sprintf( __( 'Remover %s dos favoritos', 'portus-cart-for-woocommerce' ), $product_name );
						?>
						<li class="msc-item" data-msc-cart-item data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>">
							<?php if ( $product_permalink ) : ?>
								<a class="msc-item-image" href="<?php echo esc_url( $product_permalink ); ?>">
									<?php echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail' ) ); ?>
								</a>
							<?php else : ?>
								<div class="msc-item-image">
									<?php echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail' ) ); ?>
								</div>
							<?php endif; ?>

							<div class="msc-item-content">
								<div class="msc-item-top">
									<?php if ( $product_permalink ) : ?>
										<a class="msc-item-name" href="<?php echo esc_url( $product_permalink ); ?>">
											<?php echo esc_html( $product_name ); ?>
										</a>
									<?php else : ?>
										<span class="msc-item-name"><?php echo esc_html( $product_name ); ?></span>
									<?php endif; ?>

									<button class="msc-link-button" type="button" data-msc-remove-item data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>">
										<i class="fa-regular fa-trash-can msc-remove-trash-icon" aria-hidden="true"></i>
										<span class="msc-screen-reader"><?php esc_html_e( 'Remover item', 'portus-cart-for-woocommerce' ); ?></span>
									</button>
								</div>

								<?php
								$item_data = wc_get_formatted_cart_item_data( $cart_item );
								if ( $item_data ) :
									?>
									<div class="msc-item-meta"><?php echo wp_kses_post( $item_data ); ?></div>
								<?php endif; ?>


								<div class="msc-item-price-row">
									<span class="msc-item-price"><?php echo wp_kses_post( WC()->cart->get_product_price( $product ) ); ?></span>
									<strong class="msc-item-subtotal"><?php echo wp_kses_post( WC()->cart->get_product_subtotal( $product, $cart_item['quantity'] ) ); ?></strong>
								</div>

								<div class="msc-item-actions-row">
									<div class="msc-quantity" aria-label="<?php esc_attr_e( 'Quantidade', 'portus-cart-for-woocommerce' ); ?>" <?php echo $has_max_quantity ? 'data-msc-max-qty="' . esc_attr( $max_quantity ) . '"' : ''; ?>>
										<button type="button" data-msc-qty-minus aria-label="<?php esc_attr_e( 'Diminuir quantidade', 'portus-cart-for-woocommerce' ); ?>">
											<i class="fa-solid fa-minus" aria-hidden="true"></i>
										</button>
										<span class="msc-quantity-value">
											<input
												type="hidden"
												value="<?php echo esc_attr( $cart_item['quantity'] ); ?>"
												data-msc-qty-input
												data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>"
												<?php echo $has_max_quantity ? 'data-max-qty="' . esc_attr( $max_quantity ) . '"' : ''; ?>
											/>
											<span data-msc-qty-display><?php echo esc_html( $cart_item['quantity'] ); ?></span>
										</span>
										<button type="button" data-msc-qty-plus aria-label="<?php esc_attr_e( 'Aumentar quantidade', 'portus-cart-for-woocommerce' ); ?>" <?php disabled( $plus_disabled ); ?>>
											<i class="fa-solid fa-plus" aria-hidden="true"></i>
										</button>
									</div>

									<button
										class="msc-favorite-button"
										type="button"
										data-msc-favorite
										data-product-id="<?php echo esc_attr( $favorite_id ); ?>"
										data-label-add="<?php echo esc_attr( $favorite_label_add ); ?>"
										data-label-remove="<?php echo esc_attr( $favorite_label_remove ); ?>"
										aria-pressed="false"
										aria-label="<?php echo esc_attr( $favorite_label_add ); ?>"
									>
										<i class="fa-regular fa-heart" aria-hidden="true"></i>
									</button>
								</div>

								<?php if ( $stock_notice || $limit_notice ) : ?>
									<p class="msc-stock-alert">
										<i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
										<?php echo esc_html( $stock_notice ? $stock_notice : $limit_notice ); ?>
									</p>
								<?php endif; ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
			<?php
		}




		/**
		 * Renders empty cart state.
		 */
		private static function render_empty_state() {
			$button_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
			$button_text = class_exists( 'Meu_Side_Cart_Settings' ) ? Meu_Side_Cart_Settings::get( 'empty_button_text' ) : __( 'Ver produtos', 'portus-cart-for-woocommerce' );
			if ( class_exists( 'Meu_Side_Cart_Settings' ) ) {
				$custom_button_url = trim( (string) Meu_Side_Cart_Settings::get( 'empty_button_url' ) );

				if ( '' !== $custom_button_url ) {
					$button_url = $custom_button_url;
				}
			}
			?>
			<div class="msc-empty">
				<div class="msc-empty-mark" aria-hidden="true"><?php self::render_bag_icon(); ?></div>
				<h3><?php esc_html_e( 'Seu carrinho está vazio', 'portus-cart-for-woocommerce' ); ?></h3>
				<p><?php esc_html_e( 'Adicione um produto para continuar a compra.', 'portus-cart-for-woocommerce' ); ?></p>
				<a class="msc-button msc-button-primary" href="<?php echo esc_url( $button_url ); ?>">
					<?php echo esc_html( $button_text ); ?>
				</a>
			</div>
			<?php
		}

		/**
		 * Renders footer totals and checkout action.
		 */
		private static function render_footer() {
			if ( self::is_empty() ) {
				return;
			}
			$checkout_button_text = class_exists( 'Meu_Side_Cart_Settings' ) ? Meu_Side_Cart_Settings::get( 'checkout_button_text' ) : __( 'Finalizar compra', 'portus-cart-for-woocommerce' );
			$cart_button_text     = class_exists( 'Meu_Side_Cart_Settings' ) ? Meu_Side_Cart_Settings::get( 'cart_button_text' ) : __( 'Ver carrinho', 'portus-cart-for-woocommerce' );
			$show_cart_button     = class_exists( 'Meu_Side_Cart_Settings' ) ? Meu_Side_Cart_Settings::is_enabled( 'show_cart_button' ) : true;
			?>
			<footer class="msc-footer">
				<div class="msc-total-row">
					<span class="msc-total-label">
						<i class="fa-solid fa-receipt" aria-hidden="true"></i>
						<?php esc_html_e( 'Subtotal', 'portus-cart-for-woocommerce' ); ?>
					</span>
					<strong><?php echo wp_kses_post( WC()->cart->get_cart_subtotal() ); ?></strong>
				</div>
				<div class="msc-total-row">
					<span class="msc-total-label">
						<i class="fa-solid fa-truck-fast" aria-hidden="true"></i>
						<?php esc_html_e( 'Frete', 'portus-cart-for-woocommerce' ); ?>
					</span>
					<strong><?php echo wp_kses_post( WC()->cart->get_cart_shipping_total() ); ?></strong>
				</div>
				<div class="msc-total-row msc-total-row-strong">
					<span class="msc-total-label">
						<i class="fa-solid fa-wallet" aria-hidden="true"></i>
						<?php esc_html_e( 'Total', 'portus-cart-for-woocommerce' ); ?>
					</span>
					<strong><?php echo wp_kses_post( WC()->cart->get_total() ); ?></strong>
				</div>
				<a class="msc-button msc-button-primary" href="<?php echo esc_url( wc_get_checkout_url() ); ?>">
					<?php echo esc_html( $checkout_button_text ); ?>
				</a>
				<?php if ( $show_cart_button ) : ?>
					<a class="msc-button msc-button-secondary" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
						<?php echo esc_html( $cart_button_text ); ?>
					</a>
				<?php endif; ?>
			</footer>
			<?php
		}
	}
}
