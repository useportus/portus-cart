<?php
/**
 * Admin settings.
 *
 * @package PortusCart
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Meu_Side_Cart_Settings' ) ) {
	/**
	 * Adds a WooCommerce admin settings screen and exposes safe frontend options.
	 */
	final class Meu_Side_Cart_Settings {
		const OPTION_NAME = 'meu_side_cart_settings';
		const MENU_SLUG   = 'portus-cart-for-woocommerce';

		/**
		 * Registers admin hooks.
		 */
		public static function init() {
			add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
			add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
			add_action( 'admin_post_meu_side_cart_export_settings', array( __CLASS__, 'handle_export' ) );
			add_action( 'admin_post_meu_side_cart_export_diagnostics', array( __CLASS__, 'handle_diagnostics_export' ) );
			add_action( 'admin_post_meu_side_cart_import_settings', array( __CLASS__, 'handle_import' ) );
			add_action( 'admin_post_meu_side_cart_reset_settings', array( __CLASS__, 'handle_reset' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
			add_filter( 'plugin_action_links_' . MEU_SIDE_CART_BASENAME, array( __CLASS__, 'add_plugin_action_links' ) );
		}

		/**
		 * Adds the settings page under WooCommerce.
		 */
		public static function add_menu() {
			add_submenu_page(
				'woocommerce',
				__( 'Portus Cart', 'portus-cart-for-woocommerce' ),
				__( 'Portus Cart', 'portus-cart-for-woocommerce' ),
				'manage_woocommerce',
				self::MENU_SLUG,
				array( __CLASS__, 'render_page' )
			);
		}

		/**
		 * Registers the option used by the settings form.
		 */
		public static function register_settings() {
			register_setting(
				'meu_side_cart_settings_group',
				self::OPTION_NAME,
				array(
					'type'              => 'array',
					'default'           => self::get_defaults(),
					'sanitize_callback' => array( __CLASS__, 'sanitize_settings' ),
				)
			);
		}

		/**
		 * Adds a direct settings link on the plugins screen.
		 *
		 * @param string[] $links Existing plugin action links.
		 * @return string[]
		 */
		public static function add_plugin_action_links( $links ) {
			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				return $links;
			}

			$settings_url = admin_url( 'admin.php?page=' . self::MENU_SLUG );

			array_unshift(
				$links,
				sprintf(
					'<a href="%1$s">%2$s</a>',
					esc_url( $settings_url ),
					esc_html__( 'Configurações', 'portus-cart-for-woocommerce' )
				)
			);

			return $links;
		}

		/**
		 * Enqueues admin-only assets for the plugin settings screen.
		 */
		public static function enqueue_admin_assets() {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin page routing value.
			$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

			if ( self::MENU_SLUG !== $page ) {
				return;
			}

			wp_enqueue_style(
				'portus-cart-for-woocommerce-admin',
				MEU_SIDE_CART_URL . 'assets/css/admin.css',
				array(),
				MEU_SIDE_CART_VERSION
			);

			wp_enqueue_script(
				'portus-cart-for-woocommerce-admin',
				MEU_SIDE_CART_URL . 'assets/js/admin.js',
				array(),
				MEU_SIDE_CART_VERSION,
				true
			);

			wp_add_inline_script(
				'portus-cart-for-woocommerce-admin',
				'window.aureaCartAdmin = ' . wp_json_encode(
					array(
						'optionName' => self::OPTION_NAME,
					)
				) . ';',
				'before'
			);
		}

		/**
		 * Returns default settings. These defaults preserve the current cart behavior.
		 *
		 * @return array
		 */
		public static function get_defaults() {
			return array(
				'cart_title'                   => __( 'Meu carrinho', 'portus-cart-for-woocommerce' ),
				'enabled_floating_button'      => 'yes',
				'hide_floating_on_product'     => 'yes',
				'floating_bottom_offset'       => 116,
				'floating_icon'                => 'bag-fill',
				'floating_button_size'         => 50,
				'floating_icon_size'           => 28,
				'floating_shape'               => 'circle',
				'floating_background_color'    => '#FFFFFF',
				'floating_icon_color_mode'     => 'primary',
				'floating_icon_color'          => '#00053A',
				'floating_counter_background_enabled' => 'no',
				'floating_counter_background'  => '#C0A821',
				'floating_counter_text_color'  => '#FFFFFF',
				'floating_counter_position'    => 'center',
				'show_floating_desktop'        => 'yes',
				'show_floating_mobile'         => 'yes',
				'cart_z_index'                 => 999999,
				'panel_width'                  => 440,
				'floating_side'                => 'right',
				'overlay_opacity'              => 48,
				'auto_open_on_add'             => 'yes',
				'primary_color'                => '#00053A',
				'accent_color'                 => '#C0A821',
				'checkout_button_text'         => __( 'Finalizar compra', 'portus-cart-for-woocommerce' ),
				'show_cart_button'             => 'yes',
				'cart_button_text'             => __( 'Ver carrinho', 'portus-cart-for-woocommerce' ),
				'empty_button_text'            => __( 'Ver produtos', 'portus-cart-for-woocommerce' ),
				'empty_button_url'             => '',
				'show_low_stock_alerts'        => 'yes',
				'low_stock_threshold'          => 2,
				'delete_settings_on_uninstall' => 'no',
			);
		}

		/**
		 * Returns the local SVG icons available for the floating trigger.
		 *
		 * @return array<string,string>
		 */
		private static function get_floating_icon_options() {
			return array(
				'bag-fill' => __( 'Sacola atual', 'portus-cart-for-woocommerce' ),
				'cart'     => __( 'Carrinho clássico', 'portus-cart-for-woocommerce' ),
				'basket'   => __( 'Cesta', 'portus-cart-for-woocommerce' ),
				'bag'      => __( 'Sacola minimalista', 'portus-cart-for-woocommerce' ),
			);
		}

		/**
		 * Returns saved settings merged with defaults.
		 *
		 * @return array
		 */
		public static function get_settings() {
			$settings = get_option( self::OPTION_NAME, array() );

			if ( ! is_array( $settings ) ) {
				$settings = array();
			}

			return wp_parse_args( $settings, self::get_defaults() );
		}

		/**
		 * Gets a setting value.
		 *
		 * @param string $key Setting key.
		 * @return mixed
		 */
		public static function get( $key ) {
			$settings = self::get_settings();
			$defaults = self::get_defaults();

			return isset( $settings[ $key ] ) ? $settings[ $key ] : ( isset( $defaults[ $key ] ) ? $defaults[ $key ] : null );
		}

		/**
		 * Checks if a yes/no setting is enabled.
		 *
		 * @param string $key Setting key.
		 * @return bool
		 */
		public static function is_enabled( $key ) {
			return 'yes' === self::get( $key );
		}

		/**
		 * Returns a safe integer setting.
		 *
		 * @param string $key Setting key.
		 * @param int    $min Minimum value.
		 * @param int    $max Maximum value.
		 * @return int
		 */
		public static function get_int( $key, $min = 1, $max = 8 ) {
			$value = absint( self::get( $key ) );

			return max( $min, min( $max, $value ) );
		}

		/**
		 * Returns a safe CSS color setting.
		 *
		 * @param string $key Setting key.
		 * @return string
		 */
		public static function get_color( $key ) {
			$defaults = self::get_defaults();
			$color    = sanitize_hex_color( self::get( $key ) );

			if ( $color ) {
				return $color;
			}

			return isset( $defaults[ $key ] ) ? $defaults[ $key ] : '#00053A';
		}

		/**
		 * Converts a sanitized hexadecimal color to an RGB triplet.
		 *
		 * @param string $color Hexadecimal color.
		 * @return int[]
		 */
		private static function hex_to_rgb( $color ) {
			$hex = ltrim( (string) sanitize_hex_color( $color ), '#' );

			if ( 3 === strlen( $hex ) ) {
				$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
			}

			if ( 6 !== strlen( $hex ) ) {
				return array( 255, 255, 255 );
			}

			return array(
				hexdec( substr( $hex, 0, 2 ) ),
				hexdec( substr( $hex, 2, 2 ) ),
				hexdec( substr( $hex, 4, 2 ) ),
			);
		}

		/**
		 * Returns inline CSS variables for the frontend root.
		 *
		 * @return string
		 */
		public static function get_css_variables() {
			$primary                    = self::get_color( 'primary_color' );
			$accent                     = self::get_color( 'accent_color' );
			$cart_z_index               = self::get_int( 'cart_z_index', 1000, 2147483000 );
			$overlay_z_index            = max( 1, $cart_z_index - 1 );
			$panel_width                = self::get_int( 'panel_width', 320, 720 );
			$overlay_opacity            = self::get_int( 'overlay_opacity', 0, 90 ) / 100;
			$floating_button_size       = self::get_int( 'floating_button_size', 44, 80 );
			$floating_icon_size         = min( self::get_int( 'floating_icon_size', 16, 40 ), $floating_button_size - 8 );
			$floating_background_rgb    = self::hex_to_rgb( self::get_color( 'floating_background_color' ) );
			$floating_icon_color_mode   = self::get( 'floating_icon_color_mode' );
			$floating_icon_color        = 'custom' === $floating_icon_color_mode ? self::get_color( 'floating_icon_color' ) : $primary;
			$floating_counter_color     = self::get_color( 'floating_counter_text_color' );
			$floating_counter_background = self::is_enabled( 'floating_counter_background_enabled' ) ? self::get_color( 'floating_counter_background' ) : 'transparent';

			$variables = sprintf(
				'--msc-blue:%1$s;--msc-gold:%2$s;--msc-product-title:%1$s;--msc-panel-z-index:%3$d;--msc-overlay-z-index:%4$d;--msc-panel-width:%5$dpx;--msc-overlay-opacity:%6$s;--msc-floating-z-index:2147482900;',
				$primary,
				$accent,
				$cart_z_index,
				$overlay_z_index,
				$panel_width,
				rtrim( rtrim( number_format( $overlay_opacity, 2, '.', '' ), '0' ), '.' )
			);

			$variables .= sprintf(
				'--msc-floating-button-size:%1$dpx;--msc-floating-icon-size:%2$dpx;--msc-floating-background-rgb:%3$s;--msc-floating-icon-color:%4$s;--msc-floating-counter-background:%5$s;--msc-floating-counter-color:%6$s;',
				$floating_button_size,
				$floating_icon_size,
				implode( ',', $floating_background_rgb ),
				$floating_icon_color,
				$floating_counter_background,
				$floating_counter_color
			);

			return $variables;
		}

		/**
		 * Sanitizes all submitted settings.
		 *
		 * @param array $input Raw settings.
		 * @return array
		 */
		public static function sanitize_settings( $input ) {
			$input        = is_array( $input ) ? wp_unslash( $input ) : array();
			$current      = self::get_settings();
			$active_tab   = self::get_posted_active_tab();
			$tab_fields   = self::get_tab_fields();
			$target_keys  = isset( $tab_fields[ $active_tab ] ) ? $tab_fields[ $active_tab ] : array_keys( self::get_defaults() );
			$sanitized    = $current;
			$checkboxes   = self::get_checkbox_keys();

			foreach ( $target_keys as $key ) {
				if ( in_array( $key, $checkboxes, true ) ) {
					$sanitized[ $key ] = self::sanitize_checkbox_setting( $input, $key );
					continue;
				}

				$value = array_key_exists( $key, $input ) ? $input[ $key ] : ( isset( $current[ $key ] ) ? $current[ $key ] : null );
				$sanitized[ $key ] = self::sanitize_value_by_key( $key, $value );
			}

			$sanitized = self::normalize_floating_sizes( $sanitized );


			return wp_parse_args( $sanitized, self::get_defaults() );
		}

		/**
		 * Handles settings export as a JSON download.
		 */
		public static function handle_export() {
			self::verify_tool_request( 'meu_side_cart_export_settings' );

			$payload = array(
				'plugin'      => 'portus-cart-for-woocommerce',
				'version'     => MEU_SIDE_CART_VERSION,
				'exported_at' => current_time( 'mysql' ),
				'settings'    => self::get_export_settings(),
			);

			nocache_headers();
			header( 'Content-Type: application/json; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=portus-cart-settings-' . gmdate( 'Y-m-d-His' ) . '.json' );
			echo wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
			exit;
		}

		/**
		 * Handles support diagnostic export as a JSON download.
		 */
		public static function handle_diagnostics_export() {
			self::verify_tool_request( 'meu_side_cart_export_diagnostics' );

			nocache_headers();
			header( 'Content-Type: application/json; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=portus-cart-diagnostico-' . gmdate( 'Y-m-d-His' ) . '.json' );
			echo wp_json_encode( self::get_support_diagnostic_payload(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
			exit;
		}

		/**
		 * Handles JSON settings import.
		 */
		public static function handle_import() {
			self::verify_tool_request( 'meu_side_cart_import_settings' );

			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce and capability are verified by verify_tool_request().
			if ( empty( $_FILES['meu_side_cart_import_file'] ) || ! isset( $_FILES['meu_side_cart_import_file']['tmp_name'] ) ) {
				self::redirect_with_message( 'import_failed' );
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- File upload is validated through error checks and JSON decoding before import.
			$file = $_FILES['meu_side_cart_import_file'];

			if ( ! empty( $file['error'] ) || empty( $file['tmp_name'] ) ) {
				self::redirect_with_message( 'import_failed' );
			}

			$contents = file_get_contents( $file['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$decoded  = json_decode( $contents, true );

			if ( ! is_array( $decoded ) ) {
				self::redirect_with_message( 'import_failed' );
			}

			$settings       = isset( $decoded['settings'] ) && is_array( $decoded['settings'] ) ? $decoded['settings'] : $decoded;
			$next_settings  = self::sanitize_full_settings( $settings );

			update_option( self::OPTION_NAME, $next_settings );
			self::redirect_with_message( 'imported' );
		}

		/**
		 * Restores default settings.
		 */
		public static function handle_reset() {
			self::verify_tool_request( 'meu_side_cart_reset_settings' );

			update_option( self::OPTION_NAME, self::get_defaults() );
			self::redirect_with_message( 'reset' );
		}




		/**
		 * Renders the settings page.
		 */
		public static function render_page() {
			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				return;
			}

			$settings   = self::get_settings();
			$active_tab = self::get_active_tab();
			?>
			<div class="wrap portus-cart-for-woocommerce-admin">
				<div class="portus-cart-for-woocommerce-admin__brand">
					<img src="<?php echo esc_url( add_query_arg( 'ver', MEU_SIDE_CART_VERSION, MEU_SIDE_CART_URL . 'assets/img/portus-cart-icon.svg' ) ); ?>" alt="" width="48" height="48" />
					<div>
						<h1><?php esc_html_e( 'Portus Cart for WooCommerce', 'portus-cart-for-woocommerce' ); ?></h1>
						<p class="portus-cart-for-woocommerce-admin__intro">
							<?php esc_html_e( 'Configure a experiência do carrinho lateral, destaque benefícios de compra e acompanhe a saúde do plugin sem editar código.', 'portus-cart-for-woocommerce' ); ?>
						</p>
					</div>
				</div>

				<?php self::render_admin_styles(); ?>
				<?php settings_errors(); ?>
				<?php self::render_message(); ?>
				<?php self::render_admin_overview( $settings ); ?>
				<?php self::render_tabs( $active_tab ); ?>

				<?php if ( in_array( $active_tab, array( 'status', 'help', 'support', 'about' ), true ) ) : ?>
					<div class="portus-cart-for-woocommerce-admin__grid portus-cart-for-woocommerce-admin__grid-status">
						<?php self::render_active_tab_panel( $active_tab, $settings ); ?>
					</div>
				<?php else : ?>
					<form method="post" action="options.php">
						<?php settings_fields( 'meu_side_cart_settings_group' ); ?>
						<input type="hidden" name="meu_side_cart_active_tab" value="<?php echo esc_attr( $active_tab ); ?>" />

						<div class="portus-cart-for-woocommerce-admin__grid">
							<?php self::render_active_tab_panel( $active_tab, $settings ); ?>
						</div>

						<?php submit_button( __( 'Salvar configurações', 'portus-cart-for-woocommerce' ), 'primary large' ); ?>
					</form>

					<?php if ( 'advanced' === $active_tab ) : ?>
						<?php self::render_tools_panel(); ?>
					<?php else : ?>
						<?php self::render_preview( $settings ); ?>
					<?php endif; ?>
				<?php endif; ?>
			</div>
			<?php
		}

		/**
		 * Returns tabs available in the settings screen.
		 *
		 * @return array
		 */
		private static function get_tabs() {
			return array(
				'general'    => __( 'Carrinho', 'portus-cart-for-woocommerce' ),
				'appearance' => __( 'Visual', 'portus-cart-for-woocommerce' ),
				'buttons'    => __( 'Conversão', 'portus-cart-for-woocommerce' ),
				'stock'      => __( 'Estoque', 'portus-cart-for-woocommerce' ),
				'compat'     => __( 'Compatibilidade', 'portus-cart-for-woocommerce' ),
				'advanced'   => __( 'Ferramentas', 'portus-cart-for-woocommerce' ),
				'status'     => __( 'Saúde do plugin', 'portus-cart-for-woocommerce' ),
				'help'       => __( 'Guia', 'portus-cart-for-woocommerce' ),
				'support'    => __( 'Suporte', 'portus-cart-for-woocommerce' ),
				'about'      => __( 'Sobre / Portus', 'portus-cart-for-woocommerce' ),
			);
		}

		/**
		 * Renders a compact overview above the settings tabs.
		 *
		 * @param array $settings Current settings.
		 */
		private static function render_admin_overview( $settings ) {
			?>
			<section class="portus-cart-for-woocommerce-admin__overview" aria-label="<?php esc_attr_e( 'Resumo do Portus Cart', 'portus-cart-for-woocommerce' ); ?>">
				<div>
					<span><?php esc_html_e( 'Versão', 'portus-cart-for-woocommerce' ); ?></span>
					<strong><?php echo esc_html( MEU_SIDE_CART_VERSION ); ?></strong>
					<small><?php esc_html_e( 'Instalada neste site', 'portus-cart-for-woocommerce' ); ?></small>
				</div>
				<div>
					<span><?php esc_html_e( 'Botão flutuante', 'portus-cart-for-woocommerce' ); ?></span>
					<strong><?php echo esc_html( self::is_enabled( 'enabled_floating_button' ) ? __( 'Ativo', 'portus-cart-for-woocommerce' ) : __( 'Desativado', 'portus-cart-for-woocommerce' ) ); ?></strong>
					<small><?php esc_html_e( 'Controle rápido para abrir o carrinho em qualquer página', 'portus-cart-for-woocommerce' ); ?></small>
				</div>
				<div>
					<span><?php esc_html_e( 'Tema', 'portus-cart-for-woocommerce' ); ?></span>
					<strong><?php echo esc_html( wp_get_theme()->get( 'Name' ) ); ?></strong>
					<small><?php esc_html_e( 'Ajustes de camada e posição ficam em Compatibilidade', 'portus-cart-for-woocommerce' ); ?></small>
				</div>
			</section>
			<?php
		}

		/**
		 * Returns fields saved by each tab.
		 *
		 * @return array
		 */
		private static function get_tab_fields() {
			return array(
				'general'    => array( 'cart_title', 'enabled_floating_button', 'hide_floating_on_product', 'floating_bottom_offset' ),
				'appearance' => array( 'primary_color', 'accent_color', 'floating_icon', 'floating_button_size', 'floating_icon_size', 'floating_shape', 'floating_background_color', 'floating_icon_color_mode', 'floating_icon_color', 'floating_counter_background_enabled', 'floating_counter_background', 'floating_counter_text_color', 'floating_counter_position', 'show_floating_desktop', 'show_floating_mobile' ),
				'buttons'    => array( 'checkout_button_text', 'show_cart_button', 'cart_button_text', 'empty_button_text', 'empty_button_url' ),
				'stock'      => array( 'show_low_stock_alerts', 'low_stock_threshold' ),
				'advanced'   => array( 'delete_settings_on_uninstall' ),
				'status'     => array(),
				'help'       => array(),
				'support'    => array(),
				'about'      => array(),
			);
		}

		/**
		 * Returns checkbox setting keys.
		 *
		 * @return string[]
		 */
		private static function get_checkbox_keys() {
			return array(
				'enabled_floating_button',
				'hide_floating_on_product',
				'floating_counter_background_enabled',
				'show_floating_desktop',
				'show_floating_mobile',
				'auto_open_on_add',
				'show_cart_button',
				'show_low_stock_alerts',
				'delete_settings_on_uninstall',
			);
		}

		/**
		 * Gets active tab from the URL.
		 *
		 * @return string
		 */
		private static function get_active_tab() {
			$tabs = self::get_tabs();
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only tab routing value.
			$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general';

			return isset( $tabs[ $tab ] ) ? $tab : 'general';
		}

		/**
		 * Gets posted active tab from the settings form.
		 *
		 * @return string
		 */
		private static function get_posted_active_tab() {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Settings form nonce is handled by WordPress settings_fields().
			$tab = isset( $_POST['meu_side_cart_active_tab'] ) ? sanitize_key( wp_unslash( $_POST['meu_side_cart_active_tab'] ) ) : '';

			return $tab;
		}

		/**
		 * Renders tab navigation.
		 *
		 * @param string $active_tab Active tab key.
		 */
		private static function render_tabs( $active_tab ) {
			?>
			<nav class="portus-cart-for-woocommerce-admin__tabs" aria-label="<?php esc_attr_e( 'Configurações do Portus Cart', 'portus-cart-for-woocommerce' ); ?>">
				<?php foreach ( self::get_tabs() as $tab_key => $tab_label ) : ?>
					<a class="portus-cart-for-woocommerce-admin__tab <?php echo $active_tab === $tab_key ? 'is-active' : ''; ?>" href="<?php echo esc_url( self::get_page_url( array( 'tab' => $tab_key ) ) ); ?>">
						<?php echo esc_html( $tab_label ); ?>
					</a>
				<?php endforeach; ?>
			</nav>
			<?php
		}

		/**
		 * Renders a settings tab.
		 *
		 * @param string $active_tab Active tab key.
		 * @param array  $settings Current settings.
		 */
		private static function render_active_tab_panel( $active_tab, $settings ) {
			switch ( $active_tab ) {
				case 'appearance':
					self::render_appearance_tab( $settings );
					break;
				case 'buttons':
					self::render_buttons_tab( $settings );
					break;
				case 'stock':
					self::render_stock_tab( $settings );
					break;
				case 'compat':
					self::render_compatibility_tab( $settings );
					break;
				case 'advanced':
					self::render_advanced_tab( $settings );
					break;
				case 'status':
					self::render_status_tab();
					break;
				case 'help':
					self::render_help_tab();
					break;
				case 'support':
					self::render_support_tab();
					break;
				case 'about':
					self::render_about_tab();
					break;
				case 'general':
				default:
					self::render_general_tab( $settings );
					break;
			}
		}

		/**
		 * Renders the general tab.
		 *
		 * @param array $settings Current settings.
		 */
		private static function render_general_tab( $settings ) {
			?>
			<section class="portus-cart-for-woocommerce-admin__card">
				<h2><?php esc_html_e( 'Experiência do carrinho', 'portus-cart-for-woocommerce' ); ?></h2>
				<?php
				self::render_text_input( 'cart_title', __( 'Nome exibido no cabeçalho', 'portus-cart-for-woocommerce' ), $settings['cart_title'] );
				self::render_checkbox_input( 'enabled_floating_button', __( 'Mostrar botão flutuante do carrinho', 'portus-cart-for-woocommerce' ), $settings['enabled_floating_button'] );
				self::render_checkbox_input( 'hide_floating_on_product', __( 'Ocultar botão flutuante na página de produto', 'portus-cart-for-woocommerce' ), $settings['hide_floating_on_product'] );
				self::render_number_input( 'floating_bottom_offset', __( 'Altura do botão em relação ao rodapé (px)', 'portus-cart-for-woocommerce' ), $settings['floating_bottom_offset'], 70, 240 );
				?>
				<p class="description"><?php esc_html_e( 'Por padrão, o botão flutuante não aparece no carrinho, checkout e página de pedido recebido para evitar distrações na finalização da compra.', 'portus-cart-for-woocommerce' ); ?></p>
			</section>
			<?php
		}

		/**
		 * Renders the appearance tab.
		 *
		 * @param array $settings Current settings.
		 */
		private static function render_appearance_tab( $settings ) {
			?>
			<section class="portus-cart-for-woocommerce-admin__card">
				<h2><?php esc_html_e( 'Identidade visual', 'portus-cart-for-woocommerce' ); ?></h2>
				<div class="portus-cart-for-woocommerce-admin__colors">
					<?php
					self::render_color_input( 'primary_color', __( 'Cor principal da marca', 'portus-cart-for-woocommerce' ), $settings['primary_color'] );
					self::render_color_input( 'accent_color', __( 'Cor de destaque', 'portus-cart-for-woocommerce' ), $settings['accent_color'] );
					?>
				</div>
				<p class="description"><?php esc_html_e( 'Essas cores aparecem no carrinho, no botão flutuante e no preview do admin.', 'portus-cart-for-woocommerce' ); ?></p>
			</section>

			<section class="portus-cart-for-woocommerce-admin__card">
				<h2><?php esc_html_e( 'Botão flutuante', 'portus-cart-for-woocommerce' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Personalize o atalho que abre o carrinho. Os ícones são SVGs locais do Bootstrap Icons.', 'portus-cart-for-woocommerce' ); ?></p>

				<?php self::render_floating_icon_picker( $settings['floating_icon'] ); ?>

				<div class="portus-cart-for-woocommerce-admin__field-grid">
					<?php
					self::render_range_input( 'floating_button_size', __( 'Tamanho do botão', 'portus-cart-for-woocommerce' ), $settings['floating_button_size'], 44, 80, 2, 'px' );
					self::render_range_input( 'floating_icon_size', __( 'Tamanho do ícone', 'portus-cart-for-woocommerce' ), $settings['floating_icon_size'], 16, 40, 1, 'px' );
					self::render_select_input(
						'floating_shape',
						__( 'Formato do botão', 'portus-cart-for-woocommerce' ),
						$settings['floating_shape'],
						array(
							'circle' => __( 'Circular', 'portus-cart-for-woocommerce' ),
							'rounded' => __( 'Quadrado arredondado', 'portus-cart-for-woocommerce' ),
						)
					);
					self::render_select_input(
						'floating_icon_color_mode',
						__( 'Cor do ícone', 'portus-cart-for-woocommerce' ),
						$settings['floating_icon_color_mode'],
						array(
							'primary' => __( 'Usar cor principal', 'portus-cart-for-woocommerce' ),
							'custom'  => __( 'Usar cor personalizada', 'portus-cart-for-woocommerce' ),
						)
					);
					?>
				</div>

				<div class="portus-cart-for-woocommerce-admin__colors portus-cart-for-woocommerce-admin__floating-colors">
					<?php
					self::render_color_input( 'floating_background_color', __( 'Fundo do botão', 'portus-cart-for-woocommerce' ), $settings['floating_background_color'] );
					self::render_color_input( 'floating_icon_color', __( 'Cor personalizada do ícone', 'portus-cart-for-woocommerce' ), $settings['floating_icon_color'] );
					self::render_color_input( 'floating_counter_background', __( 'Fundo do contador', 'portus-cart-for-woocommerce' ), $settings['floating_counter_background'] );
					self::render_color_input( 'floating_counter_text_color', __( 'Número do contador', 'portus-cart-for-woocommerce' ), $settings['floating_counter_text_color'] );
					?>
				</div>

				<?php self::render_checkbox_input( 'floating_counter_background_enabled', __( 'Mostrar fundo colorido no contador', 'portus-cart-for-woocommerce' ), $settings['floating_counter_background_enabled'] ); ?>
				<?php
				self::render_select_input(
					'floating_counter_position',
					__( 'Posição do contador', 'portus-cart-for-woocommerce' ),
					$settings['floating_counter_position'],
					array(
						'center'    => __( 'Sobre o ícone (padrão atual)', 'portus-cart-for-woocommerce' ),
						'top-right' => __( 'Canto superior direito', 'portus-cart-for-woocommerce' ),
						'top-left'  => __( 'Canto superior esquerdo', 'portus-cart-for-woocommerce' ),
					)
				);
				?>

				<div class="portus-cart-for-woocommerce-admin__visibility-options">
					<?php self::render_checkbox_input( 'show_floating_desktop', __( 'Mostrar em computadores', 'portus-cart-for-woocommerce' ), $settings['show_floating_desktop'] ); ?>
					<?php self::render_checkbox_input( 'show_floating_mobile', __( 'Mostrar em celulares e tablets', 'portus-cart-for-woocommerce' ), $settings['show_floating_mobile'] ); ?>
				</div>
				<p class="description"><?php esc_html_e( 'Lado e distância inferior continuam disponíveis nas abas Carrinho e Compatibilidade.', 'portus-cart-for-woocommerce' ); ?></p>
			</section>
			<?php
		}

		/**
		 * Renders the buttons tab.
		 *
		 * @param array $settings Current settings.
		 */
		private static function render_buttons_tab( $settings ) {
			?>
			<section class="portus-cart-for-woocommerce-admin__card">
				<h2><?php esc_html_e( 'Ações de compra', 'portus-cart-for-woocommerce' ); ?></h2>
				<?php
				self::render_text_input( 'checkout_button_text', __( 'Chamada principal para checkout', 'portus-cart-for-woocommerce' ), $settings['checkout_button_text'] );
				self::render_checkbox_input( 'show_cart_button', __( 'Mostrar botão Ver carrinho', 'portus-cart-for-woocommerce' ), $settings['show_cart_button'] );
				self::render_text_input( 'cart_button_text', __( 'Chamada para ver o carrinho completo', 'portus-cart-for-woocommerce' ), $settings['cart_button_text'] );
				self::render_text_input( 'empty_button_text', __( 'Chamada quando o carrinho está vazio', 'portus-cart-for-woocommerce' ), $settings['empty_button_text'] );
				self::render_text_input( 'empty_button_url', __( 'URL do botão quando o carrinho está vazio', 'portus-cart-for-woocommerce' ), $settings['empty_button_url'] );
				?>
			</section>
			<?php
		}


		/**
		 * Renders the stock tab.
		 *
		 * @param array $settings Current settings.
		 */
		private static function render_stock_tab( $settings ) {
			?>
			<section class="portus-cart-for-woocommerce-admin__card">
				<h2><?php esc_html_e( 'Urgência e limite de estoque', 'portus-cart-for-woocommerce' ); ?></h2>
				<?php
				self::render_checkbox_input( 'show_low_stock_alerts', __( 'Mostrar aviso de estoque baixo', 'portus-cart-for-woocommerce' ), $settings['show_low_stock_alerts'] );
				self::render_number_input( 'low_stock_threshold', __( 'Avisar quando o estoque chegar a', 'portus-cart-for-woocommerce' ), $settings['low_stock_threshold'], 1, 10 );
				?>
			</section>
			<?php
		}



		/**
		 * Renders theme compatibility settings.
		 *
		 * @param array $settings Current settings.
		 */
		private static function render_compatibility_tab( $settings ) {
			?>
			<section class="portus-cart-for-woocommerce-admin__card portus-cart-for-woocommerce-admin__compat-card">
				<h2><?php esc_html_e( 'Compatibilidade com temas', 'portus-cart-for-woocommerce' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Use estes ajustes quando o tema ou outro plugin disputar espaço com o carrinho, como botão de topo, WhatsApp, popups e headers fixos.', 'portus-cart-for-woocommerce' ); ?></p>

				<div class="portus-cart-for-woocommerce-admin__compat-summary">
					<div>
						<span><?php esc_html_e( 'Camada', 'portus-cart-for-woocommerce' ); ?></span>
						<strong data-aurea-range-value="cart_z_index"><?php echo esc_html( absint( $settings['cart_z_index'] ) ); ?></strong>
					</div>
					<div>
						<span><?php esc_html_e( 'Painel', 'portus-cart-for-woocommerce' ); ?></span>
						<strong><b data-aurea-range-value="panel_width"><?php echo esc_html( absint( $settings['panel_width'] ) ); ?></b>px</strong>
					</div>
					<div>
						<span><?php esc_html_e( 'Fundo', 'portus-cart-for-woocommerce' ); ?></span>
						<strong><b data-aurea-range-value="overlay_opacity"><?php echo esc_html( absint( $settings['overlay_opacity'] ) ); ?></b>%</strong>
					</div>
				</div>

				<h3><?php esc_html_e( 'Comportamento', 'portus-cart-for-woocommerce' ); ?></h3>
				<?php self::render_checkbox_input( 'auto_open_on_add', __( 'Abrir carrinho automaticamente ao adicionar produto', 'portus-cart-for-woocommerce' ), $settings['auto_open_on_add'] ); ?>
				<?php self::render_checkbox_input( 'enabled_floating_button', __( 'Mostrar botão flutuante do carrinho', 'portus-cart-for-woocommerce' ), $settings['enabled_floating_button'] ); ?>

				<h3><?php esc_html_e( 'Camada e posição', 'portus-cart-for-woocommerce' ); ?></h3>
				<?php self::render_number_input( 'cart_z_index', __( 'Camada do carrinho sobre o tema', 'portus-cart-for-woocommerce' ), $settings['cart_z_index'], 1000, 2147483000 ); ?>
				<?php self::render_range_input( 'panel_width', __( 'Largura do painel', 'portus-cart-for-woocommerce' ), $settings['panel_width'], 320, 720, 10, 'px' ); ?>
				<?php self::render_range_input( 'overlay_opacity', __( 'Escurecimento do fundo', 'portus-cart-for-woocommerce' ), $settings['overlay_opacity'], 0, 90, 1, '%' ); ?>
				<?php
				self::render_select_input(
					'floating_side',
					__( 'Lado do botão flutuante', 'portus-cart-for-woocommerce' ),
					$settings['floating_side'],
					array(
						'right' => __( 'Direita', 'portus-cart-for-woocommerce' ),
						'left'  => __( 'Esquerda', 'portus-cart-for-woocommerce' ),
					)
				);
				?>
				<p class="description"><?php esc_html_e( 'Use essas opções somente quando algum tema, botão de WhatsApp, botão de topo ou plugin visual entrar em conflito com o carrinho.', 'portus-cart-for-woocommerce' ); ?></p>
			</section>
			<?php
		}


		/**
		 * Renders the advanced tab.
		 *
		 * @param array $settings Current settings.
		 */
		private static function render_advanced_tab( $settings ) {
			?>
			<section class="portus-cart-for-woocommerce-admin__card">
				<h2><?php esc_html_e( 'Manutenção e limpeza', 'portus-cart-for-woocommerce' ); ?></h2>
				<?php self::render_checkbox_input( 'delete_settings_on_uninstall', __( 'Apagar configurações quando o plugin for desinstalado', 'portus-cart-for-woocommerce' ), $settings['delete_settings_on_uninstall'] ); ?>
				<p class="description"><?php esc_html_e( 'Use essa opção somente quando quiser remover todos os ajustes salvos ao excluir o plugin pelo WordPress.', 'portus-cart-for-woocommerce' ); ?></p>
			</section>
			<?php
		}

		/**
		 * Renders the read-only status tab.
		 */
		private static function render_status_tab() {
			$status_groups = self::get_status_groups();
			$counts        = self::get_status_counts( $status_groups );
			require MEU_SIDE_CART_PATH . 'includes/admin/health.php';
		}

		/**
		 * Renders the help and documentation tab.
		 */
		private static function render_help_tab() {
			require MEU_SIDE_CART_PATH . 'includes/admin/help.php';
		}

		/**
		 * Renders the support tab.
		 */
		private static function render_support_tab() {
			require MEU_SIDE_CART_PATH . 'includes/admin/support.php';
		}

		/**
		 * Renders the Portus brand tab.
		 */
		private static function render_about_tab() {
			$logo_url = add_query_arg( 'ver', MEU_SIDE_CART_VERSION, MEU_SIDE_CART_URL . 'assets/img/portus-cart-logo-icon.svg' );
			require MEU_SIDE_CART_PATH . 'includes/admin/about.php';
		}

		/**
		 * Returns help rows describing each tab.
		 *
		 * @return array
		 */
		private static function get_help_tab_rows() {
			return array(
				array(
					'title'       => __( 'Carrinho', 'portus-cart-for-woocommerce' ),
					'description' => __( 'Cabeçalho do carrinho, botão flutuante e altura do botão.', 'portus-cart-for-woocommerce' ),
				),
				array(
					'title'       => __( 'Visual', 'portus-cart-for-woocommerce' ),
					'description' => __( 'Cores da marca usadas no carrinho, botão e preview.', 'portus-cart-for-woocommerce' ),
				),				array(
					'title'       => __( 'Estoque', 'portus-cart-for-woocommerce' ),
					'description' => __( 'Aviso de estoque baixo e limite para exibir alerta ao cliente.', 'portus-cart-for-woocommerce' ),
				),				array(
					'title'       => __( 'Recomendações', 'portus-cart-for-woocommerce' ),
					'description' => __( 'Título e quantidade de recomendações exibidas dentro do carrinho.', 'portus-cart-for-woocommerce' ),
				),
				array(
					'title'       => __( 'Compatibilidade', 'portus-cart-for-woocommerce' ),
					'description' => __( 'Controles para camada visual, largura, escurecimento do fundo, botão flutuante, autoabertura e integração com tema.', 'portus-cart-for-woocommerce' ),
				),				array(
					'title'       => __( 'Ferramentas', 'portus-cart-for-woocommerce' ),
					'description' => __( 'Exportação, importação, restauração e limpeza ao desinstalar.', 'portus-cart-for-woocommerce' ),
				),
				array(
					'title'       => __( 'Saúde do plugin', 'portus-cart-for-woocommerce' ),
					'description' => __( 'Diagnóstico do ambiente, WooCommerce, carrinho dinâmico, tema e compatibilidade.', 'portus-cart-for-woocommerce' ),
				),
				array(
					'title'       => __( 'Suporte', 'portus-cart-for-woocommerce' ),
					'description' => __( 'Diagnóstico exportável, checklist de atendimento e links úteis para manutenção.', 'portus-cart-for-woocommerce' ),
				),
			);
		}

		/**
		 * Returns the testing checklist.
		 *
		 * @return string[]
		 */
		private static function get_help_checklist() {
			return array(
				__( 'Adicionar produto simples ao carrinho.', 'portus-cart-for-woocommerce' ),
				__( 'Adicionar produto variável ao carrinho.', 'portus-cart-for-woocommerce' ),
				__( 'Aumentar e diminuir quantidade respeitando estoque.', 'portus-cart-for-woocommerce' ),
				__( 'Remover produto do carrinho.', 'portus-cart-for-woocommerce' ),
				__( 'Aplicar e remover cupom.', 'portus-cart-for-woocommerce' ),
				__( 'Favoritar e desfavoritar produto no carrinho.', 'portus-cart-for-woocommerce' ),
				__( 'Testar o botão flutuante com contador.', 'portus-cart-for-woocommerce' ),
				__( 'Testar botão Finalizar compra até o checkout.', 'portus-cart-for-woocommerce' ),
				__( 'Testar layout no celular e no desktop.', 'portus-cart-for-woocommerce' ),
			);
		}

		/**
		 * Returns the support checklist.
		 *
		 * @return string[]
		 */
		private static function get_support_checklist() {
			return array(
				__( 'Atualizar o plugin para a versão mais recente disponível.', 'portus-cart-for-woocommerce' ),
				__( 'Limpar cache do navegador, WordPress, Hostinger e plugins de performance.', 'portus-cart-for-woocommerce' ),
				__( 'Testar em janela anônima para descartar cache de sessão.', 'portus-cart-for-woocommerce' ),
				__( 'Conferir a aba Saúde do plugin e resolver alertas críticos.', 'portus-cart-for-woocommerce' ),
				__( 'Repetir o teste com produto simples e produto variável.', 'portus-cart-for-woocommerce' ),
				__( 'Exportar o diagnóstico antes de enviar o chamado.', 'portus-cart-for-woocommerce' ),
			);
		}

		/**
		 * Returns troubleshooting FAQ items.
		 *
		 * @return array<int,array<string,string>>
		 */
		private static function get_help_faq() {
			return array(
				array(
					'question' => __( 'O carrinho não abre após adicionar produto.', 'portus-cart-for-woocommerce' ),
					'answer'   => __( 'Confira se o WooCommerce está ativo e se o tema mantém os eventos AJAX padrão de adicionar ao carrinho.', 'portus-cart-for-woocommerce' ),
				),
				array(
					'question' => __( 'O botão flutuante ficou sobre outro botão.', 'portus-cart-for-woocommerce' ),
					'answer'   => __( 'Ajuste a altura, posição lateral e z-index na aba Compatibilidade.', 'portus-cart-for-woocommerce' ),
				),
				array(
					'question' => __( 'Os estilos não aparecem corretamente.', 'portus-cart-for-woocommerce' ),
					'answer'   => __( 'Limpe cache do navegador, cache do WordPress e cache do servidor depois de alterar configurações visuais.', 'portus-cart-for-woocommerce' ),
				),
			);
		}

		/**
		 * Returns grouped status checks for the plugin admin screen.
		 *
		 * @return array
		 */
		private static function get_status_groups() {
			$woocommerce_active = class_exists( 'WooCommerce' );
			$checkout_page_id   = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'checkout' ) : 0;
			$cart_page_id       = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'cart' ) : 0;
			$theme              = wp_get_theme();
			$theme_name         = $theme->exists() ? $theme->get( 'Name' ) : __( 'Não detectado', 'portus-cart-for-woocommerce' );
			$ajax_ready         = self::are_ajax_actions_registered();
			$settings           = self::get_settings();
			$plugin_instance    = function_exists( 'meu_side_cart_plugin' ) ? meu_side_cart_plugin() : null;
			$wp_footer_ready    = $plugin_instance ? (bool) has_action( 'wp_footer', array( $plugin_instance, 'render' ) ) : false;
			$enqueue_ready      = $plugin_instance ? (bool) has_action( 'wp_enqueue_scripts', array( $plugin_instance, 'enqueue_assets' ) ) : false;
			$fragments_ready    = class_exists( 'Meu_Side_Cart_Cart' ) && (bool) has_filter( 'woocommerce_add_to_cart_fragments', array( 'Meu_Side_Cart_Cart', 'add_fragments' ) );

			return array(
				array(
					'title'       => __( 'Base do WordPress', 'portus-cart-for-woocommerce' ),
					'description' => __( 'Requisitos mínimos para o plugin carregar com segurança.', 'portus-cart-for-woocommerce' ),
					'items'       => array(
						array(
							'label'   => __( 'WordPress', 'portus-cart-for-woocommerce' ),
							'status'  => version_compare( get_bloginfo( 'version' ), '6.0', '>=' ) ? 'good' : 'warning',
							'value'   => get_bloginfo( 'version' ),
							'message' => __( 'Requer WordPress 6.0 ou superior.', 'portus-cart-for-woocommerce' ),
						),
						array(
							'label'   => __( 'PHP', 'portus-cart-for-woocommerce' ),
							'status'  => version_compare( PHP_VERSION, '7.4', '>=' ) ? 'good' : 'warning',
							'value'   => PHP_VERSION,
							'message' => __( 'Requer PHP 7.4 ou superior.', 'portus-cart-for-woocommerce' ),
						),
						array(
							'label'   => __( 'Arquivos do plugin', 'portus-cart-for-woocommerce' ),
							'status'  => self::are_plugin_files_present() ? 'good' : 'warning',
							'value'   => dirname( MEU_SIDE_CART_BASENAME ),
							'message' => __( 'Confere os arquivos principais, CSS e JavaScript.', 'portus-cart-for-woocommerce' ),
						),
					),
				),
				array(
					'title'       => __( 'WooCommerce', 'portus-cart-for-woocommerce' ),
					'description' => __( 'Integrações essenciais para carrinho, checkout e cupons e respostas dinâmicas.', 'portus-cart-for-woocommerce' ),
					'items'       => array(
						array(
							'label'   => __( 'WooCommerce ativo', 'portus-cart-for-woocommerce' ),
							'status'  => $woocommerce_active ? 'good' : 'warning',
							'value'   => $woocommerce_active ? self::get_woocommerce_version() : __( 'Inativo', 'portus-cart-for-woocommerce' ),
							'message' => __( 'O Portus Cart depende do WooCommerce ativo.', 'portus-cart-for-woocommerce' ),
						),
						array(
							'label'   => __( 'Checkout configurado', 'portus-cart-for-woocommerce' ),
							'status'  => $checkout_page_id > 0 ? 'good' : 'warning',
							'value'   => $checkout_page_id > 0 ? get_the_title( $checkout_page_id ) : __( 'Não encontrado', 'portus-cart-for-woocommerce' ),
							'message' => __( 'Necessário para o botão Finalizar compra.', 'portus-cart-for-woocommerce' ),
						),
						array(
							'label'   => __( 'Página do carrinho', 'portus-cart-for-woocommerce' ),
							'status'  => $cart_page_id > 0 ? 'good' : 'warning',
							'value'   => $cart_page_id > 0 ? get_the_title( $cart_page_id ) : __( 'Não encontrada', 'portus-cart-for-woocommerce' ),
							'message' => __( 'Usada pelo botão Ver carrinho quando ele está ativo.', 'portus-cart-for-woocommerce' ),
						),
						array(
							'label'   => __( 'Cupons', 'portus-cart-for-woocommerce' ),
							'status'  => function_exists( 'wc_coupons_enabled' ) && wc_coupons_enabled() ? 'good' : 'info',
							'value'   => function_exists( 'wc_coupons_enabled' ) && wc_coupons_enabled() ? __( 'Ativos', 'portus-cart-for-woocommerce' ) : __( 'Desativados', 'portus-cart-for-woocommerce' ),
							'message' => __( 'Quando os cupons estão desativados no WooCommerce, o campo de cupom fica oculto.', 'portus-cart-for-woocommerce' ),
						),
						array(
							'label'   => __( 'HPOS', 'portus-cart-for-woocommerce' ),
							'status'  => class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ? 'good' : 'info',
							'value'   => __( 'Compatibilidade declarada', 'portus-cart-for-woocommerce' ),
							'message' => __( 'O plugin declara compatibilidade com a estrutura moderna de pedidos do WooCommerce.', 'portus-cart-for-woocommerce' ),
						),
					),
				),				array(
					'title'       => __( 'Carrinho dinâmico', 'portus-cart-for-woocommerce' ),
					'description' => __( 'Pontos que mantêm o carrinho dinâmico sem recarregar a página.', 'portus-cart-for-woocommerce' ),
					'items'       => array(
						array(
							'label'   => __( 'Ações do carrinho', 'portus-cart-for-woocommerce' ),
							'status'  => $ajax_ready ? 'good' : 'warning',
							'value'   => $ajax_ready ? __( 'Registradas', 'portus-cart-for-woocommerce' ) : __( 'Incompletas', 'portus-cart-for-woocommerce' ),
							'message' => __( 'Remove, atualiza quantidade e cupom por rotas seguras do WordPress.', 'portus-cart-for-woocommerce' ),
						),
						array(
							'label'   => __( 'Fragmentos WooCommerce', 'portus-cart-for-woocommerce' ),
							'status'  => $fragments_ready ? 'good' : 'warning',
							'value'   => $fragments_ready ? __( 'Ativos', 'portus-cart-for-woocommerce' ) : __( 'Não registrados', 'portus-cart-for-woocommerce' ),
							'message' => __( 'Mantém contador e conteúdo do carrinho sincronizados com o WooCommerce.', 'portus-cart-for-woocommerce' ),
						),
						array(
							'label'   => __( 'Assets front-end', 'portus-cart-for-woocommerce' ),
							'status'  => $enqueue_ready ? 'good' : 'warning',
							'value'   => $enqueue_ready ? __( 'Registrados', 'portus-cart-for-woocommerce' ) : __( 'Não registrados', 'portus-cart-for-woocommerce' ),
							'message' => __( 'CSS e JS são carregados pelo hook padrão wp_enqueue_scripts.', 'portus-cart-for-woocommerce' ),
						),
					),
				),			);
		}

		/**
		 * Renders one status row.
		 *
		 * @param array $item Status item.
		 */
		private static function render_status_item( $item ) {
			$status = isset( $item['status'] ) ? $item['status'] : 'info';
			?>
			<div class="portus-cart-for-woocommerce-admin__status-item portus-cart-for-woocommerce-admin__status-item-<?php echo esc_attr( sanitize_html_class( $status ) ); ?>">
				<div>
					<strong><?php echo esc_html( $item['label'] ); ?></strong>
					<small><?php echo esc_html( $item['message'] ); ?></small>
				</div>
				<span class="portus-cart-for-woocommerce-admin__status-value"><?php echo esc_html( $item['value'] ); ?></span>
				<?php self::render_status_badge( $status ); ?>
			</div>
			<?php
		}

		/**
		 * Renders a small status badge.
		 *
		 * @param string $status Status key.
		 */
		private static function render_status_badge( $status ) {
			$labels = array(
				'good'    => __( 'OK', 'portus-cart-for-woocommerce' ),
				'warning' => __( 'Atenção', 'portus-cart-for-woocommerce' ),
				'info'    => __( 'Info', 'portus-cart-for-woocommerce' ),
			);
			?>
			<em class="portus-cart-for-woocommerce-admin__status-badge portus-cart-for-woocommerce-admin__status-badge-<?php echo esc_attr( sanitize_html_class( $status ) ); ?>">
				<?php echo esc_html( isset( $labels[ $status ] ) ? $labels[ $status ] : $labels['info'] ); ?>
			</em>
			<?php
		}

		/**
		 * Counts status items by status type.
		 *
		 * @param array $groups Status groups.
		 * @return array
		 */
		private static function get_status_counts( $groups ) {
			$counts = array(
				'good'    => 0,
				'warning' => 0,
				'info'    => 0,
			);

			foreach ( $groups as $group ) {
				foreach ( $group['items'] as $item ) {
					$status = isset( $item['status'] ) ? $item['status'] : 'info';

					if ( ! isset( $counts[ $status ] ) ) {
						$status = 'info';
					}

					$counts[ $status ]++;
				}
			}

			return $counts;
		}

		/**
		 * Checks if all plugin files expected by this version are present.
		 *
		 * @return bool
		 */
		private static function are_plugin_files_present() {
			$files = array(
				'portus-cart-for-woocommerce.php',
				'plugin-v3.php',
				'assets/css/style.css',
				'assets/js/script.js',
				'includes/cart.php',
				'includes/ajax.php',
				'includes/coupon.php',
				'includes/settings.php',
			);

			foreach ( $files as $file ) {
				if ( ! file_exists( MEU_SIDE_CART_PATH . $file ) ) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Checks if public and logged-in AJAX actions are registered.
		 *
		 * @return bool
		 */
		private static function are_ajax_actions_registered() {
			$actions = array(
				'refresh',
				'remove_item',
				'update_quantity',
				'apply_coupon',
				'remove_coupon',
			);

			foreach ( $actions as $action ) {
				if ( ! has_action( 'wp_ajax_meu_side_cart_' . $action ) || ! has_action( 'wp_ajax_nopriv_meu_side_cart_' . $action ) ) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Gets the active WooCommerce version.
		 *
		 * @return string
		 */
		private static function get_woocommerce_version() {
			if ( defined( 'WC_VERSION' ) ) {
				return WC_VERSION;
			}

			if ( isset( $GLOBALS['woocommerce'] ) && is_object( $GLOBALS['woocommerce'] ) && isset( $GLOBALS['woocommerce']->version ) ) {
				return $GLOBALS['woocommerce']->version;
			}

			return __( 'Detectado', 'portus-cart-for-woocommerce' );
		}


		/**
		 * Returns support diagnostic data without license keys or secrets.
		 *
		 * @return array
		 */
		private static function get_support_diagnostic_payload() {
			return array(
				'plugin'           => 'portus-cart-for-woocommerce',
				'version'          => MEU_SIDE_CART_VERSION,
				'generated_at'     => current_time( 'mysql' ),
				'site_url'         => home_url(),
				'environment'      => self::get_environment_rows(),
				'settings_summary' => self::get_support_settings_summary(),
				'status'           => self::get_status_groups(),
			);
		}

		/**
		 * Returns non-sensitive settings for support.
		 *
		 * @return array
		 */
		private static function get_support_settings_summary() {
			$settings = self::get_settings();

			return array(
				'cart_title'                 => isset( $settings['cart_title'] ) ? $settings['cart_title'] : '',
				'floating_button'            => self::is_enabled( 'enabled_floating_button' ) ? 'enabled' : 'disabled',
				'hide_floating_on_product'   => self::is_enabled( 'hide_floating_on_product' ) ? 'yes' : 'no',
				'auto_open_on_add'           => self::is_enabled( 'auto_open_on_add' ) ? 'enabled' : 'disabled',
				'panel_width'                => isset( $settings['panel_width'] ) ? absint( $settings['panel_width'] ) : '',
				'cart_z_index'               => isset( $settings['cart_z_index'] ) ? absint( $settings['cart_z_index'] ) : '',
				'overlay_opacity'            => isset( $settings['overlay_opacity'] ) ? absint( $settings['overlay_opacity'] ) : '',
				'show_low_stock_alerts'      => self::is_enabled( 'show_low_stock_alerts' ) ? 'yes' : 'no',
			);
		}

		/**
		 * Returns environment rows for support.
		 *
		 * @return array
		 */
		private static function get_environment_rows() {
			$theme        = wp_get_theme();
			$parent_theme = $theme->parent();

			return array(
				__( 'Plugin', 'portus-cart-for-woocommerce' )       => 'Portus Cart for WooCommerce ' . MEU_SIDE_CART_VERSION,
				__( 'Pasta', 'portus-cart-for-woocommerce' )        => dirname( MEU_SIDE_CART_BASENAME ),
				__( 'Text domain', 'portus-cart-for-woocommerce' )  => 'portus-cart-for-woocommerce',
				__( 'WordPress', 'portus-cart-for-woocommerce' )    => get_bloginfo( 'version' ),
				__( 'WooCommerce', 'portus-cart-for-woocommerce' )  => class_exists( 'WooCommerce' ) ? self::get_woocommerce_version() : __( 'Inativo', 'portus-cart-for-woocommerce' ),
				__( 'PHP', 'portus-cart-for-woocommerce' )          => PHP_VERSION,
				__( 'Tema ativo', 'portus-cart-for-woocommerce' )   => $theme->exists() ? $theme->get( 'Name' ) . ' ' . $theme->get( 'Version' ) : __( 'Não detectado', 'portus-cart-for-woocommerce' ),
				__( 'Tema pai', 'portus-cart-for-woocommerce' )     => $parent_theme ? $parent_theme->get( 'Name' ) . ' ' . $parent_theme->get( 'Version' ) : __( 'Nenhum', 'portus-cart-for-woocommerce' ),
				__( 'URL AJAX', 'portus-cart-for-woocommerce' )     => admin_url( 'admin-ajax.php' ),
				__( 'Checkout', 'portus-cart-for-woocommerce' )     => function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : __( 'Indisponível', 'portus-cart-for-woocommerce' ),
			);
		}

		/**
		 * Renders a static cart preview from saved settings.
		 *
		 * @param array $settings Current settings.
		 */
		private static function render_preview( $settings ) {
			static $preview_rendered = false;

			if ( $preview_rendered ) {
				return;
			}

			$preview_rendered = true;

			$primary             = self::sanitize_value_by_key( 'primary_color', $settings['primary_color'] );
			$accent              = self::sanitize_value_by_key( 'accent_color', $settings['accent_color'] );
			$floating_icon       = self::sanitize_value_by_key( 'floating_icon', $settings['floating_icon'] );
			$floating_button_size = self::sanitize_value_by_key( 'floating_button_size', $settings['floating_button_size'] );
			$floating_icon_size  = min( self::sanitize_value_by_key( 'floating_icon_size', $settings['floating_icon_size'] ), $floating_button_size - 8 );
			$floating_shape      = self::sanitize_value_by_key( 'floating_shape', $settings['floating_shape'] );
			$floating_background = self::sanitize_value_by_key( 'floating_background_color', $settings['floating_background_color'] );
			$floating_background_rgb = self::hex_to_rgb( $floating_background );
			$floating_icon_mode  = self::sanitize_value_by_key( 'floating_icon_color_mode', $settings['floating_icon_color_mode'] );
			$floating_icon_color = 'custom' === $floating_icon_mode ? self::sanitize_value_by_key( 'floating_icon_color', $settings['floating_icon_color'] ) : $primary;
			$floating_counter_background = self::sanitize_value_by_key( 'floating_counter_background', $settings['floating_counter_background'] );
			$floating_counter_color = self::sanitize_value_by_key( 'floating_counter_text_color', $settings['floating_counter_text_color'] );
			$floating_counter_position = self::sanitize_value_by_key( 'floating_counter_position', $settings['floating_counter_position'] );
			$floating_counter_filled = 'yes' === $settings['floating_counter_background_enabled'];
			$floating_desktop_visible = 'yes' === $settings['show_floating_desktop'];
			$floating_mobile_visible = 'yes' === $settings['show_floating_mobile'];
			$preview_style       = sprintf(
				'--aurea-preview-primary:%1$s;--aurea-preview-accent:%2$s;--aurea-preview-floating-button-size:%3$dpx;--aurea-preview-floating-icon-size:%4$dpx;--aurea-preview-floating-background-rgb:%5$s;--aurea-preview-floating-icon-color:%6$s;--aurea-preview-floating-counter-background:%7$s;--aurea-preview-floating-counter-color:%8$s;',
				$primary,
				$accent,
				$floating_button_size,
				$floating_icon_size,
				implode( ',', $floating_background_rgb ),
				$floating_icon_color,
				$floating_counter_background,
				$floating_counter_color
			);
			$checkout_text       = isset( $settings['checkout_button_text'] ) ? $settings['checkout_button_text'] : __( 'Finalizar compra', 'portus-cart-for-woocommerce' );
			$cart_button_text    = isset( $settings['cart_button_text'] ) ? $settings['cart_button_text'] : __( 'Ver carrinho', 'portus-cart-for-woocommerce' );
			$cart_title          = isset( $settings['cart_title'] ) ? $settings['cart_title'] : __( 'Meu carrinho', 'portus-cart-for-woocommerce' );
			$panel_width         = max( 320, min( 720, absint( $settings['panel_width'] ) ) );
			$overlay_opacity     = max( 0, min( 90, absint( $settings['overlay_opacity'] ) ) ) / 100;
			$floating_side       = in_array( $settings['floating_side'], array( 'left', 'right' ), true ) ? $settings['floating_side'] : 'right';
			$floating_enabled    = 'yes' === $settings['enabled_floating_button'];
			$preview_items       = array(
				array(
					'badge'    => 'AC',
					'name'     => __( 'Pulseira dourada', 'portus-cart-for-woocommerce' ),
					'meta'     => __( 'Cor: Dourado', 'portus-cart-for-woocommerce' ),
					'price'    => 129.90,
					'quantity' => 1,
					'subtotal' => 129.90,
				),
				array(
					'badge'    => 'PC',
					'name'     => __( 'Colar minimalista', 'portus-cart-for-woocommerce' ),
					'meta'     => __( 'Tamanho: Único', 'portus-cart-for-woocommerce' ),
					'price'    => 89.90,
					'quantity' => 2,
					'subtotal' => 179.80,
				),
				array(
					'badge'    => 'BR',
					'name'     => __( 'Brinco Elegance', 'portus-cart-for-woocommerce' ),
					'meta'     => __( 'Banho: Ouro 18k', 'portus-cart-for-woocommerce' ),
					'price'    => 45.00,
					'quantity' => 1,
					'subtotal' => 45.00,
				),
			);
			$preview_count       = 4;
			$preview_subtotal    = 354.70;
			$preview_style      .= sprintf(
				'--aurea-preview-panel-width:%1$dpx;--aurea-preview-overlay-opacity:%2$s;',
				$panel_width,
				rtrim( rtrim( number_format( $overlay_opacity, 2, '.', '' ), '0' ), '.' )
			);
			require MEU_SIDE_CART_PATH . 'includes/admin/preview.php';
		}

		/**
		 * Formats a preview-only price.
		 *
		 * @param float $amount Amount.
		 * @return string
		 */
		private static function format_preview_price( $amount ) {
			if ( function_exists( 'wc_price' ) ) {
				return wc_price( $amount );
			}

			return 'R$' . number_format_i18n( $amount, 2 );
		}

		/**
		 * Renders import/export/reset tools.
		 */
		private static function render_tools_panel() {
			?>
			<section class="portus-cart-for-woocommerce-admin__tools">
				<h2><?php esc_html_e( 'Ferramentas', 'portus-cart-for-woocommerce' ); ?></h2>

				<div class="portus-cart-for-woocommerce-admin__tool-grid">
					<div class="portus-cart-for-woocommerce-admin__tool">
						<h3><?php esc_html_e( 'Exportar configurações', 'portus-cart-for-woocommerce' ); ?></h3>
						<p><?php esc_html_e( 'Baixe um arquivo com todos os ajustes atuais do carrinho.', 'portus-cart-for-woocommerce' ); ?></p>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<input type="hidden" name="action" value="meu_side_cart_export_settings" />
							<?php wp_nonce_field( 'meu_side_cart_export_settings' ); ?>
							<?php submit_button( __( 'Exportar ajustes', 'portus-cart-for-woocommerce' ), 'secondary', 'submit', false ); ?>
						</form>
					</div>

					<div class="portus-cart-for-woocommerce-admin__tool">
						<h3><?php esc_html_e( 'Importar configurações', 'portus-cart-for-woocommerce' ); ?></h3>
						<p><?php esc_html_e( 'Envie um arquivo exportado anteriormente para restaurar os ajustes.', 'portus-cart-for-woocommerce' ); ?></p>
						<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<input type="hidden" name="action" value="meu_side_cart_import_settings" />
							<?php wp_nonce_field( 'meu_side_cart_import_settings' ); ?>
							<input type="file" name="meu_side_cart_import_file" accept="application/json,.json" required />
							<?php submit_button( __( 'Importar ajustes', 'portus-cart-for-woocommerce' ), 'secondary', 'submit', false ); ?>
						</form>
					</div>

					<div class="portus-cart-for-woocommerce-admin__tool portus-cart-for-woocommerce-admin__tool-danger">
						<h3><?php esc_html_e( 'Restaurar padrões', 'portus-cart-for-woocommerce' ); ?></h3>
						<p><?php esc_html_e( 'Volta todas as opções para os valores originais do plugin.', 'portus-cart-for-woocommerce' ); ?></p>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<input type="hidden" name="action" value="meu_side_cart_reset_settings" />
							<?php wp_nonce_field( 'meu_side_cart_reset_settings' ); ?>
							<?php submit_button( __( 'Restaurar padrões', 'portus-cart-for-woocommerce' ), 'delete', 'submit', false, array( 'onclick' => "return confirm('" . esc_js( __( 'Tem certeza que deseja restaurar as configurações padrão?', 'portus-cart-for-woocommerce' ) ) . "');" ) ); ?>
						</form>
					</div>
				</div>
			</section>
			<?php
		}


		/**
		 * Renders operation messages.
		 */
		private static function render_message() {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin notice routing value.
			$message_key = isset( $_GET['meu_side_cart_message'] ) ? sanitize_key( wp_unslash( $_GET['meu_side_cart_message'] ) ) : '';

			if ( ! $message_key ) {
				return;
			}

			$messages = array(
				'imported'      => array( 'success', __( 'Configurações importadas com sucesso.', 'portus-cart-for-woocommerce' ) ),
				'import_failed' => array( 'error', __( 'Não foi possível importar o arquivo. Verifique se o JSON é válido.', 'portus-cart-for-woocommerce' ) ),
				'reset'         => array( 'success', __( 'Configurações restauradas para o padrão.', 'portus-cart-for-woocommerce' ) ),
			);

			if ( ! isset( $messages[ $message_key ] ) ) {
				return;
			}

			list( $type, $message ) = $messages[ $message_key ];
			?>
			<div class="notice notice-<?php echo esc_attr( $type ); ?> is-dismissible">
				<p><?php echo esc_html( $message ); ?></p>
			</div>
			<?php
		}

		/**
		 * Returns settings for export.
		 *
		 * @return array
		 */
		private static function get_export_settings() {
			return self::get_settings();
		}







		/**
		 * Sanitizes a complete settings payload.
		 *
		 * @param array $settings Raw settings.
		 * @return array
		 */
		private static function sanitize_full_settings( $settings ) {
			$defaults  = self::get_defaults();
			$sanitized = array();

			foreach ( array_keys( $defaults ) as $key ) {
				$value             = array_key_exists( $key, $settings ) ? $settings[ $key ] : $defaults[ $key ];
				$sanitized[ $key ] = self::sanitize_value_by_key( $key, $value );
			}

			$sanitized = self::normalize_floating_sizes( $sanitized );

			return wp_parse_args( $sanitized, $defaults );
		}

		/**
		 * Keeps the floating icon inside the configured button dimensions.
		 *
		 * @param array $settings Sanitized settings.
		 * @return array
		 */
		private static function normalize_floating_sizes( $settings ) {
			$defaults    = self::get_defaults();
			$button_size = isset( $settings['floating_button_size'] ) ? absint( $settings['floating_button_size'] ) : $defaults['floating_button_size'];
			$icon_size   = isset( $settings['floating_icon_size'] ) ? absint( $settings['floating_icon_size'] ) : $defaults['floating_icon_size'];

			$button_size = max( 44, min( 80, $button_size ) );
			$icon_size   = max( 16, min( 40, $icon_size, $button_size - 8 ) );

			$settings['floating_button_size'] = $button_size;
			$settings['floating_icon_size']   = $icon_size;

			return $settings;
		}

		/**
		 * Sanitizes one setting by key.
		 *
		 * @param string $key Setting key.
		 * @param mixed  $value Raw value.
		 * @return mixed
		 */
		private static function sanitize_value_by_key( $key, $value ) {
			$defaults = self::get_defaults();

			if ( in_array( $key, self::get_checkbox_keys(), true ) ) {
				return 'yes' === $value ? 'yes' : 'no';
			}

			if ( 'floating_bottom_offset' === $key ) {
				if ( ! is_scalar( $value ) ) {
					return $defaults[ $key ];
				}

				return max( 70, min( 240, absint( $value ) ) );
			}

			if ( 'floating_button_size' === $key ) {
				if ( ! is_scalar( $value ) ) {
					return $defaults[ $key ];
				}

				return max( 44, min( 80, absint( $value ) ) );
			}

			if ( 'floating_icon_size' === $key ) {
				if ( ! is_scalar( $value ) ) {
					return $defaults[ $key ];
				}

				return max( 16, min( 40, absint( $value ) ) );
			}

			if ( 'cart_z_index' === $key ) {
				if ( ! is_scalar( $value ) ) {
					return $defaults[ $key ];
				}

				return max( 1000, min( 2147483000, absint( $value ) ) );
			}

			if ( 'panel_width' === $key ) {
				if ( ! is_scalar( $value ) ) {
					return $defaults[ $key ];
				}

				return max( 320, min( 720, absint( $value ) ) );
			}

			if ( 'overlay_opacity' === $key ) {
				if ( ! is_scalar( $value ) ) {
					return $defaults[ $key ];
				}

				return max( 0, min( 90, absint( $value ) ) );
			}

			if ( 'floating_side' === $key ) {
				$side = is_scalar( $value ) ? sanitize_key( $value ) : $defaults[ $key ];

				return in_array( $side, array( 'left', 'right' ), true ) ? $side : $defaults[ $key ];
			}

			if ( 'floating_icon' === $key ) {
				$icon  = is_scalar( $value ) ? sanitize_key( $value ) : $defaults[ $key ];
				$valid = array_keys( self::get_floating_icon_options() );

				return in_array( $icon, $valid, true ) ? $icon : $defaults[ $key ];
			}

			if ( 'floating_shape' === $key ) {
				$shape = is_scalar( $value ) ? sanitize_key( $value ) : $defaults[ $key ];

				return in_array( $shape, array( 'circle', 'rounded' ), true ) ? $shape : $defaults[ $key ];
			}

			if ( 'floating_icon_color_mode' === $key ) {
				$mode = is_scalar( $value ) ? sanitize_key( $value ) : $defaults[ $key ];

				return in_array( $mode, array( 'primary', 'custom' ), true ) ? $mode : $defaults[ $key ];
			}

			if ( 'floating_counter_position' === $key ) {
				$position = is_scalar( $value ) ? sanitize_key( $value ) : $defaults[ $key ];

				return in_array( $position, array( 'center', 'top-right', 'top-left' ), true ) ? $position : $defaults[ $key ];
			}

			if ( 'low_stock_threshold' === $key ) {
				if ( ! is_scalar( $value ) ) {
					return $defaults[ $key ];
				}

				return max( 1, min( 10, absint( $value ) ) );
			}


			if ( in_array( $key, array( 'primary_color', 'accent_color', 'floating_background_color', 'floating_icon_color', 'floating_counter_background', 'floating_counter_text_color' ), true ) ) {
				if ( ! is_scalar( $value ) ) {
					return $defaults[ $key ];
				}

				$color = sanitize_hex_color( $value );

				return $color ? $color : $defaults[ $key ];
			}




			if ( 'empty_button_url' === $key ) {
				if ( ! is_scalar( $value ) ) {
					return '';
				}

				$url = esc_url_raw( trim( $value ) );

				return $url ? $url : '';
			}



			if ( ! is_scalar( $value ) ) {
				return $defaults[ $key ];
			}

			$value = sanitize_text_field( $value );

			return '' === $value ? $defaults[ $key ] : $value;
		}

		/**
		 * Verifies an admin tool request.
		 *
		 * @param string $action Nonce action.
		 */
		private static function verify_tool_request( $action ) {
			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_die( esc_html__( 'Você não tem permissão para acessar esta ação.', 'portus-cart-for-woocommerce' ) );
			}

			check_admin_referer( $action );
		}

		/**
		 * Redirects back to an admin tab with a message.
		 *
		 * @param string $message Message key.
		 * @param string $tab     Target tab.
		 */
		private static function redirect_with_message( $message, $tab = 'advanced' ) {
			wp_safe_redirect(
				self::get_page_url(
					array(
						'tab'                => $tab,
						'meu_side_cart_message' => $message,
					)
				)
			);
			exit;
		}

		/**
		 * Returns a settings page URL.
		 *
		 * @param array $args Query args.
		 * @return string
		 */
		private static function get_page_url( $args = array() ) {
			return add_query_arg(
				wp_parse_args(
					$args,
					array(
						'page' => self::MENU_SLUG,
					)
				),
				admin_url( 'admin.php' )
			);
		}

		/**
		 * Sanitizes a checkbox setting.
		 *
		 * @param array  $input Raw input.
		 * @param string $key Setting key.
		 * @return string
		 */
		private static function sanitize_checkbox_setting( $input, $key ) {
			return isset( $input[ $key ] ) && 'yes' === $input[ $key ] ? 'yes' : 'no';
		}

		/**
		 * Renders a text field.
		 *
		 * @param string $key Setting key.
		 * @param string $label Field label.
		 * @param string $value Current value.
		 */
		private static function render_text_input( $key, $label, $value ) {
			?>
			<label class="portus-cart-for-woocommerce-admin__field">
				<span><?php echo esc_html( $label ); ?></span>
				<input type="text" name="<?php echo esc_attr( self::OPTION_NAME . '[' . $key . ']' ); ?>" value="<?php echo esc_attr( $value ); ?>" />
			</label>
			<?php
		}

		/**
		 * Renders the allow-listed local SVG selector for the floating button.
		 *
		 * @param string $value Current icon key.
		 */
		private static function render_floating_icon_picker( $value ) {
			$options = self::get_floating_icon_options();
			$value   = is_scalar( $value ) ? sanitize_key( (string) $value ) : 'bag-fill';
			$value   = array_key_exists( $value, $options ) ? $value : 'bag-fill';
			?>
			<fieldset class="portus-cart-for-woocommerce-admin__icon-picker">
				<legend><?php esc_html_e( 'Ícone do botão', 'portus-cart-for-woocommerce' ); ?></legend>
				<div>
					<?php foreach ( $options as $icon_key => $icon_label ) : ?>
						<label>
							<input type="radio" name="<?php echo esc_attr( self::OPTION_NAME . '[floating_icon]' ); ?>" value="<?php echo esc_attr( $icon_key ); ?>" <?php checked( $value, $icon_key ); ?> />
							<span class="portus-cart-for-woocommerce-admin__icon-choice" aria-hidden="true">
								<i class="portus-cart-for-woocommerce-admin__floating-icon portus-cart-for-woocommerce-admin__floating-icon--<?php echo esc_attr( sanitize_html_class( $icon_key ) ); ?>"></i>
							</span>
							<strong><?php echo esc_html( $icon_label ); ?></strong>
						</label>
					<?php endforeach; ?>
				</div>
			</fieldset>
			<?php
		}

		/**
		 * Renders a number field.
		 *
		 * @param string $key Setting key.
		 * @param string $label Field label.
		 * @param int    $value Current value.
		 * @param int    $min Minimum value.
		 * @param int    $max Maximum value.
		 */
		private static function render_number_input( $key, $label, $value, $min, $max ) {
			?>
			<label class="portus-cart-for-woocommerce-admin__field">
				<span><?php echo esc_html( $label ); ?></span>
				<input type="number" min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>" name="<?php echo esc_attr( self::OPTION_NAME . '[' . $key . ']' ); ?>" value="<?php echo esc_attr( $value ); ?>" />
			</label>
			<?php
		}

		/**
		 * Renders a range field with a live value label.
		 *
		 * @param string $key Setting key.
		 * @param string $label Field label.
		 * @param int    $value Current value.
		 * @param int    $min Minimum value.
		 * @param int    $max Maximum value.
		 * @param int    $step Step value.
		 * @param string $suffix Display suffix.
		 */
		private static function render_range_input( $key, $label, $value, $min, $max, $step, $suffix ) {
			?>
			<label class="portus-cart-for-woocommerce-admin__field portus-cart-for-woocommerce-admin__field-range">
				<span>
					<?php echo esc_html( $label ); ?>
					<em><b data-aurea-range-value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( absint( $value ) ); ?></b><?php echo esc_html( $suffix ); ?></em>
				</span>
				<input type="range" min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>" step="<?php echo esc_attr( $step ); ?>" name="<?php echo esc_attr( self::OPTION_NAME . '[' . $key . ']' ); ?>" value="<?php echo esc_attr( $value ); ?>" />
			</label>
			<?php
		}

		/**
		 * Renders a color field.
		 *
		 * @param string $key Setting key.
		 * @param string $label Field label.
		 * @param string $value Current value.
		 */
		private static function render_color_input( $key, $label, $value ) {
			?>
			<label class="portus-cart-for-woocommerce-admin__field portus-cart-for-woocommerce-admin__field-color">
				<span><?php echo esc_html( $label ); ?></span>
				<input type="color" name="<?php echo esc_attr( self::OPTION_NAME . '[' . $key . ']' ); ?>" value="<?php echo esc_attr( self::sanitize_value_by_key( $key, $value ) ); ?>" />
			</label>
			<?php
		}

		/**
		 * Renders a select field.
		 *
		 * @param string $key Setting key.
		 * @param string $label Field label.
		 * @param string $value Current value.
		 * @param array  $options Select options.
		 */
		private static function render_select_input( $key, $label, $value, $options ) {
			?>
			<label class="portus-cart-for-woocommerce-admin__field">
				<span><?php echo esc_html( $label ); ?></span>
				<select name="<?php echo esc_attr( self::OPTION_NAME . '[' . $key . ']' ); ?>">
					<?php foreach ( $options as $option_value => $option_label ) : ?>
						<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $value, $option_value ); ?>>
							<?php echo esc_html( $option_label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>
			<?php
		}

		/**
		 * Renders a checkbox field.
		 *
		 * @param string $key Setting key.
		 * @param string $label Field label.
		 * @param string $value Current value.
		 */
		private static function render_checkbox_input( $key, $label, $value ) {
			?>
			<label class="portus-cart-for-woocommerce-admin__toggle">
				<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME . '[' . $key . ']' ); ?>" value="yes" <?php checked( 'yes', $value ); ?> />
				<span><?php echo esc_html( $label ); ?></span>
			</label>
			<?php
		}

		/**
		 * Renders small scoped admin styles.
		 */
		private static function render_admin_styles() {
			// Admin styles are enqueued from assets/css/admin.css.
		}
	}
}
