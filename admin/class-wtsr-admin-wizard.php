<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      0.0.1
 *
 * @package    Wtsr
 * @subpackage Wtsr/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wtsr
 * @subpackage Wtsr/admin
 * @author     Tobias Conrad <tc@santegra.de>
 */
class Wtsr_Admin_Wizard {

    public function setup_wizard() {
        if (empty($_GET['page']) || 'wp2leads-wtsr' !== $_GET['page']) {
            return;
        }

        $tab = !empty($_GET['tab']) ? $_GET['tab'] : 'settings';

        if (Wtsr_Wizard::is_wizard()) {
            if ('wizard' !== $tab) {
                wp_redirect( esc_url_raw( Wtsr_Wizard::get_wizard_link() ) );
            } else {
                $wizard_step = !empty($_GET['wizard_step']) ? $_GET['wizard_step'] : 'intro';
                $redirect_step = Wtsr_Wizard::get_redirect_wizard_step();

                if ($wizard_step !== $redirect_step) {
                    wp_redirect( esc_url_raw( Wtsr_Wizard::get_wizard_link() ) );
                }
            }
        } else {
            if ('wizard' === $tab) {
                $admin_url = get_admin_url(null, 'admin.php?page=wp2leads-wtsr&tab=settings');
                wp_redirect( esc_url_raw( $admin_url ) );
            }
        }
    }

    public function add_tracking_pixel() {
        if (!empty($_GET['wizard_completed'])) {
            $wizard_tracking_pixels = Wtsr_Wizard::get_tracking_pixels();
            $tracking_pixel = 'wizard_completed_installation';

            if (!empty($wizard_tracking_pixels[$tracking_pixel])) {
                echo $wizard_tracking_pixels[$tracking_pixel]['code'];

                return;
            }
        }

        if (empty($_GET['page']) || empty($_GET['tab']) || 'wp2leads-wtsr' !== $_GET['page'] || 'wizard' !== $_GET['tab']) {
            return;
        }

        $wizard_step = !empty($_GET['wizard_step']) ? $_GET['wizard_step'] : 'intro';

        $required_plugins = Wtsr_Required_Plugins::required_plugins();

        if (!empty($required_plugins)) {
            $wizard_step = 'required_plugin';
        } else {
            if ($wizard_step === 'required_plugin') {
                $wizard_step = 'intro';
            }
        }

        $wizard_tracking_pixels = Wtsr_Wizard::get_tracking_pixels();

        if (!empty($wizard_tracking_pixels['wizard_' . $wizard_step])) {
            echo $wizard_tracking_pixels['wizard_' . $wizard_step]['code'];
        } elseif ('intro' === $wizard_step) {
            $tracking_pixel = 'wizard_intro_en';
            $site_lang = get_user_locale();

            $de_lang_list = array(
                'de_CH_informal',
                'de_DE_formal',
                'de_AT',
                'de_CH',
                'de_DE'
            );

            if (in_array($site_lang, $de_lang_list)) $tracking_pixel = 'wizard_intro_de';

            if (!empty($wizard_tracking_pixels[$tracking_pixel])) {
                echo $wizard_tracking_pixels[$tracking_pixel]['code'];
            }
        }
    }

    public function ajax_activate_woocommerce_only() {
        ReviewServiceManager::activate_woocommerce_only_mode();

        $message = __('Success', 'more-better-reviews-for-woocommerce') . ': ';
        $message .= __('WooCommerce only mode enabled', 'more-better-reviews-for-woocommerce') . '.';
        $params = array('message' => $message, 'reload' => 1);

        Wtsr_Admin_Ajax::success_response($params);
    }

