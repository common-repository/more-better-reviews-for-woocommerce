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
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$required_plugins = Wtsr_Required_Plugins::required_plugins();
$plugin_url = untrailingslashit( plugins_url( '/', WTSR_PLUGIN_FILE ) );
?>

<?php if (!empty($required_plugins)) {
    ?>
    <p>
        <?php _e('Before running Better Reviews for WooCommerce wizard, you need to install and activate required Wordpress plugins from the list below.', 'more-better-reviews-for-woocommerce'); ?>
    </p>

    <div class="wtsr-processing-holder">
        <div class="required-plugin-container">
            <?php
            foreach ($required_plugins as $slug => $data) {
                $plugin_installed = Wtsr_Required_Plugins::is_plugin_installed($data['slug']);
                ?>
                <div class="required-plugin-holder">
                    <div class="required-plugin-holder-inner">
                        <div class="img-holder">
                            <img src="<?php echo $plugin_url . '/admin/img/icon-'.$slug.'.png'; ?>" alt="">
                        </div>

                        <div class="info-holder">
                            <h3><a href="<?php echo $data['link'] ?>" target="_blank"><?php echo $data['label'] ?></a></h3>
                            <p><?php echo $data['author'] ?></p>
                            <?php
                            if (!empty($data['notice'])) {
                                echo $data['notice'];
                            }
                            ?>
                        </div>

                        <div class="button-holder">
                            <button id="install-<?php echo $slug ?>" class="wtsr-install-plugin button button-primary" data-plugin="<?php echo $slug ?>">
                                <?php _e('Install and Activate', 'more-better-reviews-for-woocommerce'); ?>
                            </button>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>

        <div class="wtsr-spinner-holder">
            <div class="wtsr-spinner"></div>
        </div>
    </div>
    <?php
} ?>
