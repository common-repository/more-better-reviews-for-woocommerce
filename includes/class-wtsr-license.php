<?php

/**
 * Fired during plugin activation
 *
 * @since      0.0.1
 *
 * @package    Wtsr
 * @subpackage Wtsr/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.0.1
 * @package    Wtsr
 * @subpackage Wtsr/includes
 * @author     Tobias Conrad <tc@santegra.de>
 */
class Wtsr_License {
    public static $license_trial_multi = 5;
    public static $license_check_timeout = 6 * 60 * 60;
    public static $license_not_active_timeout = 48 * 60 * 60;
    public static $license_no_server_response_timeout = 48 * 60 * 60;

    public static function server_request($license_email, $license_key, $site = '', $event = 'test') {
        if ( !$site ) {
            $site = self::get_current_site();
        }

        $parameters = 'license_email='.$license_email.'&license_key='.$license_key.'&site_url='.$site.'&event='.$event.'&plugin_product=wtsr';

        $request = wp_remote_get(
            base64_decode(
                'aHR0cHM6Ly93cDJsZWFkcy1mb3Ita2xpY2stdGlwcC5jb20vc2VydmVyL3dwMmxlYWRfY2hlY2tfbGljZW5zZS5waHA='
            ) . '?' . $parameters
        );

        if (is_wp_error($request)) {
            return false;
        }

        $response_code = $request['response']['code'];

        if (200 !== $response_code) {
            return false;
        }

        $response = json_decode(wp_remote_retrieve_body( $request ), true);

        return $response;
    }

    /**
     *
     */
    public static function get_lecense_info() {
        $license_info_default = array(
            'email' =>  '',
            'key'   =>  '',
            'secured_key'   =>  '',
            'version'   =>  '',
        );

        $license_info = get_option('wtsr_license', $license_info_default);

        return $license_info;
    }

    public static function clean_lecense_info() {
        $license_info = array(
            'email' =>  '',
            'key'   =>  '',
            'secured_key'   =>  '',
            'version'   =>  '',
        );

        update_option('wtsr_license', $license_info);
    }

    public static function get_license_version() {
        if (function_exists('mbrfw_fs')) {
            if (mbrfw_fs()->is_paying__fs__()) {
                return  'pro';
            }

            if (mbrfw_fs()->is_paying()) {
                return  'pro';
            }
        }

        $license_info = self::get_lecense_info();

        if (!empty($license_info['version'])) {
            return 'pro';
        }

        return 'free';
    }

    public static function get_license_version_label() {
        if (function_exists('mbrfw_fs')) {
            if (mbrfw_fs()->is_trial()) {
                return __('Trial', 'more-better-reviews-for-woocommerce');
            }
            if (mbrfw_fs()->is_paying()) {
                return __('Professional', 'more-better-reviews-for-woocommerce');
            }
        }

        $license_version = self::get_license_version();

        if ('pro' === $license_version) {
            return __('Professional', 'more-better-reviews-for-woocommerce');
        } else {
            return __('Free', 'more-better-reviews-for-woocommerce');
        }
    }

    public static function check_license() {
        $license_info = Wtsr_License::get_lecense_info();

        $result = Wtsr_License::server_request(
            $license_info['email'],
            $license_info['key'],
            '',
            'check'
        );

        return $result;
    }

