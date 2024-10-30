<?php
/**
 * The plugin bootstrap file
 *
 * @since             0.0.1
 * @package           Wtsr
 *
 * @wordpress-plugin
 * Plugin Name:       Get Better Reviews for WooCommerce
 * Plugin URI:        https://saleswonder.biz/blog/bessere-bewertungen-fuer-woocommerce/
 * Description:       Get Better Reviews for WooCommerce is an automated reviews request system for WooCommerce reviews
 * Version:           4.0.6
 * Author:            Tobias Conrad
 * Author URI:        https://saleswonder.biz/
 * Requires at least: 5.7
 * Tested up to: 6.4
 * WC requires at least: 6
 * WC tested up to: 8.3
 * Requires at least WooCommerce: 6
 * Tested up to WooCommerce: 8.3
 * Requires PHP: 7.2
 *
 * Text Domain:       more-better-reviews-for-woocommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 0.0.1 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WTSR_VERSION', '4.0.6' );
define( 'WTSR_DB_VERSION', '2.1.0' );

// Define WC_PLUGIN_FILE.
if ( ! defined( 'WTSR_PLUGIN_FILE' ) ) {
    define( 'WTSR_PLUGIN_FILE', __FILE__ );
}

define( 'WTSR_PRESENTATION', false );
define( 'WTSR_BRANCH', 'master' );

// Core functions
require_once( 'includes/functions.php' );

if ( !wtsr_is_plugin_pro() && ! function_exists( 'mbrfw_fs' ) ) {
    // Create a helper function for easy SDK access.
    function mbrfw_fs() {
        global $mbrfw_fs;

        if ( ! isset( $mbrfw_fs ) ) {
            // Activate multisite network integration.
            if ( ! defined( 'WP_FS__PRODUCT_5990_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_5990_MULTISITE', true );
            }

            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $mbrfw_fs = fs_dynamic_init( array(
                'id'                  => '5990',
                'slug'                => 'more-better-reviews-for-woocommerce',
                'type'                => 'plugin',
                'public_key'          => 'pk_11518766fe43cd60dc56ba57973a6',
                'is_premium'          => false,
                'has_premium_version' => false,
                'has_addons'          => false,
                'has_paid_plans'      => true,
                'is_org_compliant'    => true,
                'premium_suffix'      => 'Professional',
                'trial'               => array(
                    'days'               => 14,
                    'is_require_payment' => true,
                ),
                'has_affiliation'     => 'all',
                'menu'                => array(
                    'slug'           => 'wp2leads-wtsr',
                ),
                // Set the SDK to work in a sandbox mode (for development & testing).
                // IMPORTANT: MAKE SURE TO REMOVE SECRET KEY BEFORE DEPLOYMENT.
                'secret_key'          => '',
            ) );
        }

        return $mbrfw_fs;
    }

    // Init Freemius.
    mbrfw_fs();
    // Signal that SDK was initiated.
    do_action( 'mbrfw_fs_loaded' );
}

/**
 * WP2LEADS fallback notice.
 *
 * @since 0.0.1
 * @return string
 */
function wtsr_wp2leads_notice() {
    /* translators: 1. URL link. */
    echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'If you like to sent review request to Klick Tipp plugin requires WP2LEADS plugin to be installed and active. You can download %s here.', 'more-better-reviews-for-woocommerce' ), '<a href="https://wp2leads-for-klick-tipp.com/woo-wp4free/pop-verbinde-woo-wp-fuer-lau/" target="_blank">WP2LEADS</a>' ) . '</strong></p></div>';
}

/**
 * WP2LEADS fallback notice.
 *
 * @since 0.0.1
 * @return string
 */
function wtsr_woocommerce_notice() {
    /* translators: 1. URL link. */
    echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'WooCommerce Trusted Shops Reviews requires WooCommerce plugin to be installed and active. You can download %s here.', 'more-better-reviews-for-woocommerce' ), '<a href="https://woocommerce.com/" target="_blank">Woocommerce</a>' ) . '</strong></p></div>';
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wtsr-activator.php
 */
function activate_wtsr() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wtsr-activator.php';
	Wtsr_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wtsr-deactivator.php
 */
function deactivate_wtsr() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wtsr-deactivator.php';
	Wtsr_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wtsr' );
register_deactivation_hook( __FILE__, 'deactivate_wtsr' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wtsr.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.0.1
 */
function run_wtsr() {

	$plugin = new Wtsr();
	$plugin->run();

}
run_wtsr();
