<?php
/**
 * Coupon rendering.
 *
 * @package PortusCart
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Meu_Side_Cart_Coupon' ) ) {
	/**
	 * Coupon UI.
	 */
	class Meu_Side_Cart_Coupon {
		/**
		 * Reserved for future coupon hooks.
		 */
		public static function init() {}

		/**
		 * Renders coupon form and applied coupons.
		 */
		public static function render() {
			if ( ! function_exists( 'wc_coupons_enabled' ) || ! wc_coupons_enabled() || ! WC()->cart ) {
				return;
			}

			$coupons = WC()->cart->get_coupons();
			?>
			<section class="msc-section msc-coupon" aria-label="<?php esc_attr_e( 'Cupom de desconto', 'portus-cart-for-woocommerce' ); ?>">
				<div class="msc-section-title">
					<h3><i class="fa-solid fa-ticket" aria-hidden="true"></i><?php esc_html_e( 'Cupom de desconto', 'portus-cart-for-woocommerce' ); ?></h3>
				</div>

				<?php if ( ! empty( $coupons ) ) : ?>
					<ul class="msc-coupons-list">
						<?php foreach ( $coupons as $code => $coupon ) : ?>
							<li>
								<span><?php echo esc_html( wc_cart_totals_coupon_label( $coupon, false ) ); ?></span>
								<button type="button" class="msc-link-button" data-msc-remove-coupon data-coupon="<?php echo esc_attr( $code ); ?>">
									<i class="fa-solid fa-xmark" aria-hidden="true"></i>
									<span class="msc-screen-reader"><?php esc_html_e( 'Remover cupom', 'portus-cart-for-woocommerce' ); ?></span>
								</button>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<form class="msc-inline-form" data-msc-coupon-form>
					<label class="msc-screen-reader" for="msc-coupon-code"><?php esc_html_e( 'Codigo do cupom', 'portus-cart-for-woocommerce' ); ?></label>
					<input id="msc-coupon-code" type="text" name="coupon" autocomplete="off" placeholder="<?php esc_attr_e( 'Digite seu cupom', 'portus-cart-for-woocommerce' ); ?>" />
					<button type="submit"><i class="fa-solid fa-check" aria-hidden="true"></i><?php esc_html_e( 'Aplicar', 'portus-cart-for-woocommerce' ); ?></button>
				</form>
			</section>
			<?php
		}
	}
}