    public static function set_license() {
        $license_info = Wtsr_License::get_lecense_info();

        $license_not_active = get_transient('wtsr_license_not_active');
        $license_not_active_timeout = get_transient('wtsr_license_not_active_timeout');

        $no_server_response = get_transient('wtsr_no_server_response');
        $no_server_response_timeout = get_transient('wtsr_no_server_response_timeout');

        $result = Wtsr_License::server_request($license_info['email'], $license_info['key'], '', 'check');

        if (!$result) {
            if (!$no_server_response && !$no_server_response_timeout) {
                set_transient('wtsr_no_server_response', 1);
                set_transient('wtsr_no_server_response_timeout', 1, self::$license_no_server_response_timeout);
            } elseif ($no_server_response && !$no_server_response_timeout) {
                delete_transient('wtsr_no_server_response');
                delete_transient('wtsr_license_not_active');
                delete_transient('wtsr_license_not_active_timeout');

                self::clean_lecense_info();

                do_action('wtsr_license_version_changed');
            }
        } else {
            delete_transient('wtsr_no_server_response');
            delete_transient('wtsr_no_server_response_timeout');

            if (200 === $result['code']) {
                delete_transient('wtsr_license_not_active');
                delete_transient('wtsr_license_not_active_timeout');

                $license_new = array(
                    'email'         =>  $license_info['email'],
                    'key'           =>  $license_info['key'],
                    'secured_key'   =>  $license_info['secured_key'],
                    'version'       =>  $result['body']['version'],
                );

                update_option('wtsr_license', $license_new);
                do_action('wtsr_license_version_changed');
            } elseif (402 === $result['code']) {
                if (!$license_not_active && !$license_not_active_timeout) {
                    set_transient('wtsr_license_not_active', 1);
                    set_transient('wtsr_license_not_active_timeout', 1, self::$license_not_active_timeout);

                    $license_new = array(
                        'email'         =>  $license_info['email'],
                        'key'           =>  $license_info['key'],
                        'secured_key'   =>  $license_info['secured_key'],
                        'version'       =>  '',
                    );

                    update_option('wtsr_license', $license_new);
                    do_action('wtsr_license_version_changed');
                }

                if ($license_not_active && $license_not_active_timeout) {
                    $license_new = array(
                        'email'         =>  $license_info['email'],
                        'key'           =>  $license_info['key'],
                        'secured_key'   =>  $license_info['secured_key'],
                        'version'       =>  '',
                    );

                    update_option('wtsr_license', $license_new);
                    do_action('wtsr_license_version_changed');
                }

                if ($license_not_active && !$license_not_active_timeout) {
                    delete_transient('wtsr_license_not_active');
                    delete_transient('wtsr_license_not_active_timeout');
                    self::clean_lecense_info();
                    do_action('wtsr_license_version_changed');
                }
            } else {
                delete_transient('wtsr_license_not_active');
                delete_transient('wtsr_license_not_active_timeout');
                self::clean_lecense_info();
                do_action('wtsr_license_version_changed');
            }
        }

        return true;
    }

    public static function activate_license($license_email, $license_key) {
        $license_info = Wtsr_License::get_lecense_info();

        if ($license_key === $license_info['secured_key']) {
            return array('error' => 1, 'success' => 0, 'message' => __('Error: This action are not allowed, please enter correct license email and license key', 'more-better-reviews-for-woocommerce'));
        }

        // $result = Wtsr_License_Fake::server_request($license_email, $license_key, '', 'activate');
        $result = Wtsr_License::server_request($license_email, $license_key, '', 'activate');

        if (!$result) {
            return array('success' => 0, 'error' => 1, 'message' => __('Error: No server response.', 'more-better-reviews-for-woocommerce'));
        } else {
            $secured_key = Wtsr_License::get_secure_license_key($license_key);

            if ($result['code'] >= 200 && $result['code'] < 300) {
                set_transient('wtsr_activation_in_progress', 1);
                set_transient('wtsr_activation_in_progress_timeout', 1, 3 * 60);

                $license_new = array(
                    'email' =>  $license_email,
                    'key'   =>  $license_key,
                    'secured_key'   =>  $secured_key,
                    'version'   =>  ''
                );

                if (204 === $result['code']) {
                    $message = __('Warning: There is no available licenses, you can move license key from another site.', 'more-better-reviews-for-woocommerce');
                } elseif (200 === $result['code']) {
                    $license_new['version'] = $result['body']['version'];
                    $message = __('Success: Your product activated', 'more-better-reviews-for-woocommerce');
                }

                update_option('wtsr_license', $license_new);
                do_action('wtsr_license_version_changed');

                delete_transient('wtsr_license_not_active');
                delete_transient('wtsr_license_not_active_timeout');
                delete_transient('wtsr_no_server_response');
                delete_transient('wtsr_no_server_response_timeout');


                return array('success' => 1, 'error' => 0, 'message' => $message);
            } else {
                return array('success' => 0, 'error' => 1, 'message' => __('Activation failed: Your license are currently inactive. Possible reason: Missing payment.', 'more-better-reviews-for-woocommerce'));
            }
        }
    }

    public static function deactivate_license($license_email, $license_key) {
        $license_info = Wtsr_License::get_lecense_info();
        $result = Wtsr_License::server_request($license_info['email'], $license_info['key'], '', 'deactivate');

        if (!$result) {
            return array('success' => 0, 'error' => 1, 'message' => __('Error: No server response.', 'more-better-reviews-for-woocommerce'));
        } else {
            if (200 === $result['code']) {
                self::clean_lecense_info();

                do_action('wtsr_license_version_changed');

                return array('success' => 1, 'error' => 0, 'message' => __('Success: Your product deactivated', 'more-better-reviews-for-woocommerce') );
            } else {
                update_option('wtsr_license', $license_info);

                return array('success' => 0, 'error' => 1, 'message' => __('Error: Deactivation failed', 'more-better-reviews-for-woocommerce') );
            }
        }
    }

