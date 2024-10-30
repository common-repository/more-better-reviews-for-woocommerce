<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      0.0.1
 *
 * @package    Wtsr
 * @subpackage Wtsr/admin/partials
 *
 * @var $check_ts_credentials_empty
 * @var $required_plugins_wp2leads
 * @var $is_wp2leads_installed
 * @var $default_send_via
 * @var $wtsr_email_send_via
 */

if ( ! defined( 'ABSPATH' ) ) exit;
$is_woocommerce_reviews_disabled_globally = TSManager::is_woocommerce_reviews_disabled_globally();

if (!$is_woocommerce_reviews_disabled_globally) {
    $is_woocommerce_reviews_disabled_per_product = TSManager::is_woocommerce_reviews_disabled_per_product();
}

$is_woocommerce_only_mode = ReviewServiceManager::is_woocommerce_only_mode();
$is_ts_mode = TSManager::is_review_mode_enabled();
$is_woocommerce_only_mode = !$is_ts_mode;
$wtsr_send_via = TSManager::get_email_send_via();
?>

<div class="wrap">
    <h1>
        <?php _e('Better Reviews for WOO', 'more-better-reviews-for-woocommerce'); ?>
        (<?php echo Wtsr_License::get_license_version_label(); ?>)
        <?php
        if ('woocommerce' === $wtsr_send_via) {
            ?>- <?php _e('Woo', 'more-better-reviews-for-woocommerce'); ?><?php
        } else {
            ?>- <?php _e('Klick Tipp', 'more-better-reviews-for-woocommerce'); ?><?php
        }
        ?>
        <?php
        if (!$is_woocommerce_only_mode) {
            ?>- <?php _e('Trusted Shops', 'more-better-reviews-for-woocommerce'); ?><?php
        }
        ?>
    </h1>

    <?php settings_errors(); ?>

    <?php include(dirname(__FILE__) . '/wtsr-admin-tabs.php'); ?>

    <?php
    switch($active_tab) {
        case "overview":
            require_once dirname(__FILE__) . '/wtsr-admin-overview.php';
            break;

        case "email":
            require_once dirname(__FILE__) . '/wtsr-admin-email.php';
            break;

        case "generate":
            require_once dirname(__FILE__) . '/wtsr-admin-generate.php';
            break;

        case "reviews":
            if (!empty($_GET['review_id'])) {
                require_once dirname(__FILE__) . '/wtsr-admin-review.php';
            } else {
                require_once dirname(__FILE__) . '/wtsr-admin-reviews.php';
            }
            break;

        case "ts-reviews":
            require_once dirname(__FILE__) . '/wtsr-admin-ts-reviews.php';
            break;

        case "license":
            require_once dirname(__FILE__) . '/wtsr-admin-license.php';
            break;

        case "settings":
        default:
            require_once dirname(__FILE__) . '/wtsr-admin-settings.php';
            break;
    }
    ?>
</div>