    public function set_ts_credentials() {
        $is_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

        if (!$is_ajax) {
            $is_ts_credential_set = get_transient('wtsr_ts_credential_set');

            if (!$is_ts_credential_set) {
                delete_transient('wtsr_ts_credential_empty_set');
                delete_transient('wtsr_ts_domain_credentials_set');

                delete_option('wtsr_check_ts_credentials_empty');
                delete_option('wtsr_check_ts_credentials');
                delete_option('wtsr_check_ts_domain_credentials');
                // ReviewServiceManager::deactivate_woocommerce_only_mode();

                if (TSManager::is_credentials_empty()) {
                    update_option('wtsr_check_ts_credentials_empty', 1);
                } else {
                    delete_option('wtsr_check_ts_credentials_empty');

                    $check_ts_credentials = TSManager::check_ts_credentials();

                    if (!empty($check_ts_credentials)) {
                        update_option('wtsr_check_ts_credentials', $check_ts_credentials);
                    } else {
                        delete_option('wtsr_check_ts_credentials');

                        $check_ts_domain_credentials = TSManager::check_ts_domain_credentials();

                        if (!empty($check_ts_domain_credentials)) {
                            update_option('wtsr_check_ts_domain_credentials', $check_ts_domain_credentials);
                        } else {
                            delete_option('wtsr_check_ts_domain_credentials');
                        }
                    }
                }

                set_transient('wtsr_ts_credential_set', 1, Wtsr_License::$license_check_timeout);
            }
        }
    }

    public function ajax_set_ts_credentials() {
        if (empty($_POST['formData']) || !is_array($_POST['formData'])) {
            $params = array('message' => __('Cheating, hah?', 'more-better-reviews-for-woocommerce'));
            Wtsr_Admin_Ajax::error_response($params);
        }

        $data = array();

        foreach ($_POST['formData'] as $form_data) {
            $data[sanitize_text_field($form_data['name'])] = sanitize_text_field(trim($form_data['value']));
        }

        $validate_result = Wtsr_Settings::validate_integration_credentials($data);

        if (!empty($validate_result['error_array'])) {
            $error_message = implode(' ', $validate_result['error_array']);
            $params = array('message' => __('Error', 'more-better-reviews-for-woocommerce') . ': ' . $error_message);
            Wtsr_Admin_Ajax::error_response($params);
        }

        Wtsr_Settings::update_integration_credentials($data);

        $params = array(
            'message' => __('Success', 'more-better-reviews-for-woocommerce') . ': ' . __('Credentials saved', 'more-better-reviews-for-woocommerce'),
            'reload' => 1,
        );

        Wtsr_Admin_Ajax::success_response($params);
    }

