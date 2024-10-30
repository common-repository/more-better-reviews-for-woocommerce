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

$license_info = Wtsr_License::get_lecense_info();
$activation_in_progress = get_transient( 'wtsr_activation_in_progress' );
$user_limit = get_option('wtsr_limit_users') * Wtsr_License::get_license_multi();
$days_limit = get_option('wtsr_limit_days');
$license_version = Wtsr_License::get_license_version();

if ('free' === $license_version) {
    ?>
    <p>
        <?php _e('You can use More Better Reviews for WooCommerce for free and see you reviews created from you current and new WooCommerce customers and transfer them to Klick Tipp.', 'more-better-reviews-for-woocommerce'); ?>
    </p>

    <p>
        <?php
        $string = __( 'Lifetime free usage of all features with a limitation for <strong>%s reviews</strong> sent per <strong>%s days</strong>.', 'more-better-reviews-for-woocommerce' );
        echo sprintf($string , $user_limit, $days_limit );
        ?>

        <?php _e('You can get a license for unlimited reviews <a href="https://wp2leads-for-klick-tipp.com/web/better-reviews-for-woocommerce-price-list-eng/" target="_blank">here</a>.', 'more-better-reviews-for-woocommerce' ); ?>
    </p>

    <p>
        <?php _e('If you already have your license email and key input them in the form below and click "Activate" button.', 'more-better-reviews-for-woocommerce'); ?>
    </p>
    <?php
}
?>

<form>
    <input type="hidden" name="wtsr_wizard_license_info" value="1">
    <div class="settings-container">
        <div class="settings-item">
            <div class="settings-item-group">
                <div class="settings-item-group-col">
                    <p class="settings-item-label">
                        <?php _e('Email', 'more-better-reviews-for-woocommerce'); ?>
                    </p>
                </div>

                <div class="settings-item-group-col">
                    <div class="settings-item-value">
                        <input class="form-input" name="wtsr_license_email" id="wtsr_license_email" type="email" value="<?php echo $license_info['email']; ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="settings-item">
            <div class="settings-item-group">
                <div class="settings-item-group-col">
                    <p class="settings-item-label">
                        <?php _e('License key', 'more-better-reviews-for-woocommerce'); ?>
                    </p>
                </div>

                <div class="settings-item-group-col">
                    <div class="settings-item-value">
                        <input class="form-input" name="wtsr_license_key" id="wtsr_license_key" type="text" value="<?php echo $activation_in_progress ? $license_info['key'] : $license_info['secured_key']; ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="settings-control">
            <button data-license-action="activate" class="button button-primary wizard-license-action-btn" type="button">
                <?php _e('Activate', 'more-better-reviews-for-woocommerce'); ?>
            </button>

            <button data-license-action="deactivate" class="button button-primary wizard-license-action-btn" type="button">
                <?php _e('Deactivate', 'more-better-reviews-for-woocommerce'); ?>
            </button>

            <?php
            if ($activation_in_progress) {
                ?>
                <button data-license-action="close-license" class="button button-danger wizard-license-action-btn" type="button">
                    <?php _e('Close manage licenses', 'more-better-reviews-for-woocommerce'); ?>
                </button>
                <?php
            }
            ?>
        </div>

        <?php
        if ($activation_in_progress) {
            $site_list = Wtsr_License::get_license_list();

            if ($site_list) {
                ?>
                <div class="settings-item">
                    <p class="settings-item-label"><?php _e('List of sites', 'more-better-reviews-for-woocommerce'); ?></p>

                    <div>
                        <?php
                        if (is_array($site_list)) {
                            ?>
                            <table style="width:100%">
                                <tr>
                                    <td colspan="3">
                                        <p><?php echo __('Total licenses', 'wp2leads') ?>: <strong><?php echo  Wtsr_License::count_licenses(); ?></strong></p>
                                        <p><?php echo __('Available licenses', 'wp2leads') ?>: <strong><?php echo  Wtsr_License::count_licenses(false); ?></strong></p>
                                    </td>
                                </tr>
                                <?php
                                foreach ($site_list as $site) {
                                    if ($site['site_url'] === Wtsr_License::get_current_site()) {
                                        $status = __('Current', 'wp2leads');
                                        $current = true;
                                    } else {
                                        $status = $site['status'] === '1' ? __('Active', 'wp2leads') : __('Disabled', 'wp2leads');
                                        $current = false;
                                    }
                                    ?>
                                    <tr>
                                        <td style="padding: 5px;"><strong><?php echo $site['site_url'] ?></strong></td>
                                        <td style="padding: 5px;"><?php echo $status ?></td>
                                        <td style="padding: 5px;">
                                            <?php
                                            if (!$current) {
                                                ?>
                                                <button class="button wtsr_remove_license button-small button-danger" data-site="<?php echo $site['site_url'] ?>" type="button"><?php _e( 'Remove', 'wp2leads' ) ?></button>
                                                <?php
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </table>
                            <?php
                        } else {
                            echo $site_list;
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
        }
        ?>
    </div>
</form>