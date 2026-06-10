<?php
/**
 * Admin health tab markup.
 *
 * Variables and helpers are provided by Meu_Side_Cart_Settings::render_status_tab().
 *
 * @package PortusCart
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="portus-cart-for-woocommerce-admin__card portus-cart-for-woocommerce-admin__status-card portus-cart-for-woocommerce-admin__status-card-main">
	<h2><?php esc_html_e( 'Saúde do plugin', 'portus-cart-for-woocommerce' ); ?></h2>
	<p><?php esc_html_e( 'Diagnóstico rápido do ambiente, integrações WooCommerce e pontos de compatibilidade com temas.', 'portus-cart-for-woocommerce' ); ?></p>

	<div class="portus-cart-for-woocommerce-admin__status-summary">
		<div>
			<strong><?php echo esc_html( MEU_SIDE_CART_VERSION ); ?></strong>
			<span><?php esc_html_e( 'Versão instalada', 'portus-cart-for-woocommerce' ); ?></span>
		</div>
		<div>
			<strong><?php echo esc_html( (string) $counts['good'] ); ?></strong>
			<span><?php esc_html_e( 'Itens OK', 'portus-cart-for-woocommerce' ); ?></span>
		</div>
		<div>
			<strong><?php echo esc_html( (string) $counts['warning'] ); ?></strong>
			<span><?php esc_html_e( 'Alertas', 'portus-cart-for-woocommerce' ); ?></span>
		</div>
		<div>
			<strong><?php echo esc_html( (string) $counts['info'] ); ?></strong>
			<span><?php esc_html_e( 'Informativos', 'portus-cart-for-woocommerce' ); ?></span>
		</div>
	</div>
</section>

<?php foreach ( $status_groups as $meu_side_cart_group ) : ?>
	<section class="portus-cart-for-woocommerce-admin__card portus-cart-for-woocommerce-admin__status-card">
		<h2><?php echo esc_html( $meu_side_cart_group['title'] ); ?></h2>
		<?php if ( ! empty( $meu_side_cart_group['description'] ) ) : ?>
			<p><?php echo esc_html( $meu_side_cart_group['description'] ); ?></p>
		<?php endif; ?>
		<div class="portus-cart-for-woocommerce-admin__status-list">
			<?php foreach ( $meu_side_cart_group['items'] as $meu_side_cart_item ) : ?>
				<?php self::render_status_item( $meu_side_cart_item ); ?>
			<?php endforeach; ?>
		</div>
	</section>
<?php endforeach; ?>

<section class="portus-cart-for-woocommerce-admin__card portus-cart-for-woocommerce-admin__status-card">
	<h2><?php esc_html_e( 'Informações do ambiente', 'portus-cart-for-woocommerce' ); ?></h2>
	<div class="portus-cart-for-woocommerce-admin__status-table">
		<?php foreach ( self::get_environment_rows() as $meu_side_cart_label => $meu_side_cart_value ) : ?>
			<div>
				<span><?php echo esc_html( $meu_side_cart_label ); ?></span>
				<strong><?php echo esc_html( $meu_side_cart_value ); ?></strong>
			</div>
		<?php endforeach; ?>
	</div>
</section>
