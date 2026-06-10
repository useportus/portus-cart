<?php
/**
 * Admin live preview markup.
 *
 * Variables are provided by Meu_Side_Cart_Settings::render_preview().
 *
 * @package PortusCart
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="portus-cart-for-woocommerce-admin__preview-section">
	<div class="portus-cart-for-woocommerce-admin__preview-copy">
		<h2><?php esc_html_e( 'Preview do carrinho', 'portus-cart-for-woocommerce' ); ?></h2>
		<p><?php esc_html_e( 'Amostra visual com dados fictícios. Ela atualiza automaticamente enquanto você altera os campos desta tela.', 'portus-cart-for-woocommerce' ); ?></p>
	</div>

	<div class="portus-cart-for-woocommerce-preview portus-cart-for-woocommerce-preview--floating-<?php echo esc_attr( $floating_side ); ?>" data-portus-cart-for-woocommerce-preview data-preview-shipping-price="<?php echo esc_attr( $remaining_price_text ); ?>" style="<?php echo esc_attr( $preview_style ); ?>">
		<div class="portus-cart-for-woocommerce-preview__overlay" aria-hidden="true"></div>
		<div class="portus-cart-for-woocommerce-preview__panel">
			<header class="portus-cart-for-woocommerce-preview__header">
				<span class="portus-cart-for-woocommerce-preview__bag" aria-hidden="true">
					<span class="portus-cart-for-woocommerce-preview__bag-icon" aria-hidden="true"></span>
					<strong><?php echo esc_html( $preview_count ); ?></strong>
				</span>
				<div>
					<h3 data-preview-text="cart_title"><?php echo esc_html( $cart_title ); ?></h3>
					<span>
						<?php
						printf(
							/* translators: %d: preview cart item count. */
							esc_html__( '%d itens', 'portus-cart-for-woocommerce' ),
							absint( $preview_count )
						);
						?>
					</span>
				</div>
				<i class="fa-solid fa-xmark" aria-hidden="true"></i>
			</header>

			<div class="portus-cart-for-woocommerce-preview__body">
				<?php foreach ( $preview_items as $meu_side_cart_preview_item ) : ?>
					<div class="portus-cart-for-woocommerce-preview__item">
						<div class="portus-cart-for-woocommerce-preview__image" aria-hidden="true"><?php echo esc_html( $meu_side_cart_preview_item['badge'] ); ?></div>
						<div class="portus-cart-for-woocommerce-preview__item-copy">
							<div class="portus-cart-for-woocommerce-preview__item-top">
								<strong><?php echo esc_html( $meu_side_cart_preview_item['name'] ); ?></strong>
								<span class="portus-cart-for-woocommerce-preview__remove" aria-hidden="true">
									<i class="fa-regular fa-trash-can"></i>
								</span>
							</div>
							<small><?php echo esc_html( $meu_side_cart_preview_item['meta'] ); ?></small>
							<div class="portus-cart-for-woocommerce-preview__price-row">
								<span><?php echo wp_kses_post( self::format_preview_price( $meu_side_cart_preview_item['price'] ) ); ?></span>
								<strong><?php echo wp_kses_post( self::format_preview_price( $meu_side_cart_preview_item['subtotal'] ) ); ?></strong>
							</div>
							<div class="portus-cart-for-woocommerce-preview__actions" aria-hidden="true">
								<div class="portus-cart-for-woocommerce-preview__quantity">
									<span>-</span>
									<strong><?php echo esc_html( $meu_side_cart_preview_item['quantity'] ); ?></strong>
									<span>+</span>
								</div>
								<span class="portus-cart-for-woocommerce-preview__favorite">
									<i class="fa-regular fa-heart"></i>
								</span>
							</div>
						</div>
					</div>
				<?php endforeach; ?>

				<div class="portus-cart-for-woocommerce-preview__notice" data-preview-toggle="show_low_stock_alerts" <?php echo 'yes' === $settings['show_low_stock_alerts'] ? '' : 'hidden'; ?>>
					<span class="dashicons dashicons-warning" aria-hidden="true"></span>
					<span data-preview-stock>
						<?php
						printf(
							/* translators: %d: low stock threshold shown in the admin preview. */
							esc_html__( 'Estoque baixo: restam %d unidades.', 'portus-cart-for-woocommerce' ),
							absint( $settings['low_stock_threshold'] )
						);
						?>
					</span>
				</div>

				<div class="portus-cart-for-woocommerce-preview__coupon" aria-label="<?php esc_attr_e( 'Cupom de desconto', 'portus-cart-for-woocommerce' ); ?>">
					<div class="portus-cart-for-woocommerce-preview__coupon-title">
						<i class="fa-solid fa-ticket" aria-hidden="true"></i>
						<strong><?php esc_html_e( 'Cupom de desconto', 'portus-cart-for-woocommerce' ); ?></strong>
					</div>
					<div class="portus-cart-for-woocommerce-preview__coupon-form" aria-hidden="true">
						<span><?php esc_html_e( 'Digite seu cupom', 'portus-cart-for-woocommerce' ); ?></span>
						<button type="button" disabled>
							<i class="fa-solid fa-check" aria-hidden="true"></i>
							<?php esc_html_e( 'Aplicar', 'portus-cart-for-woocommerce' ); ?>
						</button>
					</div>
				</div>




			</div>

			<footer class="portus-cart-for-woocommerce-preview__footer">
				<div><span><i class="fa-solid fa-receipt" aria-hidden="true"></i><?php esc_html_e( 'Subtotal', 'portus-cart-for-woocommerce' ); ?></span><strong><?php echo wp_kses_post( self::format_preview_price( $preview_subtotal ) ); ?></strong></div>
				<div><span><i class="fa-solid fa-truck-fast" aria-hidden="true"></i><?php esc_html_e( 'Frete', 'portus-cart-for-woocommerce' ); ?></span><strong><?php echo wp_kses_post( self::format_preview_price( 0 ) ); ?></strong></div>
				<div class="portus-cart-for-woocommerce-preview__total"><span><i class="fa-solid fa-wallet" aria-hidden="true"></i><?php esc_html_e( 'Total', 'portus-cart-for-woocommerce' ); ?></span><strong><?php echo wp_kses_post( self::format_preview_price( $preview_subtotal ) ); ?></strong></div>
				<a class="portus-cart-for-woocommerce-preview__primary" data-preview-text="checkout_button_text" href="#" onclick="return false;"><?php echo esc_html( $checkout_text ); ?></a>
				<a class="portus-cart-for-woocommerce-preview__secondary" data-preview-toggle="show_cart_button" data-preview-text="cart_button_text" href="#" onclick="return false;" <?php echo 'yes' === $settings['show_cart_button'] ? '' : 'hidden'; ?>><?php echo esc_html( $cart_button_text ); ?></a>
			</footer>
		</div>
		<button class="portus-cart-for-woocommerce-preview__floating" data-preview-toggle="enabled_floating_button" type="button" disabled <?php echo $floating_enabled ? '' : 'hidden'; ?>>
			<span class="portus-cart-for-woocommerce-preview__bag-icon" aria-hidden="true"></span>
			<span><?php echo esc_html( $preview_count ); ?></span>
		</button>
	</div>
</section>
