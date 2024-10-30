<?php

/**
 * Helpers library functions
 *
 * @since      0.0.1
 *
 * @package    Wtsr
 * @subpackage Wtsr/includes
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function wtsr_is_plugin_pro() {
    include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wtsr-license.php';

    return 'pro' === Wtsr_License::get_license_version();
}

/**
 * wcj_is_plugin_activated.
 *
 * @version 0.0.1
 * @since   0.0.1
 * @return  bool
 */
function wtsr_is_plugin_activated( $plugin_folder, $plugin_file ) {
    if ( wtsr_is_plugin_active_simple( $plugin_folder . '/' . $plugin_file ) ) {
        return true;
    } else {
        return wtsr_is_plugin_active_by_file( $plugin_file );
    }
}

/**
 * wcj_is_plugin_active_simple.
 *
 * @version 0.0.1
 * @since   0.0.1
 * @return  bool
 */
function wtsr_is_plugin_active_simple( $plugin ) {
    return (
        in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ) ) ||
        ( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) )
    );
}

/**
 * wcj_is_plugin_active_by_file.
 *
 * @version 0.0.1
 * @since   0.0.1
 * @return  bool
 */
function wtsr_is_plugin_active_by_file( $plugin_file ) {
    foreach ( wtsr_get_active_plugins() as $active_plugin ) {
        $active_plugin = explode( '/', $active_plugin );

        if ( isset( $active_plugin[1] ) && $plugin_file === $active_plugin[1] ) {
            return true;
        }
    }

    return false;
}

/**
 * wcj_get_active_plugins.
 *
 * @version 0.0.1
 * @since   0.0.1
 * @return  array
 */
function wtsr_get_active_plugins() {
    $active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );
    if ( is_multisite() ) {
        $active_plugins = array_merge( $active_plugins, array_keys( get_site_option( 'active_sitewide_plugins', array() ) ) );
    }
    return $active_plugins;
}

/**
 * Show templates passing attributes and including the file.
 *
 * @param string $template_name
 * @param array $args
 * @param string $template_path
 */
function wtsr_show_template($template_name, $args = array(), $template_path = 'public/partials')
{
    if (!empty($args) && is_array($args)) {
        extract($args);
    }

    $located = wtsr_locate_template($template_name, $template_path);

    if (!file_exists($located)) {
        return;
    }

    include($located);
}

/**
 * Like show, but returns the HTML instead of outputting.
 *
 * @param $template_name
 * @param array $args
 * @param string $template_path
 * @param string $default_path
 *
 * @return string
 */
function wtsr_get_template($template_name, $args = array(), $template_path = 'public/partials')
{
    ob_start();
    wtsr_show_template($template_name, $args, $template_path);
    return ob_get_clean();
}

/**
 * Locate a template and return the path for inclusion.
 *
 * @param $template_name
 * @param string $template_path
 * @return string
 */
function wtsr_locate_template($template_name, $template_path = 'public/partials/')
{
    if (!$template_path) {
        $template_path = 'public/partials/';
    }

    $template_path = untrailingslashit( plugin_dir_path( WTSR_PLUGIN_FILE ) ) . '/public/partials/';

    $template = locate_template(
        array(
            trailingslashit($template_path) . $template_name,
            $template_name
        )
    );

    // Get default template/.
    if ( ! $template ) {
        $template = $template_path . $template_name;
    }

    return $template;
}

function wtsr_maybe_generate_thankyou_email($status, $review_id, $order_id) {
    if ('reviewed' !== $status) return false;

    $wtsr_thankyou_settings = Wtsr_Settings::get_thankyou_settings();

    if (
        'no' === $wtsr_thankyou_settings["thankyou_enabled"] ||
        '' === trim($wtsr_thankyou_settings["thankyou_template"])
    ) {
        return false;
    }

    // Check if review exists
    if (empty($review = ReviewsModel::get_by_id($review_id))) return false;

    // Check if order exists
    if (empty($order = wc_get_order( $order_id ))) return false;

    // Check if email exists
    if (empty($email = $review['email'])) return false;

    $thankyou_template = wp_unslash($wtsr_thankyou_settings["thankyou_template"]);

    $coupon_id = false;
    $coupon_description = '';

    // If coupon generation enabled - generate new coupon
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/lib/Wtsr_Coupon.php';
    if (Wtsr_Coupon::is_coupon_generation_enabled($review_id)) $coupon_id = wtsr_generate_coupon($review_id, $email);

    if (!empty($coupon_id)) {
        $coupon = new WC_Coupon( $coupon_id );
        $coupon_description = wpautop($coupon->get_description());
    }

    if (false !== strpos($thankyou_template, '{coupon_description}')) {
        $thankyou_template = str_replace( '{coupon_description}', $coupon_description, $thankyou_template );
    }

    ReviewsModel::update_meta($review_id, 'thankyou_email_template', wpautop($thankyou_template));

    if ( !empty(trim($thankyou_template)) && 'woocommerce' === TSManager::get_email_send_via() ) {
        $mailer = WC()->mailer();
        $mail = $mailer->emails['Wtsr_WC_Email_Customer_Coupon'];
        $mail->trigger( $review_id );
    }

    return true;
}

function wtsr_generate_coupon($review_id, $order_email) {
    $wtsr_thankyou_settings = Wtsr_Settings::get_thankyou_settings();

    $data = array(
        'customer_email' => array($order_email),
        'review_id' => $review_id,
        'discount_type' => $wtsr_thankyou_settings["discount_type"],
        'coupon_amount' => $wtsr_thankyou_settings["coupon_amount"],
    );

    if (!empty($wtsr_thankyou_settings["coupon_expiration"])) {
        $date_expires = ((int) $wtsr_thankyou_settings["coupon_expiration"] * 60 * 60) + time();
        $data["date_expires"] = $date_expires;
        $data['expiry_date'] = date('Y-m-d', $date_expires);
        $data['coupon_expiration'] = $wtsr_thankyou_settings["coupon_expiration"];
    }

    if (!empty($wtsr_thankyou_settings["coupon_description"])) {
        $data['post_excerpt'] = $wtsr_thankyou_settings["coupon_description"];
    }

    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/lib/Wtsr_Coupon.php';

    $coupon_id = Wtsr_Coupon::generate_coupon($data);

    return $coupon_id;
}

function wtsr_unlim_email_template_length($limit) {
    return 60000;
}

function wtsr_add_coupon_valid_date($coupon_label, $coupon) {
    $coupon_id = $coupon->get_id();
    $coupon_review_id = get_post_meta($coupon_id, 'review_id', true);
    $expiry_date = get_post_meta($coupon_id, 'date_expires', true);

    if (!empty($coupon_review_id) && !empty($expiry_date) && is_numeric($expiry_date)) {
        $coupon_label = $coupon_label . ' (' . __('coupon valid until: ', 'more-better-reviews-for-woocommerce') . date('d-m-Y H:i', $expiry_date) . ')';
        // $coupon_label = $coupon_label . ' (' . __('coupon valid until: ', 'more-better-reviews-for-woocommerce') . $expiry_date . ')';
    }

    return $coupon_label;
}

add_filter('woocommerce_cart_totals_coupon_label', 'wtsr_add_coupon_valid_date', 100, 2);