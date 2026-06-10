<?php
/**
 * Admin support tab markup.
 *
 * Variables and helpers are provided by Meu_Side_Cart_Settings::render_support_tab().
 *
 * @package PortusCart
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="portus-cart-for-woocommerce-admin__card portus-cart-for-woocommerce-admin__support-hero">
	<h2><?php esc_html_e( 'Suporte Portus', 'portus-cart-for-woocommerce' ); ?></h2>
	<p><?php esc_html_e( 'Reúna informações importantes da instalação, confira o checklist antes de abrir um chamado e exporte um diagnóstico sem dados sensíveis.', 'portus-cart-for-woocommerce' ); ?></p>
	<div class="portus-cart-for-woocommerce-admin__support-actions">
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="meu_side_cart_export_diagnostics" />
			<?php wp_nonce_field( 'meu_side_cart_export_diagnostics' ); ?>
			<?php submit_button( __( 'Exportar diagnóstico', 'portus-cart-for-woocommerce' ), 'secondary', 'submit', false ); ?>
		</form>
		<a href="<?php echo esc_url( self::get_page_url( array( 'tab' => 'help' ) ) ); ?>"><?php esc_html_e( 'Abrir guia', 'portus-cart-for-woocommerce' ); ?></a>
		<a href="<?php echo esc_url( self::get_page_url( array( 'tab' => 'status' ) ) ); ?>"><?php esc_html_e( 'Ver saúde do plugin', 'portus-cart-for-woocommerce' ); ?></a>
	</div>
</section>

<section class="portus-cart-for-woocommerce-admin__card portus-cart-for-woocommerce-admin__support-card">
	<h2><?php esc_html_e( 'Informações para atendimento', 'portus-cart-for-woocommerce' ); ?></h2>
	<div class="portus-cart-for-woocommerce-admin__support-meta">
		<div>
			<span><?php esc_html_e( 'Versão', 'portus-cart-for-woocommerce' ); ?></span>
			<strong><?php echo esc_html( MEU_SIDE_CART_VERSION ); ?></strong>
		</div>
		<div>
			<span><?php esc_html_e( 'Documentação', 'portus-cart-for-woocommerce' ); ?></span>
			<strong><?php esc_html_e( 'Guia interno disponível', 'portus-cart-for-woocommerce' ); ?></strong>
		</div>
		<div>
			<span><?php esc_html_e( 'Canal futuro', 'portus-cart-for-woocommerce' ); ?></span>
			<strong><?php esc_html_e( 'suporte@useportus.com.br', 'portus-cart-for-woocommerce' ); ?></strong>
		</div>
		<div>
			<span><?php esc_html_e( 'Atualizações', 'portus-cart-for-woocommerce' ); ?></span>
			<strong><?php esc_html_e( 'Preparado para servidor próprio', 'portus-cart-for-woocommerce' ); ?></strong>
		</div>
	</div>
</section>

<div class="portus-cart-for-woocommerce-admin__support-split">
	<section class="portus-cart-for-woocommerce-admin__card portus-cart-for-woocommerce-admin__support-card">
		<h2><?php esc_html_e( 'Antes de pedir suporte', 'portus-cart-for-woocommerce' ); ?></h2>
		<div class="portus-cart-for-woocommerce-admin__check-grid">
			<?php foreach ( self::get_support_checklist() as $meu_side_cart_item ) : ?>
				<label>
					<input type="checkbox" />
					<span><?php echo esc_html( $meu_side_cart_item ); ?></span>
				</label>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="portus-cart-for-woocommerce-admin__card portus-cart-for-woocommerce-admin__support-card">
		<h2><?php esc_html_e( 'O que enviar em um chamado', 'portus-cart-for-woocommerce' ); ?></h2>
		<ul class="portus-cart-for-woocommerce-admin__help-list">
			<li><?php esc_html_e( 'Arquivo de diagnóstico exportado nesta aba.', 'portus-cart-for-woocommerce' ); ?></li>
			<li><?php esc_html_e( 'Print ou vídeo curto mostrando o comportamento.', 'portus-cart-for-woocommerce' ); ?></li>
			<li><?php esc_html_e( 'Página onde o problema acontece e passos para repetir.', 'portus-cart-for-woocommerce' ); ?></li>
			<li><?php esc_html_e( 'Tema ativo, plugins de cache e plugins que alteram checkout ou carrinho.', 'portus-cart-for-woocommerce' ); ?></li>
		</ul>
	</section>
</div>
