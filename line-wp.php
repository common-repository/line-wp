<?php
/**
 * Plugin Name: LINE for WordPress
 * Plugin URI: https://wordpress.org/plugins/line-wp/
 * Description: A plugin that integrates WordPress and LINE.
 * Version: 1.0.1
 * Author: Artisan Workshop
 * Author URI: https://wc.artws.info/
 * Requires at least: 5.7
 * Tested up to: 5.7.0
 *
 * Text Domain: line-wp
 * Domain Path: /i18n/
 *
 * @package line-wp
 * @author Artisan Workshop
 */
//use ArtisanWorkshop\WooCommerce\PluginFramework\v2_0_8 as Framework;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'LineWP' ) ) :

class LineWP{

	/**
	 * LINE for WP version.
	 *
	 * @var string
	 */
	public $version = '1.0.1';

    /**
     * Artisan Workshop FrameWork for WooCommerce version.
     *
     * @var string
     */
	public $framework_version = '2.0.8';

    /**
     * The single instance of the class.
     *
     * @var object
     */
    protected static $instance = null;

	/**
	 * LINE for WP Constructor.
     *
	 * @access public
	 * @return LineWP
	 */
	public function __construct() {
		// rated appeal
		add_action( 'wp_ajax_wc4jp_rated', array( __CLASS__, 'jp4wc_rated') );
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 1 );
	}

    /**
     * Get class instance.
     *
     * @return object Instance.
     */
    public static function instance() {
        if ( null === static::$instance ) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Init the feature plugin, only if we can detect WooCommerce.
     *
     * @since 2.0.0
     * @version 2.0.0
     */
    public function init() {
        $this->define_constants();
        register_deactivation_hook( LINE_WP_PLUGIN_FILE, array( $this, 'on_deactivation' ) );
        add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ), 20 );
    }

    /**
     * Flush rewrite rules on deactivate.
     *
     * @return void
     */
    public function on_deactivation() {
        flush_rewrite_rules();
    }

    /**
     * Setup plugin once all other plugins are loaded.
     *
     * @return void
     */
    public function on_plugins_loaded() {
        $this->load_plugin_textdomain();
        $this->includes();
    }

    /**
     * Define Constants.
     */
    protected function define_constants() {
        $this->define( 'LINE_WP_ABSPATH', dirname( __FILE__ ) . '/' );
        $this->define( 'LINE_WP_URL_PATH', plugins_url( '/' ) . 'line-wp/' );
        $this->define( 'LINE_WP_INCLUDES_PATH', LINE_WP_ABSPATH . 'includes/' );
        $this->define( 'LINE_WP_PLUGIN_FILE', __FILE__ );
        $this->define( 'LINE_WP_VERSION', $this->version );
        $this->define( 'LINE_WP_FRAMEWORK_VERSION', $this->framework_version );
        $this->define( 'LINE_WP_PLUGIN_PREFIX', 'line_wp_' );
        $this->define( 'LINE_WP_ENCRYPT_PASSWORD', 'N6rMue5B' );
    }

    /**
     * Load Localisation files.
     */
    protected function load_plugin_textdomain() {
        load_plugin_textdomain( 'line-wp', false, basename( dirname( __FILE__ ) ) . '/i18n' );
    }

    /**
	 * Include JP4WC classes.
	 */
	private function includes() {
		//load framework
        $version_text = 'v'.str_replace('.', '_', LINE_WP_FRAMEWORK_VERSION);
		if ( ! class_exists( '\\ArtisanWorkshop\\WooCommerce\\PluginFramework\\'.$version_text.'\\JP4WC_Plugin' ) ) {
            require_once LINE_WP_INCLUDES_PATH.'jp4wc-framework/class-jp4wc-framework.php';
		}
        // Admin Setting Screen
        require_once LINE_WP_INCLUDES_PATH.'admin/class-admin-setting.php';
        // Post send to LINE
        require_once LINE_WP_INCLUDES_PATH.'class-line-wp-post-send-line.php';
	}

	/**
	 * Change the admin footer text on WooCommerce for Japan admin pages.
	 *
	 * @since  1.2
     * @version 2.0.0
	 * @param  string $footer_text
	 * @return string
	 */
	public function admin_footer_text( $footer_text ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return $footer_text;
		}
		$current_screen = get_current_screen();
		$wc4jp_pages = 'line-settings';
		// Check to make sure we're on a WooCommerce admin page
		if ( isset( $current_screen->id ) && $current_screen->id == $wc4jp_pages ) {
			if ( ! get_option( 'line_wp_rated' ) ) {
				$footer_text = sprintf( __( 'If you like <strong>"LINE for WordPress</strong> please leave us a %s&#9733;&#9733;&#9733;&#9733;&#9733;%s rating. A huge thanks in advance!', 'line-wp' ), '<a href="https://wordpress.org/support/plugin/line-wp/reviews?rate=5#new-post" target="_blank" class="wc4jp-rating-link" data-rated="' . esc_attr__( 'Thanks :)', 'line-wp' ) . '">', '</a>' );
				wc_enqueue_js( "
					jQuery( 'a.wc4jp-rating-link' ).click( function() {
						jQuery.post( '" . WC()->ajax_url() . "', { action: 'wc4jp_rated' } );
						jQuery( this ).parent().text( jQuery( this ).data( 'rated' ) );
					});
				" );
			}else{
				$footer_text = __( 'Thank you for installing with LINE for WordPress.', 'line-wp' );
			}
		}
		return $footer_text;
	}
	/**
	 * Triggered when clicking the rating footer.
	 */
	public static function jp4wc_rated() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die(-1);
		}

		update_option( 'line_wp_rated', 1 );
		die();
	}

    /**
     * Define constant if not already set.
     *
     * @param string Constant name.
     * @param string|bool Constant value.
     */
    protected function define( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }
}

endif;

/**
 * Load plugin functions.
 */
add_action( 'plugins_loaded', 'LINEWP_plugin');

function LINEWP_plugin() {
    LineWP::instance()->init();
}
