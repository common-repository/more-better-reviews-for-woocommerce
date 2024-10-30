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
 * Settings wizard class
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.8
 * @package    Wtsr
 * @subpackage Wtsr/includes
 * @author     Tobias Conrad <tc@santegra.de>
 */
class Wtsr_Wizard {
    public static function is_wizard() {
        // return false;

        $required_plugins = Wtsr_Required_Plugins::required_plugins();

        if (!empty($required_plugins)) {
            return true;
        }

        // return false;
        $wizard_completed = get_option('wtsr_wizard_completed', false);

        if ($wizard_completed) {
            return false;
        }

        $wizard_step = get_option('wtsr_wizard_step', false);

        if ($wizard_step) {
            return true;
        }

        $wtsr_options = TSManager::get_wtsr_options_labels();

        foreach ($wtsr_options as $wtsr_option) {
            global $wpdb;

            $option = $wpdb->get_row("SELECT option_id FROM {$wpdb->options} WHERE option_name LIKE '%{$wtsr_option}%'");

            if (!empty($option)) {
                update_option('wtsr_wizard_completed', 1);
                return false;
            }
        }

        return true;
    }

    public static function get_wizard_link() {
        $required_plugins = Wtsr_Required_Plugins::required_plugins();
        $redirect_step = Wtsr_Wizard::get_redirect_wizard_step();
        $admin_url = get_admin_url(null, 'admin.php?page=wp2leads-wtsr&tab=wizard&wizard_step=' . $redirect_step);

        if (!empty($required_plugins)) {
            $admin_url = get_admin_url(null, 'admin.php?page=wp2leads-wtsr&tab=wizard&wizard_step=required_plugin');
        }

        return $admin_url;
    }

    public static function get_wizard_steps() {
        $wizard_steps = array(
            'required_plugin' => array(
                'title' => __('Required plugins installation', 'more-better-reviews-for-woocommerce'),
                'required' => true,
            ),
            'intro' => array(
                //'title' => __('Welcome to installation Wizard', 'more-better-reviews-for-woocommerce'),
                'required' => true
            ),
//            'license_info' => array(
//                'title' => __('Information about license', 'more-better-reviews-for-woocommerce'),
//                'required' => false
//            ),
            'ts_credentials' => array(
                // 'title' => __('Trusted Shops credentials', 'more-better-reviews-for-woocommerce') . ' ' . __('(optional)', 'more-better-reviews-for-woocommerce'),
                'title' => __('Reviews services integration', 'more-better-reviews-for-woocommerce') . ' ' . __('(optional)', 'more-better-reviews-for-woocommerce'),
                'required' => false
            ),
            'general_settings' => array(
                'title' => __('General settings', 'more-better-reviews-for-woocommerce'),
                'required' => false
            ),
            'email_send_via' => array(
                'title' => __('Select service for mailing', 'more-better-reviews-for-woocommerce'),
                'required' => false
            ),
            'html_template' => array(
                'title' => __('Email template settings', 'more-better-reviews-for-woocommerce'),
                'required' => false
            ),
            'rating_stars' => array(
                'title' => __('Rating stars link settings', 'more-better-reviews-for-woocommerce'),
                'required' => false
            ),
            'all_in_one_page' => array(
                'title' => __('All-In-One Reviews page settings', 'more-better-reviews-for-woocommerce'),
                'required' => false
            ),
            'thankyou_settings' => array(
                'title' => __('Thank you email settings', 'more-better-reviews-for-woocommerce'),
                'required' => false
            ),
            'generate_reviews' => array(
                'title' => __('Generate requests', 'more-better-reviews-for-woocommerce'),
                'required' => false
            ),
            'reviews_list' => array(
                'title' => __('Reviews list', 'more-better-reviews-for-woocommerce'),
                'required' => false
            ),
            'start_transfering' => array(
                'title' => __('Start transferring', 'more-better-reviews-for-woocommerce'),
                'required' => false
            ),
        );

        if (empty(Wtsr_Required_Plugins::required_plugins())) {
            unset($wizard_steps['required_plugin']);
        }

        return $wizard_steps;
    }

    public static function get_redirect_wizard_step() {
        $step = get_option('wtsr_wizard_step', 'intro');

        if (!empty(Wtsr_Required_Plugins::required_plugins())) {
            $step = 'required_plugin';
        }

        return $step;
    }