    public function ajax_save_star_rating_settings() {
        $message = __('Success', 'more-better-reviews-for-woocommerce') . ': ';
        $message .= __('Settings saved', 'more-better-reviews-for-woocommerce');

        if (
            !empty($_POST['wtsr_ts_one_star_link']) &&
            !empty($_POST['wtsr_ts_two_star_link']) &&
            !empty($_POST['wtsr_ts_three_star_link']) &&
            !empty($_POST['wtsr_ts_four_star_link']) &&
            !empty($_POST['wtsr_ts_five_star_link'])
        ) {
            $wtsr_rating_links = array(
                'one_star' => sanitize_text_field( $_POST['wtsr_ts_one_star_link'] ),
                'two_star' => sanitize_text_field( $_POST['wtsr_ts_two_star_link'] ),
                'three_star' => sanitize_text_field( $_POST['wtsr_ts_three_star_link'] ),
                'four_star' => sanitize_text_field( $_POST['wtsr_ts_four_star_link'] ),
                'five_star' => sanitize_text_field( $_POST['wtsr_ts_five_star_link'] ),
            );

            $empty_custom_link = false;

            if ( 'wtsr_custom_link' === $_POST['wtsr_ts_one_star_link'] && !empty($_POST['wtsr_ts_one_star_custom_link'])) {
                $wtsr_rating_links['custom_link']['one_star'] = sanitize_text_field($_POST['wtsr_ts_one_star_custom_link']);
            } elseif ( 'wtsr_custom_link' === $_POST['wtsr_ts_one_star_link'] && empty($_POST['wtsr_ts_one_star_custom_link'])) {
                $empty_custom_link = true;
            } else {
                $wtsr_rating_links['custom_link']['one_star'] = '';
            }

            if ( 'wtsr_custom_link' === $_POST['wtsr_ts_two_star_link'] && !empty($_POST['wtsr_ts_two_star_custom_link'])) {
                $wtsr_rating_links['custom_link']['two_star'] = sanitize_text_field($_POST['wtsr_ts_two_star_custom_link']);
            } elseif ( 'wtsr_custom_link' === $_POST['wtsr_ts_two_star_link'] && empty($_POST['wtsr_ts_two_star_custom_link'])) {
                $empty_custom_link = true;
            } else {
                $wtsr_rating_links['custom_link']['two_star'] = '';
            }

            if ( 'wtsr_custom_link' === $_POST['wtsr_ts_three_star_link'] && !empty($_POST['wtsr_ts_three_star_custom_link'])) {
                $wtsr_rating_links['custom_link']['three_star'] = sanitize_text_field($_POST['wtsr_ts_three_star_custom_link']);
            } elseif ( 'wtsr_custom_link' === $_POST['wtsr_ts_three_star_link'] && empty($_POST['wtsr_ts_three_star_custom_link'])) {
                $empty_custom_link = true;
            } else {
                $wtsr_rating_links['custom_link']['three_star'] = '';
            }

            if ( 'wtsr_custom_link' === $_POST['wtsr_ts_four_star_link'] && !empty($_POST['wtsr_ts_four_star_custom_link'])) {
                $wtsr_rating_links['custom_link']['four_star'] = sanitize_text_field($_POST['wtsr_ts_four_star_custom_link']);
            } elseif ( 'wtsr_custom_link' === $_POST['wtsr_ts_four_star_link'] && empty($_POST['wtsr_ts_four_star_custom_link'])) {
                $empty_custom_link = true;
            } else {
                $wtsr_rating_links['custom_link']['four_star'] = '';
            }

            if ( 'wtsr_custom_link' === $_POST['wtsr_ts_five_star_link'] && !empty($_POST['wtsr_ts_five_star_custom_link'])) {
                $wtsr_rating_links['custom_link']['five_star'] = sanitize_text_field($_POST['wtsr_ts_five_star_custom_link']);
            } elseif ( 'wtsr_custom_link' === $_POST['wtsr_ts_five_star_link'] && empty($_POST['wtsr_ts_five_star_custom_link'])) {
                $empty_custom_link = true;
            } else {
                $wtsr_rating_links['custom_link']['five_star'] = '';
            }

            if ($empty_custom_link) {
                $message = __('Error', 'more-better-reviews-for-woocommerce') . ': ';
                $message .= __('Custom link field should not be empty', 'more-better-reviews-for-woocommerce');

                $response = array('success' => 0, 'error' => 1, 'message' => $message );

                echo json_encode($response);
                wp_die();
            }

            delete_option( 'wtsr_rating_links' );
            add_option( 'wtsr_rating_links', $wtsr_rating_links );
        }

        $response = array('success' => 1, 'error' => 0, 'message' => $message );

        echo json_encode($response);
        wp_die();
    }

    public function ajax_save_email_template_settings() {
        $message = __('Success', 'more-better-reviews-for-woocommerce') . ': ';
        $message .= __('Settings saved', 'more-better-reviews-for-woocommerce');

        $wtsr_email_template_editor = TSManager::get_default_email_template_editor();
        $wtsr_button_colors = TSManager::get_button_colors();

        if (!empty($_POST['wtsr_email_template'])) {
            delete_option( 'wtsr_email_template' );
            add_option( 'wtsr_email_template', wp_kses_post( $_POST['wtsr_email_template'] ) );
        } else {
            delete_option( 'wtsr_email_template' );
            add_option( 'wtsr_email_template', wp_kses_post( $wtsr_email_template_editor ) );
        }

        if (!empty($_POST['wtsr_button_bg_color'])) {
            delete_option( 'wtsr_button_bg_color' );
            add_option( 'wtsr_button_bg_color', sanitize_text_field( $_POST['wtsr_button_bg_color'] ) );
        } else {
            delete_option( 'wtsr_button_bg_color' );
            add_option( 'wtsr_button_bg_color', $wtsr_button_colors['bg_color'] );
        }

        if (!empty($_POST['wtsr_button_text_color'])) {
            delete_option( 'wtsr_button_text_color' );
            add_option( 'wtsr_button_text_color', sanitize_text_field( $_POST['wtsr_button_text_color'] ) );
        } else {
            delete_option( 'wtsr_button_text_color' );
            add_option( 'wtsr_button_text_color', $wtsr_button_colors['text_color'] );
        }

        if (!empty($_POST['wtsr_image_size'])) {
            delete_option( 'wtsr_image_size' );
            add_option( 'wtsr_image_size', sanitize_text_field( $_POST['wtsr_image_size'] ) );
        } else {
            delete_option( 'wtsr_image_size' );
            add_option( 'wtsr_image_size', TSManager::get_default_image_size() );
        }

        $response = array('success' => 1, 'error' => 0, 'message' => $message );

        echo json_encode($response);
        wp_die();
    }

