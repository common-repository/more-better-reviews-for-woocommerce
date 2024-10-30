<?php
$license_version = Wtsr_License::get_license_version();
$license_info = Wtsr_License::get_lecense_info();
$activation_in_progress = get_transient( 'wtsr_activation_in_progress' );

if ('free' === $license_version) {
    ?>
    <p style="text-align: center">
        <button class="button button-small data-show-toggle-control" data-toggle-target="wtsr_wizard_license_info_form">
            <?php _e("I have a Digistore24 license already!" , 'more-better-reviews-for-woocommerce') ?>
        </button>


        <?php
        if (function_exists('mbrfw_fs')) {

            if (!mbrfw_fs()->is_trial()) {
                $trial_string = __('%s or', 'more-better-reviews-for-woocommerce') . ' ';

                $trial_link = '<a href="'.mbrfw_fs()->get_trial_url().'" class="button button-small button-primary" target="_blank">';
                $trial_link .= __('Start trial here', 'more-better-reviews-for-woocommerce');
                $trial_link .= '</a>';

                echo sprintf($trial_string, $trial_link);
            }

            $upgrade_link = '<a href="'.mbrfw_fs()->get_upgrade_url().'" class="button button-small button-success" target="_blank">';
            $upgrade_link .= __('Buy license', 'more-better-reviews-for-woocommerce');
            $upgrade_link .= '</a>';
            echo $upgrade_link;
        } else {
            ?>
            <a href="<?php echo Wtsr_Settings::get_want_to_buy_license_link(); ?>" class="button button-primary button-small" target="_blank">
                <?php _e("I want to buy a license!" , 'more-better-reviews-for-woocommerce') ?>
            </a>
            <?php
        }
        ?>
    </p>
    <?php
} else {
    if (!function_exists('mbrfw_fs')) {
        ?>
        <p style="text-align: center">
            <button class="button button-primary button-small data-show-toggle-control" data-toggle-target="wtsr_wizard_license_info_form">
                <?php _e("License info" , 'more-better-reviews-for-woocommerce') ?>
            </button>
        </p>
        <?php
    }
}
?>

<div
    id="wtsr_wizard_license_info_form"
    class="data-show-toggle-target<?php echo !empty($activation_in_progress) ? ' active' : '' ?>"
    <?php echo !empty($activation_in_progress) ? '' : ' style="display:none;"' ?>
>
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
                <button data-license-action="activate" class="button button-primary button-small wizard-license-action-btn" type="button">
                    <?php _e('Activate', 'more-better-reviews-for-woocommerce'); ?>
                </button>

                <button data-license-action="deactivate" class="button button-primary button-small wizard-license-action-btn" type="button">
                    <?php _e('Deactivate', 'more-better-reviews-for-woocommerce'); ?>
                </button>

                <?php
                if ($activation_in_progress) {
                    ?>
                    <button data-license-action="close-license" class="button button-danger button-small wizard-license-action-btn" type="button">
                        <?php _e('Close manage licenses', 'more-better-reviews-for-woocommerce'); ?>
                    </button>
                    <?php
                }
                ?>

                <button class="button button-small data-show-toggle-control" data-toggle-target="wtsr_wizard_license_info_form" type="button">
                    <?php _e("Cancel" , 'more-better-reviews-for-woocommerce') ?>
                </button>
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
</div>