    public static function complete_wizard() {
        // self::save_default_settings();

        delete_option('wtsr_wizard_step');
        update_option('wtsr_wizard_completed', 1);

        $reviews = ReviewsModel::get_all();

        if (!empty($reviews)) {
            $required_plugins_wp2leads = Wtsr_Required_Plugins::get_required_plugins_wp2leads();
            $is_wp2leads_installed = Wtsr_Required_Plugins::is_plugin_installed( $required_plugins_wp2leads['slug'] ) && Wtsr_Required_Plugins::is_plugin_active( $required_plugins_wp2leads['slug'] );

            if ($is_wp2leads_installed) {
                $default_send_via = 'klick-tipp';
            } else {
                $default_send_via = 'woocommerce';
            }

            $wtsr_email_send_via = get_option('wtsr_email_send_via', $default_send_via);

            if ('woocommerce' === $wtsr_email_send_via) {
                return get_admin_url(null, 'admin.php?page=wp2leads-wtsr&wizard_completed=1');
            } else {
                $is_wtsr_map_exists = TSManager::is_wtsr_map_exists();

                if (!empty($is_wtsr_map_exists) && !empty($is_wtsr_map_exists[0]["id"])) {
                    return get_admin_url(null, 'admin.php?page=wp2l-admin&tab=map_to_api&active_mapping=' . $is_wtsr_map_exists[0]["id"].'&wizard_completed=1');
                } else {
                    return get_admin_url(null, 'admin.php?page=wp2l-admin&tab=map_port&wizard_completed=1');
                }
            }
        } else {
            return get_admin_url(null, 'admin.php?page=wp2leads-wtsr&wizard_completed=1');
        }
    }

    public static function save_default_settings() {
        $wtsr_license = get_option('wtsr_license', false);

        if (empty($wtsr_license)) {
            $license_info = array(
                'email' =>  '',
                'key'   =>  '',
                'secured_key'   =>  '',
                'version'   =>  '',
            );

            update_option('wtsr_license', $license_info);
        }

        $ts_id = get_option('wtsr_ts_id', '');
        $ts_email = get_option('wtsr_ts_email', '');
        $ts_password = get_option('wtsr_ts_password', '');

        if (empty($ts_id) || empty($ts_email) || empty($ts_password)) {
            if (empty($ts_id)) {
                update_option('wtsr_ts_id', '');
            }

            if (empty($ts_email)) {
                update_option('wtsr_ts_email', '');
            }

            if (empty($ts_password)) {
                update_option('wtsr_ts_password', '');
            }

            // TODO: Save Woocommerce only mode
        }

        $selected_review_period = get_option('wtsr_review_period', false);

        if (empty($selected_review_period)) {
            update_option('wtsr_review_period', '30');
        }

        $selected_filter_email_domain = get_option('wtsr_filter_email_domain', false);

        if (empty($selected_filter_email_domain)) {
            update_option('wtsr_filter_email_domain', '');
        }

        $selected_review_ask = get_option('wtsr_review_ask', false);

        if (empty($selected_review_ask)) {
            update_option('wtsr_review_ask', 'no');
        }
    }

    public static function clean_wtsr_options() {
        $wtsr_options = TSManager::get_wtsr_options_labels();

        foreach ($wtsr_options as $wtsr_option) {
            delete_option($wtsr_option);
        }

        delete_option('wtsr_wizard_completed');

        update_option('wtsr_wizard_step', 'intro');
    }

    public static function activate_wizard() {
        self::clean_wtsr_options();

        return true;
    }

    public static function get_tracking_pixels() {
        $tracking_pixels = array(
            'wizard_intro_de' => array(
                'code' => "<img src='https://klick.santegra-international.com/pix/1ilez7uazfz3893' height='1' width='1' />"
            ),
            'wizard_intro_en' => array(
                'code' => "<img src='https://klick.santegra-international.com/pix/1iljz7uazfzc24a' height='1' width='1' />"
            ),
            'wizard_ts_credentials' => array(
                'code' => "<img src='https://klick.santegra-international.com/pix/1ilmz7uazfz4516' height='1' width='1' />"
            ),
            'wizard_general_settings' => array(
                'code' => "<img src='https://klick.santegra-international.com/pix/1ilnz7uazfz257d' height='1' width='1' />"
            ),
            'wizard_email_send_via' => array(
                'code' => "<img src='https://klick.santegra-international.com/pix/1iloz7uazfz455c' height='1' width='1' />"
            ),
            'wizard_html_template' => array(
                'code' => "<img src='https://klick.santegra-international.com/pix/1ilpz7uazfz86c1' height='1' width='1' />"
            ),
            'wizard_rating_stars' => array(
                'code' => "<img src='https://klick.santegra-international.com/pix/1ilqz7uazfz1d64' height='1' width='1' />"
            ),
            'wizard_all_in_one_page' => array(
                'code' => "<img src='https://klick.santegra-international.com/pix/1ilrz7uazfz32df' height='1' width='1' />"
            ),
            'wizard_generate_reviews' => array(
                'code' => "<img src='https://klick.santegra-international.com/pix/1ilsz7uazfz21ed' height='1' width='1' />"
            ),
            'wizard_reviews_list' => array(
                'code' => "<img src='https://klick.santegra-international.com/pix/1iltz7uazfz00e5' height='1' width='1' />"
            ),
            'wizard_start_transfering' => array(
                'code' => "<img src='https://klick.santegra-international.com/pix/1iluz7uazfz5afa' height='1' width='1' />"
            ),
            'wizard_completed_installation' => array(
                'code' => "<img src='https://klick.santegra-international.com/pix/1ilvz7uazfz37a8' height='1' width='1' />"
            ),
        );

        return $tracking_pixels;
    }
}