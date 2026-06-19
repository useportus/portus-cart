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
			add_filter( 'wp_nav_menu_items', array( __CLASS__, 'append_menu_trigger' ), 20, 2 );
			add_shortcode( 'portus_cart_button', array( __CLASS__, 'render_trigger_shortcode' ) );
			add_action( 'init', array( __CLASS__, 'register_trigger_block' ) );
		}

		/**
		 * Adds fragments used by WooCommerce AJAX.
		 *
		 * @param array $fragments WooCommerce fragments.
		 * @return array
		 */
		public static function add_fragments( $fragments ) {
			$fragments['span.msc-floating-count'] = self::get_count_badge_html();
			$fragments['span.msc-header-count']   = self::get_header_count_badge_html();
			$fragments['div.msc-panel-content']  = '<div class="msc-panel-content" data-msc-content>' . self::get_panel_content_html() . '</div>';

			return $fragments;
		}

		/**
		 * Appends the optional cart trigger to one selected classic menu location.
		 *
		 * @param string   $items Existing menu items HTML.
		 * @param stdClass $args  Menu rendering arguments.
		 * @return string
		 */
		public static function append_menu_trigger( $items, $args ) {
			if ( ! class_exists( 'Meu_Side_Cart_Settings' ) || ! Meu_Side_Cart_Settings::is_enabled( 'enabled_header_trigger' ) || ! Meu_Side_Cart_Settings::is_enabled( 'auto_insert_menu_trigger' ) ) {
				return $items;
			}

			$selected_location = sanitize_key( (string) Meu_Side_Cart_Settings::get( 'header_menu_location' ) );
			$current_location  = isset( $args->theme_location ) ? sanitize_key( (string) $args->theme_location ) : '';

			if ( '' === $selected_location || $selected_location !== $current_location ) {
				return $items;
			}

			$trigger = self::get_header_trigger_html( array(), 'menu' );

			if ( '' === $trigger ) {
				return $items;
			}

			$item = '<li class="menu-item msc-menu-item">' . $trigger . '</li>';

			return 'start' === Meu_Side_Cart_Settings::get( 'header_menu_position' ) ? $item . $items : $items . $item;
		}

		/**
		 * Renders the manual header trigger shortcode.
		 *
		 * @param array $attributes Shortcode attributes.
		 * @return string
		 */
		public static function render_trigger_shortcode( $attributes = array() ) {
			$attributes = shortcode_atts(
				array(
					'label'   => '',
					'display' => '',
					'icon'    => '',
					'counter' => '',
				),
				is_array( $attributes ) ? $attributes : array(),
				'portus_cart_button'
			);

			return self::get_header_trigger_html( $attributes, 'shortcode' );
		}

		/**
		 * Registers a dynamic Gutenberg block for theme and template headers.
		 */
		public static function register_trigger_block() {
			if ( ! function_exists( 'register_block_type' ) ) {
				return;
			}

			wp_register_script(
				'portus-cart-for-woocommerce-trigger-block',
				MEU_SIDE_CART_URL . 'assets/js/block.js',
				array( 'wp-blocks', 'wp-block-editor', 'wp-components', 'wp-element', 'wp-i18n' ),
				MEU_SIDE_CART_VERSION,
				true
			);

			register_block_type(
				'portus/cart-trigger',
				array(
					'api_version'     => 2,
					'editor_script'   => 'portus-cart-for-woocommerce-trigger-block',
					'render_callback' => array( __CLASS__, 'render_trigger_block' ),
					'attributes'      => array(
						'label'   => array( 'type' => 'string', 'default' => '' ),
						'display' => array( 'type' => 'string', 'default' => '' ),
						'icon'    => array( 'type' => 'string', 'default' => '' ),
						'counter' => array( 'type' => 'boolean' ),
					),
				)
			);
		}

		/**
		 * Renders the dynamic Gutenberg block.
		 *
		 * @param array $attributes Block attributes.
		 * @return string
		 */
		public static function render_trigger_block( $attributes = array() ) {
			if ( isset( $attributes['counter'] ) ) {
				$attributes['counter'] = $attributes['counter'] ? 'yes' : 'no';
			}

			return self::get_header_trigger_html( is_array( $attributes ) ? $attributes : array(), 'block' );
		}

		/**
		 * Returns one safe and theme-neutral menu/header trigger.
		 *
		 * @param array  $overrides Optional shortcode or block overrides.
		 * @param string $context   Trigger placement context.
		 * @return string
		 */
		private static function get_header_trigger_html( $overrides = array(), $context = 'manual' ) {
			if ( ! class_exists( 'Meu_Side_Cart_Settings' ) || ! Meu_Side_Cart_Settings::is_enabled( 'enabled_header_trigger' ) || self::is_header_trigger_hidden_context() ) {
				return '';
			}

			$valid_icons    = array( 'bag-fill', 'cart', 'basket', 'bag' );
			$valid_displays = array( 'icon', 'icon-text', 'text' );
			$icon           = isset( $overrides['icon'] ) && '' !== $overrides['icon'] ? sanitize_key( $overrides['icon'] ) : sanitize_key( (string) Meu_Side_Cart_Settings::get( 'header_trigger_icon' ) );
			$display        = isset( $overrides['display'] ) && '' !== $overrides['display'] ? sanitize_key( $overrides['display'] ) : sanitize_key( (string) Meu_Side_Cart_Settings::get( 'header_trigger_display' ) );
			$label          = isset( $overrides['label'] ) && '' !== $overrides['label'] ? sanitize_text_field( $overrides['label'] ) : sanitize_text_field( (string) Meu_Side_Cart_Settings::get( 'header_trigger_label' ) );
			$show_counter   = isset( $overrides['counter'] ) && '' !== $overrides['counter'] ? 'yes' === $overrides['counter'] : Meu_Side_Cart_Settings::is_enabled( 'header_trigger_show_counter' );
			$style          = sanitize_key( (string) Meu_Side_Cart_Settings::get( 'header_trigger_style' ) );
			$icon           = in_array( $icon, $valid_icons, true ) ? $icon : 'bag-fill';
			$display        = in_array( $display, $valid_displays, true ) ? $display : 'icon-text';
			$style          = in_array( $style, array( 'minimal', 'outline', 'filled' ), true ) ? $style : 'minimal';
			$label          = '' !== $label ? $label : __( 'Carrinho', 'portus-cart-for-woocommerce' );
			$classes        = array(
				'msc-header-trigger',
				'msc-header-trigger-' . sanitize_html_class( $context ),
				'msc-header-trigger-' . sanitize_html_class( $style ),
				'msc-header-trigger-display-' . sanitize_html_class( $display ),
			);

			if ( ! Meu_Side_Cart_Settings::is_enabled( 'show_header_trigger_desktop' ) ) {
				$classes[] = 'msc-header-trigger-hide-desktop';
			}

			if ( ! Meu_Side_Cart_Settings::is_enabled( 'show_header_trigger_mobile' ) ) {
				$classes[] = 'msc-header-trigger-hide-mobile';
			}

			$custom_colors = Meu_Side_Cart_Settings::is_enabled( 'header_trigger_custom_colors' );
			$foreground    = $custom_colors ? Meu_Side_Cart_Settings::get( 'header_trigger_color' ) : Meu_Side_Cart_Settings::get( 'primary_color' );
			$background    = $custom_colors ? Meu_Side_Cart_Settings::get( 'header_trigger_background_color' ) : '#FFFFFF';
			$hover         = $custom_colors ? Meu_Side_Cart_Settings::get( 'header_trigger_hover_color' ) : Meu_Side_Cart_Settings::get( 'accent_color' );
			$counter_bg    = $custom_colors ? Meu_Side_Cart_Settings::get( 'header_trigger_counter_background' ) : Meu_Side_Cart_Settings::get( 'accent_color' );
			$counter_color = $custom_colors ? Meu_Side_Cart_Settings::get( 'header_trigger_counter_color' ) : '#FFFFFF';
			$foreground    = sanitize_hex_color( $foreground ) ?: '#00053A';
			$background    = sanitize_hex_color( $background ) ?: '#FFFFFF';
			$hover         = sanitize_hex_color( $hover ) ?: '#C0A821';
			$counter_bg    = sanitize_hex_color( $counter_bg ) ?: '#C0A821';
			$counter_color = sanitize_hex_color( $counter_color ) ?: '#FFFFFF';
			$icon_size     = Meu_Side_Cart_Settings::get_int( 'header_trigger_icon_size', 16, 36 );
			$inline_style  = sprintf(
				'--msc-header-color:%1$s;--msc-header-background:%2$s;--msc-header-hover:%3$s;--msc-header-counter-background:%4$s;--msc-header-counter-color:%5$s;--msc-header-icon-size:%6$dpx;',
				$foreground,
				$background,
				$hover,
				$counter_bg,
				$counter_color,
				$icon_size
			);
			/* translators: %s: cart button label. */
			$aria_label = sprintf( __( 'Abrir carrinho: %s', 'portus-cart-for-woocommerce' ), $label );

			ob_start();
			?>
			<button class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" type="button" data-msc-open style="<?php echo esc_attr( $inline_style ); ?>" aria-expanded="false" aria-controls="msc-side-panel" aria-label="<?php echo esc_attr( $aria_label ); ?>">
				<?php if ( 'text' !== $display ) : ?>
					<span class="msc-header-icon" aria-hidden="true"><?php self::render_floating_icon( $icon ); ?></span>
				<?php endif; ?>
				<?php if ( 'icon' !== $display ) : ?>
					<span class="msc-header-label"><?php echo esc_html( $label ); ?></span>
				<?php endif; ?>
				<?php if ( $show_counter ) : ?>
					<?php echo wp_kses_post( self::get_header_count_badge_html() ); ?>
				<?php endif; ?>
			</button>
			<?php

			return trim( ob_get_clean() );
		}

		/**
		 * Renders the fixed root, floating trigger and side panel.
		 */
		public static function render_shell() {
			$has_settings          = class_exists( 'Meu_Side_Cart_Settings' );
			$count                 = self::get_count();
			$css_variables         = $has_settings ? Meu_Side_Cart_Settings::get_css_variables() : '';
			$show_floating_button  = $has_settings ? Meu_Side_Cart_Settings::is_enabled( 'enabled_floating_button' ) : true;
			$show_floating_desktop = $has_settings ? Meu_Side_Cart_Settings::is_enabled( 'show_floating_desktop' ) : true;
			$show_floating_mobile  = $has_settings ? Meu_Side_Cart_Settings::is_enabled( 'show_floating_mobile' ) : true;
			$hide_on_product       = $has_settings ? Meu_Side_Cart_Settings::is_enabled( 'hide_floating_on_product' ) : true;
			$hide_floating_button  = self::is_floating_button_hidden_context( $hide_on_product );
			$floating_offset       = $has_settings ? Meu_Side_Cart_Settings::get_int( 'floating_bottom_offset', 70, 240 ) : 116;
			$floating_side         = $has_settings ? Meu_Side_Cart_Settings::get( 'floating_side' ) : 'right';
			$floating_side         = in_array( $floating_side, array( 'left', 'right' ), true ) ? $floating_side : 'right';
			$floating_icon         = $has_settings ? sanitize_key( Meu_Side_Cart_Settings::get( 'floating_icon' ) ) : 'bag-fill';
			$floating_icon         = in_array( $floating_icon, array( 'bag-fill', 'cart', 'basket', 'bag' ), true ) ? $floating_icon : 'bag-fill';
			$floating_shape        = $has_settings ? sanitize_key( Meu_Side_Cart_Settings::get( 'floating_shape' ) ) : 'circle';
			$floating_shape        = in_array( $floating_shape, array( 'circle', 'rounded' ), true ) ? $floating_shape : 'circle';
			$counter_position      = $has_settings ? sanitize_key( Meu_Side_Cart_Settings::get( 'floating_counter_position' ) ) : 'center';
			$counter_position      = in_array( $counter_position, array( 'center', 'top-right', 'top-left' ), true ) ? $counter_position : 'center';
			$counter_filled        = $has_settings && Meu_Side_Cart_Settings::is_enabled( 'floating_counter_background_enabled' );
			$button_style          = $css_variables . '--msc-floating-offset:' . $floating_offset . 'px;';
			$button_classes        = array(
				'msc-floating-button',
				'msc-floating-' . $floating_side,
				'msc-floating-shape-' . $floating_shape,
				'msc-floating-counter-' . $counter_position,
			);

			if ( $counter_filled ) {
				$button_classes[] = 'msc-floating-counter-filled';
			}

			if ( ! $show_floating_desktop ) {
				$button_classes[] = 'msc-floating-hide-desktop';
			}

			if ( ! $show_floating_mobile ) {
				$button_classes[] = 'msc-floating-hide-mobile';
			}
			?>
			<div class="msc-side-cart" id="meu-side-cart" data-msc-root data-count="<?php echo esc_attr( $count ); ?>" style="<?php echo esc_attr( $css_variables ); ?>">
				<?php if ( $show_floating_button && ( $show_floating_desktop || $show_floating_mobile ) && ! $hide_floating_button ) : ?>
					<button class="<?php echo esc_attr( implode( ' ', $button_classes ) ); ?>" type="button" data-msc-open data-msc-floating-offset="<?php echo esc_attr( $floating_offset ); ?>" style="<?php echo esc_attr( $button_style ); ?>" aria-expanded="false" aria-controls="msc-side-panel" aria-label="<?php esc_attr_e( 'Abrir carrinho', 'portus-cart-for-woocommerce' ); ?>">
						<span class="msc-floating-icon" aria-hidden="true"><?php self::render_floating_icon( $floating_icon ); ?></span>
						<?php echo wp_kses_post( self::get_count_badge_html() ); ?>
					</button>
				<?php endif; ?>

				<div class="msc-overlay" data-msc-close hidden></div>

				<aside class="msc-panel" id="msc-side-panel" role="dialog" aria-modal="true" aria-labelledby="msc-title" aria-hidden="true" tabindex="-1">
					<div class="msc-panel-content" data-msc-content>
						<?php self::render_panel_content(); ?>
					</div>
				</aside>

				<div class="msc-add-toast" data-msc-add-toast role="status" aria-live="polite" hidden>
					<span class="msc-add-toast-icon" aria-hidden="true"></span>
					<span data-msc-add-toast-message><?php esc_html_e( 'Produto adicionado ao carrinho.', 'portus-cart-for-woocommerce' ); ?></span>
					<button type="button" data-msc-open data-msc-toast-open aria-expanded="false" aria-controls="msc-side-panel"><?php esc_html_e( 'Ver carrinho', 'portus-cart-for-woocommerce' ); ?></button>
				</div>
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
		 * Renders one allow-listed local SVG mask for the floating trigger.
		 *
		 * @param string $icon Icon key.
		 */
		private static function render_floating_icon( $icon ) {
			$icon = in_array( $icon, array( 'bag-fill', 'cart', 'basket', 'bag' ), true ) ? $icon : 'bag-fill';
			?>
			<span class="msc-floating-glyph msc-floating-glyph-<?php echo esc_attr( sanitize_html_class( $icon ) ); ?>" aria-hidden="true"></span>
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
		 * Hides menu and header triggers during checkout completion flows.
		 *
		 * @return bool
		 */
		private static function is_header_trigger_hidden_context() {
			if ( function_exists( 'is_checkout' ) && is_checkout() ) {
				return true;
			}

			if ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) {
				return true;
			}

			return function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-received' );
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
		 * Returns the menu/header count badge.
		 *
		 * @return string
		 */
		public static function get_header_count_badge_html() {
			return '<span class="msc-header-count" data-msc-count>' . esc_html( self::get_count() ) . '</span>';
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