    public function ajax_save_general_settings() {
        $message = __('Success', 'more-better-reviews-for-woocommerce') . ': ';
        $message .= __('Settings saved', 'more-better-reviews-for-woocommerce');

        if (!empty($_POST['wtsr_review_ask'])) {
            $wtsr_review_ask = sanitize_text_field( $_POST['wtsr_review_ask'] );

            if (!empty($wtsr_review_ask)) {
                delete_option( 'wtsr_review_ask' );
                add_option( 'wtsr_review_ask', $wtsr_review_ask );
            }
        }

        if (!empty($_POST['wtsr_review_ask_template_editor'])) {
            $wtsr_review_ask_template_editor = $_POST['wtsr_review_ask_template_editor'];

            if (!empty($wtsr_review_ask_template_editor)) {
                delete_option( 'wtsr_review_ask_template_editor' );
                add_option( 'wtsr_review_ask_template_editor', wp_kses_post( $wtsr_review_ask_template_editor ) );
            }
        } else {
            add_option( 'wtsr_review_ask_template_editor', '' );
        }

        if (!empty($_POST['wtsr_filter_email_domain'])) {
            $wtsr_filter_email_domain = sanitize_textarea_field( $_POST['wtsr_filter_email_domain'] );

            if (!empty($wtsr_filter_email_domain)) {
                $wtsr_filter_email_domain = explode("\r\n", $wtsr_filter_email_domain);

                delete_option( 'wtsr_filter_email_domain' );
                add_option( 'wtsr_filter_email_domain', $wtsr_filter_email_domain );
            }
        } else {
            add_option( 'wtsr_filter_email_domain', '' );
        }

        if (!empty($_POST['wtsr_review_period'])) {
            $wtsr_review_period = sanitize_text_field( $_POST['wtsr_review_period'] );

            if (!empty($wtsr_review_period)) {
                delete_option( 'wtsr_review_period' );
                add_option( 'wtsr_review_period', $wtsr_review_period );
            }
        } else {
            add_option( 'wtsr_review_period', '30' );
        }

        if (!empty($_POST['wtsr_order_status'])) {
            $wtsr_order_status = sanitize_text_field( $_POST['wtsr_order_status'] );

            if (!empty($wtsr_order_status)) {
                delete_option( 'wtsr_order_status' );
                add_option( 'wtsr_order_status', $wtsr_order_status );
            }
        }

        if (!empty($_POST['wtsr_review_variations'])) {
            $wtsr_review_variations = sanitize_text_field( $_POST['wtsr_review_variations'] );

            if (!empty($wtsr_review_variations)) {
                delete_option( 'wtsr_review_variations' );
                add_option( 'wtsr_review_variations', $wtsr_review_variations );
            }
        }

        if (!empty($_POST['wtsr_review_approved'])) {
            delete_option( 'wtsr_review_approved' );
            add_option( 'wtsr_review_approved', 'yes' );
        } else {
            delete_option( 'wtsr_review_approved' );
            add_option( 'wtsr_review_approved', 'no' );
        }

        $response = array('success' => 1, 'error' => 0, 'message' => $message );

        echo json_encode($response);
        wp_die();
    }

