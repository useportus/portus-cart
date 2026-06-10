<?php
/**
 * Admin about tab markup.
 *
 * Variables and helpers are provided by Meu_Side_Cart_Settings::render_about_tab().
 *
 * @package PortusCart
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="portus-cart-for-woocommerce-admin__card portus-cart-for-woocommerce-admin__about-hero">
	<img class="portus-cart-for-woocommerce-admin__about-logo" src="<?php echo esc_url( $logo_url ); ?>" alt="" width="96" height="96" />
	<div>
		<span class="portus-cart-for-woocommerce-admin__about-kicker"><?php esc_html_e( 'Produto Portus', 'portus-cart-for-woocommerce' ); ?></span>
		<h2><?php esc_html_e( 'Portus Cart for WooCommerce', 'portus-cart-for-woocommerce' ); ?></h2>
		<p><?php esc_html_e( 'Carrinho lateral AJAX criado para transformar a experiência de compra em uma jornada rápida, clara e pronta para conversão.', 'portus-cart-for-woocommerce' ); ?></p>
		<div class="portus-cart-for-woocommerce-admin__about-actions">
			<a href="<?php echo esc_url( 'https://useportus.com/cart' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Site do produto', 'portus-cart-for-woocommerce' ); ?></a>
			<a href="<?php echo esc_url( self::get_page_url( array( 'tab' => 'help' ) ) ); ?>"><?php esc_html_e( 'Guia rápido', 'portus-cart-for-woocommerce' ); ?></a>
			<a href="<?php echo esc_url( self::get_page_url( array( 'tab' => 'support' ) ) ); ?>"><?php esc_html_e( 'Suporte', 'portus-cart-for-woocommerce' ); ?></a>
			<a href="<?php echo esc_url( self::get_page_url( array( 'tab' => 'status' ) ) ); ?>"><?php esc_html_e( 'Saúde do plugin', 'portus-cart-for-woocommerce' ); ?></a>
		</div>
	</div>
</section>

<section class="portus-cart-for-woocommerce-admin__card portus-cart-for-woocommerce-admin__about-card">
	<h2><?php esc_html_e( 'Identidade do plugin', 'portus-cart-for-woocommerce' ); ?></h2>
	<div class="portus-cart-for-woocommerce-admin__about-meta">
		<div>
			<span><?php esc_html_e( 'Nome', 'portus-cart-for-woocommerce' ); ?></span>
			<strong><?php esc_html_e( 'Portus Cart for WooCommerce', 'portus-cart-for-woocommerce' ); ?></strong>
		</div>
		<div>
			<span><?php esc_html_e( 'Marca', 'portus-cart-for-woocommerce' ); ?></span>
			<strong><?php esc_html_e( 'Portus', 'portus-cart-for-woocommerce' ); ?></strong>
		</div>
		<div>
			<span><?php esc_html_e( 'Versão instalada', 'portus-cart-for-woocommerce' ); ?></span>
			<strong><?php echo esc_html( MEU_SIDE_CART_VERSION ); ?></strong>
		</div>
		<div>
			<span><?php esc_html_e( 'Compatibilidade', 'portus-cart-for-woocommerce' ); ?></span>
			<strong><?php esc_html_e( 'WooCommerce padrão', 'portus-cart-for-woocommerce' ); ?></strong>
		</div>
	</div>
</section>

<section class="portus-cart-for-woocommerce-admin__card portus-cart-for-woocommerce-admin__about-card">
	<h2><?php esc_html_e( 'O que o Portus Cart entrega', 'portus-cart-for-woocommerce' ); ?></h2>
	<div class="portus-cart-for-woocommerce-admin__about-grid">
		<div>
			<strong><?php esc_html_e( 'Carrinho lateral avançado', 'portus-cart-for-woocommerce' ); ?></strong>
			<span><?php esc_html_e( 'Produtos, quantidades, remoção, cupom e checkout em uma experiência rápida.', 'portus-cart-for-woocommerce' ); ?></span>
		</div>
		<div>
			<strong><?php esc_html_e( 'Controle pelo admin', 'portus-cart-for-woocommerce' ); ?></strong>
			<span><?php esc_html_e( 'Ajustes visuais, compatibilidade e diagnóstico sem editar código.', 'portus-cart-for-woocommerce' ); ?></span>
		</div>
		<div>
			<strong><?php esc_html_e( 'Base para venda futura', 'portus-cart-for-woocommerce' ); ?></strong>
			<span><?php esc_html_e( 'Base gratuita para validar o carrinho lateral em WooCommerce.', 'portus-cart-for-woocommerce' ); ?></span>
		</div>
	</div>
</section>