    public static function close_license() {
        $license_info = get_option('wtsr_license');
        $license_info['key'] = md5 ( $license_info['key'] );

        $result = update_option('wtsr_license', $license_info);

        if ($result) {
            delete_transient( 'wtsr_activation_in_progress' );

            return array('success' => 1, 'error' => 0, 'message' => __('Success: Activation completed', 'more-better-reviews-for-woocommerce'));
        }

        return array('success' => 0, 'error' => 1, 'message' => __('Error: Activation not completed', 'more-better-reviews-for-woocommerce'));
    }

    public static function remove_license($license_email, $license_key, $license_site) {
        $result = Wtsr_License::server_request($license_email, $license_key, $license_site, 'delete');

        if (200 === $result['code']) {
            return array('error' => 0, 'success' => 1, 'message' => __('Success: Site have been deleted', 'more-better-reviews-for-woocommerce'));
        } else {
            return array('error' => 1, 'success' => 0, 'message' => __('Error: Deactivation failed', 'more-better-reviews-for-woocommerce'));
        }
    }

    public static function get_license_list() {
        $license_info = Wtsr_License::get_lecense_info();

        // $result = Wtsr_License_Fake::server_request($license_info['email'], $license_info['key'], '', 'get_all');
        $result = Wtsr_License::server_request($license_info['email'], $license_info['key'], '', 'get_all');

        if (200 === $result['code']) {
            return $result['body']['site_list'];
        } elseif (204 === $result['code']) {
            return __('No active sites');
        } else {
            return false;
        }
    }

    public static function count_licenses($total = true) {
        $license_info = Wtsr_License::get_lecense_info();

        $event = $total ? 'count_all' : 'count_available';

        // $result = Wtsr_License_Fake::server_request($license_info['email'], $license_info['key'], '', $event);
        $result = Wtsr_License::server_request($license_info['email'], $license_info['key'], '', $event);

        if (200 === $result['code']) {
            return $result['body']['total'];
        } else {
            return 0;
        }
    }

    public static function licenses_notices() {
        $activation_in_progress = get_transient('wtsr_activation_in_progress');
        $license_not_active = get_transient('wtsr_license_not_active');
        $license_version = self::get_license_version();

        if ($activation_in_progress) {
            ?>
            <div class="notice notice-error">
                <p>
                    <strong><?php _e('Better Reviews for WooCommerce', 'more-better-reviews-for-woocommerce') ?></strong>:
                    <?php _e('Your activation is in progress. You need to Complete this to securely save your license data.', 'more-better-reviews-for-woocommerce') ?>
                    <a href="?page=wp2leads-wtsr&tab=settings"><?php _e('Open settings tab', 'more-better-reviews-for-woocommerce') ?></a>
                </p>
            </div>
            <?php
        }

        if ($license_not_active) {
            ?>
            <div class="notice notice-error">
                <p>
                    <strong><?php _e('Better Reviews for WooCommerce', 'more-better-reviews-for-woocommerce') ?></strong>:
                    <?php _e('Your license will stop soon, because of an issue with payment. Please check your emails from support@digistore24.com!', 'more-better-reviews-for-woocommerce') ?>
                </p>
            </div>
            <?php
        }

        if ('free' === $license_version) {
            $user_limit = get_option('wtsr_limit_users') * Wtsr_License::get_license_multi();
            $days_limit = get_option('wtsr_limit_days');
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
            <div class="notice notice-warning">
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
    }

    /**
     * @return null|string|string[]
     */
    public static function get_current_site() {
        $site_url = $_SERVER['HTTP_HOST'];
        $site_url = preg_replace('/^http:\/\//i', "", $site_url );
        $site_url = preg_replace('/^https:\/\//i', "", $site_url );
        $site_url = preg_replace('/^www./i', "", $site_url );

        return $site_url;
    }

    public static function get_secure_license_key( $license_key ) {
        $parts = explode('-', $license_key);

        foreach ($parts as $index => $part) {
            if ($index >= 2 && $index <= 5) {
                $parts[$index] = 'XXXXX';
            }
        }

        return implode('-', $parts);
    }

    public static function is_trial() {
        if (!function_exists('mbrfw_fs')) {
            return false;
        }

        if (mbrfw_fs()->is_trial()) {
            return  true;
        }

        return false;
    }

    public static function get_license_multi() {
        if (Wtsr_License::is_trial()) {
            return Wtsr_License::$license_trial_multi;
        }

        return 1;
    }
}