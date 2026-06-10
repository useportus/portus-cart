<?php
/**
 * Portus Cart for WooCommerce - implementation v3.
 *
 * @package PortusCart
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Meu_Side_Cart_Plugin' ) ) {
	/**
	 * Main plugin bootstrap.
	 */
	final class Meu_Side_Cart_Plugin {
		/**
		 * Singleton instance.
		 *
		 * @var Meu_Side_Cart_Plugin|null
		 */
		private static $instance = null;

		/**
		 * Returns the singleton instance.
		 *
		 * @return Meu_Side_Cart_Plugin
		 */
		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Prevent direct construction.
		 */
		private function __construct() {
			add_action( 'before_woocommerce_init', array( $this, 'declare_woocommerce_compatibility' ) );
			add_action( 'plugins_loaded', array( $this, 'init' ), 20 );
			add_action( 'admin_notices', array( $this, 'missing_woocommerce_notice' ) );
		}

		/**
		 * Blocks activation when WooCommerce is inactive.
		 */
		public static function activate() {
			if ( class_exists( 'WooCommerce' ) ) {
				return;
			}

			deactivate_plugins( MEU_SIDE_CART_BASENAME );

			wp_die(
				esc_html__( 'Portus Cart for WooCommerce precisa do WooCommerce ativo para funcionar.', 'portus-cart-for-woocommerce' ),
				esc_html__( 'WooCommerce obrigatorio', 'portus-cart-for-woocommerce' ),
				array( 'back_link' => true )
			);
		}

		/**
		 * Declares compatibility with current WooCommerce storage features.
		 */
		public function declare_woocommerce_compatibility() {
			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', MEU_SIDE_CART_FILE, true );
			}
		}

		/**
		 * Starts the plugin after WooCommerce is available.
		 */
		public function init() {
			if ( ! $this->is_woocommerce_active() ) {
				return;
			}


			$this->includes();

			Meu_Side_Cart_Settings::init();
			Meu_Side_Cart_Cart::init();
			Meu_Side_Cart_Ajax::init();
			Meu_Side_Cart_Coupon::init();

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 20 );
			add_action( 'wp_footer', array( $this, 'render' ), 30 );
			add_filter( 'body_class', array( $this, 'body_class' ) );
		}

		/**
		 * Checks if WooCommerce is loaded.
		 *
		 * @return bool
		 */
		private function is_woocommerce_active() {
			return class_exists( 'WooCommerce' );
		}

		/**
		 * Loads plugin modules.
		 */
		private function includes() {
			require_once MEU_SIDE_CART_PATH . 'includes/settings.php';
			require_once MEU_SIDE_CART_PATH . 'includes/cart.php';
			require_once MEU_SIDE_CART_PATH . 'includes/ajax.php';
			require_once MEU_SIDE_CART_PATH . 'includes/coupon.php';
		}

		/**
		 * Adds a small body class for debugging and integration.
		 *
		 * @param string[] $classes Body classes.
		 * @return string[]
		 */
		public function body_class( $classes ) {
			$classes[] = 'portus-cart-for-woocommerce-active';

			return $classes;
		}

		/**
		 * Enqueues isolated frontend assets.
		 */
		public function enqueue_assets() {
			if ( is_admin() ) {
				return;
			}

			wp_enqueue_style(
				'portus-cart-for-woocommerce',
				MEU_SIDE_CART_URL . 'assets/css/style.css',
				array(),
				MEU_SIDE_CART_VERSION
			);

			wp_enqueue_script(
				'portus-cart-for-woocommerce',
				MEU_SIDE_CART_URL . 'assets/js/script.js',
				array( 'jquery' ),
				MEU_SIDE_CART_VERSION,
				true
			);

			wp_localize_script(
				'portus-cart-for-woocommerce',
				'MeuSideCartData',
				array(
					'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
					'nonce'         => wp_create_nonce( 'meu_side_cart_nonce' ),
					'compatibility' => array(
						'autoOpenOnAdd'          => class_exists( 'Meu_Side_Cart_Settings' ) && Meu_Side_Cart_Settings::is_enabled( 'auto_open_on_add' ),
					),
					'i18n'          => array(
						'genericError'    => esc_html__( 'Não foi possível atualizar o carrinho. Tente novamente.', 'portus-cart-for-woocommerce' ),
						'loading'         => esc_html__( 'Atualizando...', 'portus-cart-for-woocommerce' ),
						'favoriteAdded'   => esc_html__( 'Produto adicionado aos favoritos.', 'portus-cart-for-woocommerce' ),
						'favoriteRemoved' => esc_html__( 'Produto removido dos favoritos.', 'portus-cart-for-woocommerce' ),
					),
				)
			);
		}

		/**
		 * Prints the side cart in wp_footer for broad theme compatibility.
		 */
		public function render() {
			if ( is_admin() || ! function_exists( 'WC' ) || ! WC()->cart ) {
				return;
			}

			Meu_Side_Cart_Cart::render_shell();
		}

		/**
		 * Shows an admin notice when WooCommerce is missing.
		 */
		public function missing_woocommerce_notice() {
			if ( $this->is_woocommerce_active() || ! current_user_can( 'activate_plugins' ) ) {
				return;
			}
			?>
			<div class="notice notice-error">
				<p><?php esc_html_e( 'Portus Cart for WooCommerce está inativo porque o WooCommerce não está ativo.', 'portus-cart-for-woocommerce' ); ?></p>
			</div>
			<?php
		}
	}
}

register_activation_hook( MEU_SIDE_CART_FILE, array( 'Meu_Side_Cart_Plugin', 'activate' ) );

/**
 * Returns the plugin instance.
 *
 * @return Meu_Side_Cart_Plugin
 */
function meu_side_cart_plugin() {
	return Meu_Side_Cart_Plugin::instance();
}

meu_side_cart_plugin();
