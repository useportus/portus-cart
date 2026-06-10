<?php
/**
 * Admin help tab markup.
 *
 * Variables and helpers are provided by Meu_Side_Cart_Settings::render_help_tab().
 *
 * @package PortusCart
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="portus-cart-for-woocommerce-admin__card portus-cart-for-woocommerce-admin__help-hero">
	<h2><?php esc_html_e( 'Ajuda / Documentação', 'portus-cart-for-woocommerce' ); ?></h2>
	<p><?php esc_html_e( 'Guia rápido para configurar, testar e manter o Portus Cart for WooCommerce com segurança.', 'portus-cart-for-woocommerce' ); ?></p>
	<div class="portus-cart-for-woocommerce-admin__help-links">
		<a href="<?php echo esc_url( self::get_page_url( array( 'tab' => 'general' ) ) ); ?>"><?php esc_html_e( 'Configurações do carrinho', 'portus-cart-for-woocommerce' ); ?></a>
		<a href="<?php echo esc_url( self::get_page_url( array( 'tab' => 'status' ) ) ); ?>"><?php esc_html_e( 'Ver diagnóstico', 'portus-cart-for-woocommerce' ); ?></a>
		<a href="<?php echo esc_url( self::get_page_url( array( 'tab' => 'support' ) ) ); ?>"><?php esc_html_e( 'Suporte', 'portus-cart-for-woocommerce' ); ?></a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings' ) ); ?>"><?php esc_html_e( 'WooCommerce', 'portus-cart-for-woocommerce' ); ?></a>
	</div>
</section>

<section class="portus-cart-for-woocommerce-admin__card portus-cart-for-woocommerce-admin__help-card">
	<h2><?php esc_html_e( 'Comece por aqui', 'portus-cart-for-woocommerce' ); ?></h2>
	<ol class="portus-cart-for-woocommerce-admin__help-list">
		<li><?php esc_html_e( 'Confira a aba Saúde do plugin para confirmar WooCommerce, checkout, carrinho dinâmico e tema ativo.', 'portus-cart-for-woocommerce' ); ?></li>
		<li><?php esc_html_e( 'Ajuste título, botão flutuante e distância inferior na aba Carrinho.', 'portus-cart-for-woocommerce' ); ?></li>
		<li><?php esc_html_e( 'Defina as cores principais na aba Visual.', 'portus-cart-for-woocommerce' ); ?></li>
		<li><?php esc_html_e( 'Revise estoque, cupons, botões, cores e compatibilidade.', 'portus-cart-for-woocommerce' ); ?></li>
		<li><?php esc_html_e( 'Teste produto simples, produto variável, cupom, quantidade, remoção, favoritos e checkout em desktop e mobile.', 'portus-cart-for-woocommerce' ); ?></li>
	</ol>
</section>

<section class="portus-cart-for-woocommerce-admin__card portus-cart-for-woocommerce-admin__help-card">
	<h2><?php esc_html_e( 'O que cada aba controla', 'portus-cart-for-woocommerce' ); ?></h2>
	<div class="portus-cart-for-woocommerce-admin__help-table">
		<?php foreach ( self::get_help_tab_rows() as $meu_side_cart_row ) : ?>
			<div>
				<strong><?php echo esc_html( $meu_side_cart_row['title'] ); ?></strong>
				<span><?php echo esc_html( $meu_side_cart_row['description'] ); ?></span>
			</div>
		<?php endforeach; ?>
	</div>
</section>

<section class="portus-cart-for-woocommerce-admin__card portus-cart-for-woocommerce-admin__help-card">
	<h2><?php esc_html_e( 'Checklist de teste', 'portus-cart-for-woocommerce' ); ?></h2>
	<div class="portus-cart-for-woocommerce-admin__check-grid">
		<?php foreach ( self::get_help_checklist() as $meu_side_cart_item ) : ?>
			<label>
				<input type="checkbox" />
				<span><?php echo esc_html( $meu_side_cart_item ); ?></span>
			</label>
		<?php endforeach; ?>
	</div>
</section>

<section class="portus-cart-for-woocommerce-admin__card portus-cart-for-woocommerce-admin__help-card">
	<h2><?php esc_html_e( 'Solução de problemas', 'portus-cart-for-woocommerce' ); ?></h2>
	<div class="portus-cart-for-woocommerce-admin__faq-list">
		<?php foreach ( self::get_help_faq() as $meu_side_cart_item ) : ?>
			<details>
				<summary><?php echo esc_html( $meu_side_cart_item['question'] ); ?></summary>
				<p><?php echo esc_html( $meu_side_cart_item['answer'] ); ?></p>
			</details>
		<?php endforeach; ?>
	</div>
</section>

<section class="portus-cart-for-woocommerce-admin__card portus-cart-for-woocommerce-admin__help-card">
	<h2><?php esc_html_e( 'Boas práticas para compatibilidade', 'portus-cart-for-woocommerce' ); ?></h2>
	<ul class="portus-cart-for-woocommerce-admin__help-list">
		<li><?php esc_html_e( 'Mantenha WooCommerce, tema e plugins principais atualizados.', 'portus-cart-for-woocommerce' ); ?></li>
		<li><?php esc_html_e( 'Ao alterar CSS ou JS, aumente a versão do plugin para evitar cache antigo.', 'portus-cart-for-woocommerce' ); ?></li>
		<li><?php esc_html_e( 'Depois de publicar mudanças visuais, limpe cache do navegador, WordPress e Hostinger.', 'portus-cart-for-woocommerce' ); ?></li>
		<li><?php esc_html_e( 'Evite editar arquivos diretamente no servidor quando a versão oficial está no GitHub.', 'portus-cart-for-woocommerce' ); ?></li>
		<li><?php esc_html_e( 'Use a exportação de configurações antes de grandes alterações no admin.', 'portus-cart-for-woocommerce' ); ?></li>
	</ul>
</section>