    public function ajax_generate_dummy_orders() {
        $site = Wtsr_License::get_current_site();
        $selected_order_status = get_option('wtsr_order_status', 'wc-completed');

        $status = !empty($_POST['status']) ? str_replace('wc-', '', sanitize_text_field($_POST['status'])) : str_replace('wc-', '', $selected_order_status);

        $dummy_statuses = TSManager::get_dummy_order_statuses();

        if (!in_array($status, $dummy_statuses)) {
            $status = 'completed';
        }

        $order = TSManager::generate_dummy_orders($site, true, 'checkout', $status);
        $order = TSManager::generate_dummy_orders($site, true, 'checkout', $status);
        $order = TSManager::generate_dummy_orders($site, true, 'checkout', $status);
        $order = TSManager::generate_dummy_orders($site, true, 'checkout', $status);
        $order = TSManager::generate_dummy_orders($site, true, 'checkout', $status);

        $message = __('Success', 'more-better-reviews-for-woocommerce') . ': ';

        $message .= __('5 orders generated successfully', 'more-better-reviews-for-woocommerce') . '.';

        $response = array('success' => 1, 'error' => 0, 'message' => $message );

        echo json_encode($response);
        wp_die();
    }

    public function ajax_import_activate_map() {
        $mapids = array();
        $mapids[] = '758b64026691b9bfa0b81f3aa2267c4a';

        if (class_exists('MapBuilderManager')) {
            $maps_from_server = MapBuilderManager::import_maps_from_server($mapids);

            if (empty($maps_from_server)) {
                $message = __('Error', 'more-better-reviews-for-woocommerce') . ': ';
                $message .= __("Map can't be imported from server", 'more-better-reviews-for-woocommerce') . '.';

                $response = array('success' => 0, 'error' => 1, 'message' => $message );

                echo json_encode($response);
                wp_die();
            }

            $last_map_id = $maps_from_server[0];

            $map_id = $last_map_id;
            $module_key = 'wtsr_review_request';
            $module_status = true;

            if (class_exists('Wp2leads_Transfer_Modules')) {
                $result = Wp2leads_Transfer_Modules::save_module_map($map_id, $module_key, $module_status);

                if (empty($result) || !empty($result['error'])) {
                    $message = __('Error', 'more-better-reviews-for-woocommerce') . ': ';
                    $message .= __("Map imported from server but we can't activate transfer module", 'more-better-reviews-for-woocommerce') . '.';

                    $response = array('success' => 0, 'error' => 1, 'message' => $message );

                    echo json_encode($response);
                    wp_die();
                }
            }
        } else {
            if (empty($maps_from_server)) {
                $message = __('Error', 'more-better-reviews-for-woocommerce') . ': ';
                $message .= __("Map can't be imported from server", 'more-better-reviews-for-woocommerce') . '.';

                $response = array('success' => 0, 'error' => 1, 'message' => $message );

                echo json_encode($response);
                wp_die();
            }
        }

        $message = __('Success', 'more-better-reviews-for-woocommerce') . ': ';
        $message .= __('Map Imported and activated successfully', 'more-better-reviews-for-woocommerce') . '.';

        $response = array('success' => 1, 'error' => 0, 'message' => $message );

        echo json_encode($response);
        wp_die();
    }

    public function ajax_activate_map() {
        $map_id = !empty($_POST['map_id']) ? sanitize_text_field($_POST['map_id']) : false;

        if (empty($map_id)) {
            $message = __('Error', 'more-better-reviews-for-woocommerce') . ': ';
            $message .= __("No map ID selected", 'more-better-reviews-for-woocommerce') . '.';

            $response = array('success' => 0, 'error' => 1, 'message' => $message );

            echo json_encode($response);
            wp_die();
        }
        $module_key = 'wtsr_review_request';
        $module_status = true;

        if (class_exists('Wp2leads_Transfer_Modules')) {
            $result = Wp2leads_Transfer_Modules::save_module_map($map_id, $module_key, $module_status);

            if (empty($result) || !empty($result['error'])) {
                $message = __('Error', 'more-better-reviews-for-woocommerce') . ': ';
                $message .= __("We can't activate transfer module for this map", 'more-better-reviews-for-woocommerce') . '.';

                $response = array('success' => 0, 'error' => 1, 'message' => $message );

                echo json_encode($response);
                wp_die();
            }
        }

        $message = __('Success', 'more-better-reviews-for-woocommerce') . ': ';
        $message .= __('Map Activated successfully', 'more-better-reviews-for-woocommerce') . '.';

        $response = array('success' => 1, 'error' => 0, 'message' => $message );

        echo json_encode($response);
        wp_die();
    }

    public function save_settings() {
        // License info
        if (!empty($_POST['wtsr_wizard_license_info'])) {

        }
    }
}
