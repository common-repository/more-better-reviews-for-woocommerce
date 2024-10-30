<?php
$license_version = Wtsr_License::get_license_version();
$user_limit = get_option('wtsr_limit_users') * Wtsr_License::get_license_multi();
$days_limit = get_option('wtsr_limit_days');

if ('free' === $license_version) {
    $user_count = get_option('wtsr_limit_count', 0);
    $user_count_timeout = get_option('wtsr_limit_count_timeout', 0);
    $timeout_label = '';

    if (!empty($user_count_timeout)) {
        $user_count_timeout_left = $user_count_timeout - time();

        if ($user_count_timeout_left > 86400) {
            $timeout_left = ceil($user_count_timeout_left / 86400);
            $timeout_label = $timeout_left . ' ' . __(' days', 'more-better-reviews-for-woocommerce');
        } else {
            $timeout_left = ceil($user_count_timeout_left / 3600);
            $timeout_label = $timeout_left . ' ' . __(' hours', 'more-better-reviews-for-woocommerce');
        }
    }

    $user_left = (int) $user_limit - (int) $user_count;

    ?>
    <div class="notice notice-warning inline" style="margin-bottom:15px;">
        <p>
            <strong><?php _e('Better Reviews for WooCommerce', 'more-better-reviews-for-woocommerce') ?></strong>:
            <?php
            $upgrade_url = function_exists('mbrfw_fs') ? mbrfw_fs()->get_upgrade_url() : Wtsr_Settings::get_want_to_buy_license_link();
            $upgrade_link = '<a href="'.$upgrade_url.'" target="_blank">' . __('Buy license', 'more-better-reviews-for-woocommerce') . '</a>';

            if (0 > $user_left || 0 === $user_left) {
                echo __( 'Review request generating stopped!', 'more-better-reviews-for-woocommerce' );

                if (!empty($timeout_label)) {
                    $string = ' ' . __( 'Wait %s without generating new review requests.', 'more-better-reviews-for-woocommerce' );
                    echo sprintf($string, $timeout_label );
                }

                if (function_exists('mbrfw_fs')) {
                    if (!mbrfw_fs()->is_trial()) {
                        $trial_link = '<a href="'.mbrfw_fs()->get_trial_url().'" target="_blank">'. __('trial here', 'more-better-reviews-for-woocommerce'). '</a>';
                        $trial_string = ' ' . __('To continue testing and generate 5 times the amount of current review requests, start %s.', 'more-better-reviews-for-woocommerce');

                        echo sprintf($trial_string, $trial_link);
                    }
                }

                $upgrade_string = '</br>' . __( '%s to generate unlimited review requests instantly.', 'more-better-reviews-for-woocommerce' );
                echo sprintf($upgrade_string, $upgrade_link);
            } else {
                $string = __( 'You have whole functionality, but generating review requests is limited to %s request.', 'more-better-reviews-for-woocommerce' );
                echo sprintf($string , $user_limit );

                if (!empty($user_count)) {
                    $string = ' ' . __( 'You created already %s review request.', 'more-better-reviews-for-woocommerce' );
                    echo sprintf($string , $user_count );
                }

                if (!empty($timeout_label)) {
                    $string = ' ' . __( 'Limit will be reset in %s.', 'more-better-reviews-for-woocommerce' );
                    echo sprintf($string , $timeout_label );
                }

                if (function_exists('mbrfw_fs')) {
                    if (!mbrfw_fs()->is_trial()) {
                        $trial_link = '<a href="'.mbrfw_fs()->get_trial_url().'" target="_blank">'. __('trial here', 'more-better-reviews-for-woocommerce'). '</a>';
                        $trial_string = ' ' . __('To continue testing and generate 5 times the amount of current review requests, start %s.', 'more-better-reviews-for-woocommerce');

                        echo sprintf($trial_string, $trial_link);
                    }
                }

                $upgrade_string = '</br>' . __( '%s to generate unlimited review requests.', 'more-better-reviews-for-woocommerce' );
                echo sprintf($upgrade_string, $upgrade_link);
            }
            ?>
        </p>
    </div>
    <?php
}