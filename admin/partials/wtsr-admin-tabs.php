<?php

/**
 * WP2LEADS WTSR Tabs
 *
 * @since      0.0.1
 *
 * @package    Wtsr
 * @subpackage Wtsr/admin/partials
 *
 * @var $check_ts_credentials_empty
 * @var $active_tab
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$wtsr_is_dev_env = defined( 'WTSR_DEV_ENV' ) && WTSR_DEV_ENV;
?>

<h2 id="wtsr-nav-tab" class="nav-tab-wrapper">
    <a href="?page=wp2leads-wtsr&tab=overview"
        class="nav-tab <?php echo $active_tab == 'overview' ? 'nav-tab-active' : ''; ?>"
    ><?php _e('Overview', 'more-better-reviews-for-woocommerce') ?></a>

    <a href="?page=wp2leads-wtsr&tab=settings"
        class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>"
    ><?php _e('Settings', 'more-better-reviews-for-woocommerce') ?></a>

    <a href="?page=wp2leads-wtsr&tab=email"
       class="nav-tab <?php echo $active_tab == 'email' ? 'nav-tab-active' : ''; ?>"
    ><?php _e('E-mail and Page', 'more-better-reviews-for-woocommerce') ?></a>

    <a href="?page=wp2leads-wtsr&tab=generate"
       class="nav-tab <?php echo $active_tab == 'generate' ? 'nav-tab-active' : ''; ?>"
    ><?php _e('Generate requests', 'more-better-reviews-for-woocommerce') ?></a>

    <a href="?page=wp2leads-wtsr&tab=reviews"
        class="nav-tab <?php echo $active_tab == 'reviews' ? 'nav-tab-active' : ''; ?>"
    ><?php _e('Review requests', 'more-better-reviews-for-woocommerce') ?></a>

    <?php
    if (!$check_ts_credentials_empty) {
        ?>
        <a href="?page=wp2leads-wtsr&tab=ts-reviews"
           class="nav-tab <?php echo $active_tab == 'ts-reviews' ? 'nav-tab-active' : ''; ?>"
        ><?php _e('TS Reviews', 'more-better-reviews-for-woocommerce') ?></a>
        <?php
    }
    ?>

    <?php
    if ($wtsr_is_dev_env) {
        ?>
        <a href="?page=wp2leads-wtsr&tab=license"
           class="nav-tab <?php echo $active_tab == 'license' ? 'nav-tab-active' : ''; ?>"
        ><?php _e('Dev area', 'more-better-reviews-for-woocommerce') ?></a>
        <?php
    }
    ?>
</h2>

