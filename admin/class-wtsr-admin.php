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
class Wtsr_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    0.0.1
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    0.0.1
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    0.0.1
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Wordpress init.
     *
     * @since    0.0.1
     */
    public function init() {
        $is_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

        if (!$is_ajax) {
            self::check_version();
        }

        add_action( 'wp2leads_transfer_user_created', array($this, 'user_transfered_to_kt'), 15, 5 );
        add_action( 'wp2leads_transfer_user_updated', array($this, 'user_transfered_to_kt'), 15, 5 );
    }

    public function add_plugin_screen_link($links)
    {
        $links[] = '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=wp2leads-wtsr')) . '">'. __( "Start", 'wp2leads-wtsr' ) .'</a>';
        return $links;
    }

    public function display_post_states($post_states, $post) {
        $wtsr_all_reviews_page = get_option('wtsr_all_reviews_page', false);

        if( !empty($wtsr_all_reviews_page) && $post->ID == $wtsr_all_reviews_page ) {
            $post_states[] = __('Custom all products review page', 'more-better-reviews-for-woocommerce');
        }

        return $post_states;
    }

    public function rest_api_init() {
        $controller = new Wtsr_Rest;
        $controller->register_routes();
    }

    public function license_version_changed() {
        $license_version = Wtsr_License::get_license_version();

        if ('pro' === $license_version) {
            TSManager::remove_reviews_limit_counter();
        }
    }

    public function new_product_review($comment_ID, $comment_approved, $commentdata) {
        $wtsr_review_approved = get_option('wtsr_review_approved', 'yes');

        if (!empty($comment_approved) || 'no' === $wtsr_review_approved) {
            $comment = get_comment( $comment_ID );

            $email = $comment->comment_author_email;
            $post_id = $comment->comment_post_ID;
            $product = wc_get_product($post_id);

            if (!empty($product)) {
                if (!empty($_COOKIE['wtsr_review_id'])) {
                    $review = ReviewsModel::get_by_id($_COOKIE['wtsr_review_id']);
                    $order_id = $review['order_id'];

                    $order = wc_get_order( $order_id );

                    if ($order) {
                        $product_in_order = false;
                        $order_items = TSManager::get_order_items($order);

                        foreach ($order_items as $product_data) {
                            if ($product_data['parent_id'] === (int)$post_id) {
                                $product_in_order = true;

                                break;
                            }
                        }

                        if ($product_in_order) {
                            add_comment_meta( $comment_ID, 'wtsr_review_id', $_COOKIE['wtsr_review_id'], true );
                        }
                    }
                }

                TSManager::maybe_product_page_review_reviewed($comment_ID, $post_id, $email);
            }
        }
    }

    public function edit_product_review($comment_ID, $data) {
        $wtsr_review_approved = get_option('wtsr_review_approved', 'yes');
        $comment = get_comment( $comment_ID );

        if (!empty($comment) && (!empty($comment->comment_approved) || 'no' === $wtsr_review_approved)) {
            $email = $comment->comment_author_email;
            $post_id = $comment->comment_post_ID;
            $product = wc_get_product($post_id);

            if (!empty($product)) {
                TSManager::maybe_product_page_review_reviewed($comment_ID, $post_id, $email);
            }
        }
    }

    public function product_reviews() {
        $is_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

        if (!$is_ajax) {
            // ReviewServiceManager::check_product_reviews();
        }
    }

    public function check_settings() {
        $is_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

        if (!$is_ajax) {
            delete_transient('wtsr_settings_errors');
            $is_woocommerce_only_mode = ReviewServiceManager::is_woocommerce_only_mode();
            $is_woocommerce_reviews_disabled_globally = TSManager::is_woocommerce_reviews_disabled_globally();

            $default_rating_links = ReviewsModel::get_default_rating_links();
            $wtsr_rating_links = get_option('wtsr_rating_links', $default_rating_links);

            $errors = array();

            foreach ($wtsr_rating_links as $star => $rating_link) {
                if ($is_woocommerce_only_mode && 'wtsr_ts_review_link' === $rating_link) $rating_link = 'wtsr_product_url';
                if ($is_woocommerce_reviews_disabled_globally && 'wtsr_product_url' === $rating_link) $rating_link = 'wtsr_custom_link';

                if ('wtsr_custom_link' === $rating_link) {
                    if (empty($wtsr_rating_links["custom_link"][$star])) {
                        $errors[] = __('You need to specify custom links values on <a href="?page=wp2leads-wtsr&tab=email">Email tab</a>', 'more-better-reviews-for-woocommerce');

                        break;
                    }
                }
            }

            if (!empty($errors)) {
                $error_msg = implode(' ', $errors);

                if (!empty($error_msg)) {
                    set_transient('wtsr_settings_errors', $error_msg);
                }
            }
        }
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

    public function set_reviews_limitation() {
        $is_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

        if (!$is_ajax) {
            $is_limit_set = get_transient('wtsr_limit_set');

            if (!$is_limit_set) {
                $limit_set = TSManager::set_reviews_limitation();

                if ($limit_set) {
                    set_transient('wtsr_limit_set', 1, Wtsr_License::$license_check_timeout);
                }
            }

            TSManager::reset_reviews_limit_counter();
        }
    }

    public function set_license() {
        $is_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

        if (!$is_ajax) {

            $activation_in_progress = get_transient('wtsr_activation_in_progress');

            if ($activation_in_progress) {
                $activation_in_progress_timeout = get_transient('wtsr_activation_in_progress_timeout');

                if (!$activation_in_progress_timeout) {
                    Wtsr_License::close_license();
                    delete_transient('wtsr_activation_in_progress');
                }

                return;
            }

            $is_license_set = get_transient('wtsr_license_set');

            if (!$is_license_set) {
                Wtsr_License::set_license();
                set_transient('wtsr_license_set', 1, Wtsr_License::$license_check_timeout);
            }
        }
    }

    public function woocommerce_order() {
        $selected_order_status = get_option('wtsr_order_status', 'wc-completed');
        $is_wizard = Wtsr_Wizard::is_wizard();

        if (!$is_wizard) {
            if ( 'wc-order-created' === $selected_order_status ) {
                add_action( 'woocommerce_checkout_order_processed', 'TSManager::get_ts_link_new_order', 10, 3 );
            } else {
                add_action('woocommerce_order_status_changed', 'TSManager::get_ts_link_order_status_changed', 50, 4);
            }
        }

        add_action('wtsr_order_review_generate_request', 'TSManager::get_ts_link_new_order', 10, 3);
    }

    public function add_admin_notices() {
        Wtsr_Notices::admin_notices();
    }

    public function new_review_item_created($review_id, $order_id) {}
    public function review_status_changed( $status, $id, $order_id ) {
        // error_log( 'Status changed to ' . $status . ' for review # ' . $id . ' with order # ' . $order_id );
    }

    public function maybe_generate_thankyou_email($status, $review_id, $order_id) {
        return wtsr_maybe_generate_thankyou_email($status, $review_id, $order_id);
    }

    public static function check_version() {
        $version = get_option( 'wtsr_version' );
        $dbversion = get_option( 'wtsr_db_version' );

        if (
                empty($version) || version_compare( $version, WTSR_VERSION, '<' ) ||
                empty($dbversion) || version_compare( $dbversion, WTSR_DB_VERSION, '<' )
        ) {
            self::install();
            do_action( 'wtsr_updated' );
        }

        $check_3_0_0_update = get_option('wtsr_3_0_0_update');

        if (empty($check_3_0_0_update)) {
            require_once plugin_dir_path( WTSR_PLUGIN_FILE ) . 'includes/class-wtsr-updates.php';

            Wtsr_Updates::wp2leads_3_0_0_update();
            update_option('wtsr_3_0_0_update', 1);
        }

        $check_4_0_0_update = get_option('wtsr_4_0_0_update');

        if (empty($check_4_0_0_update)) {
            require_once plugin_dir_path( WTSR_PLUGIN_FILE ) . 'includes/class-wtsr-updates.php';

            Wtsr_Updates::wp2leads_4_0_0_update();
        }
    }

    public static function update_version() {
        delete_option( 'wtsr_version' );
        add_option( 'wtsr_version', WTSR_VERSION );

        delete_option( 'wtsr_db_version' );
        add_option( 'wtsr_db_version', WTSR_DB_VERSION );
    }

    public static function install() {
        ReviewsModel::create_table();
        ReviewsModel::create_meta_table();
        self::update_version();
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    0.0.1
     */
    public function enqueue_styles() {
//        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wtsr-admin.css', array(), $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    0.0.1
     */
    public function enqueue_global_scripts() {
        wp_enqueue_script( $this->plugin_name . '-global', plugin_dir_url( __FILE__ ) . 'js/wtsr-admin-global.js', array( 'jquery' ), $this->version . time(), true );
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    0.0.1
     */
    public function enqueue_global_styles() {
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_style( $this->plugin_name . '-jquery-ui', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.min.css', array(), $this->version . time(), 'all' );
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wtsr-admin.css', array(), $this->version . time(), 'all' );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    0.0.1
     */
    public function enqueue_scripts() {
        if ( ! did_action( 'wp_enqueue_media' ) ) {
            wp_enqueue_media();
        }

        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wtsr-admin.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'wp-color-picker' ), $this->version . time(), true );

    }

    public function user_transfered_to_kt($map_id, $email, $data, $tags, $detach_tags) {
        $map = MapsModel::get($map_id);

        if (empty($map)) {
            return false;
        }

        $map_mapping = unserialize($map->mapping);
        $is_map_wtsr = false;

        foreach ($map_mapping['selects_only'] as $map_column) {
            if (
                'wp2lwtsr_reviews.id' === trim($map_column) ||
                'wp2lwtsr_reviews.order_id' === trim($map_column) ||
                'wp2lwtsr_reviews.email' === trim($map_column) ||
                'wp2lwtsr_reviews.status' === trim($map_column) ||
                'wp2lwtsr_reviews.review_link' === trim($map_column) ||
                'wp2lwtsr_reviews.review_message' === trim($map_column) ||
                'wp2lwtsr_reviews.review_created' === trim($map_column) ||
                'wp2lwtsr_reviews.review_sent' === trim($map_column)
            ) {
                $is_map_wtsr = true;
                break;
            }
        }

        if (!$is_map_wtsr) {
            return false;
        }

        $review = ReviewsModel::get_last_by_email($email);

        if (empty($review) || 'ready' !== trim($review['status'])) {
            return false;
        }

        $transfered = gmdate( 'Y-m-d H:i:s' );

        $data = array(
            'id' => $review['id'],
            'status' => 'transferred',
            'review_sent' => $transfered,
        );

        $id = ReviewsModel::update($data);

        $order = wc_get_order( $review['order_id'] );
        $order_email = !empty($order_data['billing']['email']) ? $order_data['billing']['email'] : false;
        $order_items = TSManager::get_order_items($order);
        $user_products = get_option('wtsr_transferred_' . $email, array());

        foreach ($order_items as $order_item) {
            $user_products[] = $order_item['sku'];
        }

        update_option('wtsr_transferred_' . $email, $user_products, false);

        return $id;
    }

    public function new_review_woo_item_created($review_id, $order_id) {
        $wtsr_email_send_via = TSManager::get_email_send_via();

        if (empty($wtsr_email_send_via) || 'woocommerce' !== $wtsr_email_send_via) {
            return false;
        }

        $wtsr_email_send_via_woocommerce_delay = get_option('wtsr_email_send_via_woocommerce_delay', '');

        if (empty($wtsr_email_send_via_woocommerce_delay)) {
            $mailer = WC()->mailer();
            $email = $mailer->emails['Wtsr_WC_Email_Review_Request'];
            $email->trigger( $order_id );
        } else {
            $now = time();
            $delay_array = explode(':', $wtsr_email_send_via_woocommerce_delay);

            if (count($delay_array)) {
                $is_immediately = false;

                foreach ($delay_array as $index => $delay_days) {
                    if (empty($delay_days)) {
                        $is_immediately = true;
                        unset($delay_array[$index]);
                    }
                }

                if (!empty($delay_array)) {
                    Wtsr_Cron::add_woo_email_schedule($review_id);
                }

                if ($is_immediately) {
                    $mailer = WC()->mailer();
                    $email = $mailer->emails['Wtsr_WC_Email_Review_Request'];
                    $email->trigger( $order_id );
                }
            }
        }
    }

    public function woo_review_request_email_sent($order_id, $scheduled) {
        $review = ReviewsModel::get_by_order_id($order_id);

        if (empty($review)) {
            return;
        }

        $review = $review[0];

        if (empty($review) || ('ready' !== trim($review->status) && 'woo-sent' !== trim($review->status))) {
            return false;
        }

        $order = wc_get_order(  $order_id );
        $note = __( 'Review Request Sent to customer', 'more-better-reviews-for-woocommerce' );
        $order->add_order_note( $note );
        $transfered = gmdate( 'Y-m-d H:i:s' );

        $data = array(
            'id' => $review->id,
            'status' => 'woo-sent',
            'review_sent' => $transfered,
        );

        $id = ReviewsModel::update($data);

        // Double check if scheduled review sent
        if (!empty($scheduled)) {
            $now = time();
            $wtsr_send_woo_email_schedule_all = get_option(Wtsr_Cron::$schedule_option, array());


            if (!empty($wtsr_send_woo_email_schedule_all[$id]) && !empty($wtsr_send_woo_email_schedule_all[$id]['schedule'])) {

                $new_scheduled_array = array();
                $old_schedule = $wtsr_send_woo_email_schedule_all[$id]['schedule'];

                foreach ($old_schedule as $schedule) {
                    if ($schedule > $now) {
                        $new_scheduled_array[] = $schedule;
                    }
                }

                $wtsr_send_woo_email_schedule_all = get_option(Wtsr_Cron::$schedule_option, array());

                if (empty($new_scheduled_array)) {
                    unset($wtsr_send_woo_email_schedule_all[$id]);
                } else {
                    $wtsr_send_woo_email_schedule_all[$id]['schedule'] = $new_scheduled_array;
                }

                update_option(Wtsr_Cron::$schedule_option, $wtsr_send_woo_email_schedule_all);
            }
        }
    }

    /**
     * Add menu item.
     *
     * @since    0.0.1
     */
    public function admin_menu() {
        add_menu_page(
            __('Better Reviews for WooCommerce', 'more-better-reviews-for-woocommerce'),
            __('WOO better Reviews', 'more-better-reviews-for-woocommerce'),
            'administrator',
            'wp2leads-wtsr',
            array($this, 'render_settings_page'),
            '',
            87.9
        );

        if (function_exists('mbrfw_fs')) {
            $is_registered = mbrfw_fs()->is_registered();
            if (!$is_registered) {
                add_submenu_page(
                    'wp2leads-wtsr',
                    __('Opt-in to see account', 'more-better-reviews-for-woocommerce'),
                    __('Opt-in to see account', 'more-better-reviews-for-woocommerce'),
                    'administrator',
                    'wp2leads-wtsr-tutorial',
                    array($this, 'render_settings_page')
                );
            }
        }
    }

    public function plugin_menu_optin() {
        global $submenu;

        if (function_exists('mbrfw_fs')) {
            $reconnect_url = mbrfw_fs()->get_activation_url( array(
                'nonce'     => wp_create_nonce( mbrfw_fs()->get_unique_affix() . '_reconnect' ),
                'fs_action' => ( mbrfw_fs()->get_unique_affix() . '_reconnect' ),
            ) );

            $is_registered = mbrfw_fs()->is_registered();

            if (!$is_registered && isset($submenu["wp2leads-wtsr"])) {
                foreach ($submenu["wp2leads-wtsr"] as $i => $subitem) {
                    if ($subitem[2] === 'wp2leads-wtsr-tutorial') {
                        $submenu["wp2leads-wtsr"][$i] = array(
                            __('Opt-in to see account', 'more-better-reviews-for-woocommerce'),
                            'administrator',
                            $reconnect_url
                        );
                    }
                }
            }
        }
    }

    /**
     * Render settings page.
     *
     * @since    0.0.1
     */
    public function render_settings_page() {
        $reviews = ReviewsModel::get_all();
        $default_tab = !empty($reviews) ? 'reviews' : 'generate';
        $active_tab = isset( $_GET[ 'tab' ] ) ? sanitize_text_field( $_GET[ 'tab' ] ) : $default_tab;

        $locale = get_user_locale();
        $locale_short = explode('_', $locale)[0];

        $required_plugins_wp2leads = Wtsr_Required_Plugins::get_required_plugins_wp2leads();
        $is_wp2leads_installed = Wtsr_Required_Plugins::is_plugin_installed( $required_plugins_wp2leads['slug'] ) && Wtsr_Required_Plugins::is_plugin_active( $required_plugins_wp2leads['slug'] );
        $wtsr_email_send_via = TSManager::get_email_send_via();
        $wtsr_email_send_via_woocommerce_delay = get_option('wtsr_email_send_via_woocommerce_delay', '');

        if ('wizard' === $active_tab) {
            include(dirname(__FILE__) . '/partials/wtsr-admin-wizard.php');
        } else {
            $check_ts_credentials_empty = TSManager::check_ts_credentials_empty();

            if ($active_tab === 'ts-reviews' && $check_ts_credentials_empty) {
                $active_tab = $default_tab;
            }

            include(dirname(__FILE__) . '/partials/wtsr-admin-display.php');
        }
    }

    public function checkbox_for_ts_review(  ) {
        $selected_review_ask = get_option('wtsr_review_ask', 'no');

        if ('yes' === $selected_review_ask) {
            $review_ask_template_editor = get_option('wtsr_review_ask_template_editor', TSManager::get_default_review_ask_template_editor());
            ?>
            <style>
                #ts_review_checkbox_field .woocommerce-form__label-for-checkbox {
                    position: relative;
                    padding-left: 25px;
                    display: block;
                }
                #ts_review_checkbox_field .woocommerce-form__label-for-checkbox .input-checkbox {
                    position: absolute;
                    left: 0;
                    top: 5px;
                }
                #ts_review_checkbox_field .woocommerce-form__label-for-checkbox .ts_review_checkbox-checkbox-text p {
                    margin-top: 0;
                    line-height: 1.2;
                }
            </style>
            <div id="ts_review_checkbox_field">
                <div class="form-row form-row-wide">
                    <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
                        <input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="ts_review_checkbox" id="ts_review_checkbox" />
                        <div class="ts_review_checkbox-checkbox-text"><?php echo wpautop($review_ask_template_editor); ?></div>
                    </label>
                </div>
            </div>
            <?php
        }
    }

    public function checkbox_for_ts_review_save($order_id) {
        $selected_review_ask = get_option('wtsr_review_ask', 'no');

        if ('yes' === $selected_review_ask) {
            if ( ! empty( $_POST['ts_review_checkbox'] ) ) {
                update_post_meta( $order_id, 'Trusted Shops Review', 'yes' );
            }
        } else {
            update_post_meta( $order_id, 'Trusted Shops Review', 'yes' );
        }
    }

    public function checkbox_for_ts_display($order) {
        $is_review_allowed = get_post_meta( $order->get_id(), 'Trusted Shops Review', true );

        if (empty($is_review_allowed)) {
            $is_review_allowed = 'no';
        }

        echo '<p><strong>'.__('Trusted Shops Review').':</strong> ' . $is_review_allowed . '</p>';
    }

    /**
     * Render settings page.
     *
     * @since    0.0.1
     */
    public function save_settings() {
        if (!empty($_POST['wtsr_settings_save'])) {
            $tab = !empty($_GET['tab']) ? sanitize_text_field( $_GET['tab'] ) : 'settings';

            // Save general settings
            if (!empty($_POST['wtsr_settings_general'])) {
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

                if (!empty($_POST['wtsr_review_ask'])) {
                    $wtsr_review_ask = sanitize_text_field( $_POST['wtsr_review_ask'] );

                    if (!empty($wtsr_review_ask)) {
                        delete_option( 'wtsr_review_ask' );
                        add_option( 'wtsr_review_ask', $wtsr_review_ask );
                    }

                    if (!empty($_POST['wtsr_review_ask_template_editor'])) {
                        $wtsr_review_ask_template_editor = $_POST['wtsr_review_ask_template_editor'];

                        if (!empty($wtsr_review_ask_template_editor)) {
                            delete_option( 'wtsr_review_ask_template_editor' );
                            add_option( 'wtsr_review_ask_template_editor', wp_kses_post( $wtsr_review_ask_template_editor ) );
                        }
                    } else {
                        delete_option( 'wtsr_review_ask_template_editor' );
                    }
                }

                if (!empty($_POST['wtsr_initial_review_period'])) {
                    $wtsr_initial_review_period = sanitize_text_field( $_POST['wtsr_initial_review_period'] );

                    if (!empty($wtsr_initial_review_period)) {
                        delete_option( 'wtsr_initial_review_period' );
                        add_option( 'wtsr_initial_review_period', $wtsr_initial_review_period );
                    }
                }

                if (!empty($_POST['wtsr_filter_email_domain'])) {
                    $wtsr_filter_email_domain = sanitize_textarea_field( $_POST['wtsr_filter_email_domain'] );

                    if (!empty($wtsr_filter_email_domain)) {
                        $wtsr_filter_email_domain = explode("\r\n", $wtsr_filter_email_domain);

                        delete_option( 'wtsr_filter_email_domain' );
                        add_option( 'wtsr_filter_email_domain', $wtsr_filter_email_domain );
                    }
                } else {
                    delete_option( 'wtsr_filter_email_domain' );
                }

                if (!empty($_POST['wtsr_review_period'])) {
                    $wtsr_review_period = sanitize_text_field( $_POST['wtsr_review_period'] );

                    if (!empty($wtsr_review_period)) {
                        delete_option( 'wtsr_review_period' );
                        add_option( 'wtsr_review_period', $wtsr_review_period );
                    }
                }

                if (!empty($_POST['wtsr_review_approved'])) {
                    delete_option( 'wtsr_review_approved' );
                    add_option( 'wtsr_review_approved', 'yes' );
                } else {
                    delete_option( 'wtsr_review_approved' );
                    add_option( 'wtsr_review_approved', 'no' );
                }
            }

            // Save email settings
            if (!empty($_POST['wtsr_settings_email'])) {
                if (!empty($_POST['wtsr_email_template'])) {
                    $wtsr_email_template = $_POST['wtsr_email_template'];
                    $wtsr_email_template_html = wpautop($wtsr_email_template);

                    delete_option( 'wtsr_email_template' );
                    add_option( 'wtsr_email_template', wp_kses_post( $_POST['wtsr_email_template'] ) );
                } else {
                    delete_option( 'wtsr_email_template' );
                }

                $wtsr_button_colors = TSManager::get_button_colors();

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

                    if ( 'wtsr_custom_link' === $_POST['wtsr_ts_one_star_link'] && !empty($_POST['wtsr_ts_one_star_custom_link'])) {
                        $wtsr_rating_links['custom_link']['one_star'] = sanitize_text_field($_POST['wtsr_ts_one_star_custom_link']);
                    } else {
                        $wtsr_rating_links['custom_link']['one_star'] = '';
                    }

                    if ( 'wtsr_custom_link' === $_POST['wtsr_ts_two_star_link'] && !empty($_POST['wtsr_ts_two_star_custom_link'])) {
                        $wtsr_rating_links['custom_link']['two_star'] = sanitize_text_field($_POST['wtsr_ts_two_star_custom_link']);
                    } else {
                        $wtsr_rating_links['custom_link']['two_star'] = '';
                    }

                    if ( 'wtsr_custom_link' === $_POST['wtsr_ts_three_star_link'] && !empty($_POST['wtsr_ts_three_star_custom_link'])) {
                        $wtsr_rating_links['custom_link']['three_star'] = sanitize_text_field($_POST['wtsr_ts_three_star_custom_link']);
                    } else {
                        $wtsr_rating_links['custom_link']['three_star'] = '';
                    }

                    if ( 'wtsr_custom_link' === $_POST['wtsr_ts_four_star_link'] && !empty($_POST['wtsr_ts_four_star_custom_link'])) {
                        $wtsr_rating_links['custom_link']['four_star'] = sanitize_text_field($_POST['wtsr_ts_four_star_custom_link']);
                    } else {
                        $wtsr_rating_links['custom_link']['four_star'] = '';
                    }

                    if ( 'wtsr_custom_link' === $_POST['wtsr_ts_five_star_link'] && !empty($_POST['wtsr_ts_five_star_custom_link'])) {
                        $wtsr_rating_links['custom_link']['five_star'] = sanitize_text_field($_POST['wtsr_ts_five_star_custom_link']);
                    } else {
                        $wtsr_rating_links['custom_link']['five_star'] = '';
                    }

                    delete_option( 'wtsr_rating_links' );
                    add_option( 'wtsr_rating_links', $wtsr_rating_links );
                }
            }

            wp_redirect( admin_url( 'admin.php?page=wp2leads-wtsr&tab=' . trim($tab) ) );
        }
    }

    public function add_review_source_columns( $cols ) {
        $review_source_col = array(
            'wtsr_review_source' => __('Source', 'more-better-reviews-for-woocommerce'),
            'wtsr_review_rating' => __('Rating', 'more-better-reviews-for-woocommerce'),
        );

        $cols = array_slice( $cols, 0, 3, true ) + $review_source_col + array_slice( $cols, 3, NULL, true );

        // $cols = array_merge($cols, $review_source_col);

        return $cols;
    }

    public function add_review_sortable_columns( $cols ) {
        $cols['wtsr_review_rating'] = 'wtsr_review_rating';
        $cols['wtsr_review_source'] = 'wtsr_review_source';

        return $cols;
    }

    public function comments_clauses_callback( $clauses ) {
        global $wpdb;
        $show_empty = false;

        $order = !empty($_GET['order']) ? strtoupper( $_GET['order']) : 'DESC';

        if ( 'ASC' !== $order ) {
            $order = 'DESC';
        }

        $join_type = $show_empty ? 'LEFT' : 'INNER';

        $clauses['join'] .= $wpdb->prepare( "
			{$join_type} JOIN {$wpdb->commentmeta} AS acsort_commentmeta ON {$wpdb->comments}.comment_ID = acsort_commentmeta.comment_id
			AND acsort_commentmeta.meta_key = %s
		", 'rating' );

        if ( ! $show_empty ) {
            $clauses['join'] .= " AND acsort_commentmeta.meta_value <> ''";
        }

        $clauses['orderby'] = sprintf( "CAST( acsort_commentmeta.meta_value AS SIGNED ) $order, {$wpdb->comments}.comment_ID $order" );
        $clauses['groupby'] = "{$wpdb->comments}.comment_ID";

        remove_filter( 'comments_clauses', [ $this, __FUNCTION__ ] );

        return $clauses;
    }

    public function comments_clauses_source_callback( $clauses ) {
        global $wpdb;
        $show_empty = true;

        $order = !empty($_GET['order']) ? strtoupper( $_GET['order']) : 'DESC';

        if ( 'ASC' !== $order ) {
            $order = 'DESC';
        }

        $join_type = $show_empty ? 'LEFT' : 'INNER';

        $clauses['join'] .= $wpdb->prepare( "
			{$join_type} JOIN {$wpdb->commentmeta} AS acsort_commentmeta ON {$wpdb->comments}.comment_ID = acsort_commentmeta.comment_id
			AND (acsort_commentmeta.meta_key = %s OR acsort_commentmeta.meta_key = %s)
		", 'wtsr_ts_uuid', 'wtsr_review_id' );

        if ( ! $show_empty ) {
            $clauses['join'] .= " AND acsort_commentmeta.meta_value <> ''";
        }

        if (!empty($clauses['where'])) {
            $clauses['where'] .= " AND comment_type = 'review'";
        } else {
            $clauses['where'] = "comment_type = 'review'";
        }

        $clauses['orderby'] = sprintf( "CAST( acsort_commentmeta.meta_key AS SIGNED ) $order, {$wpdb->comments}.comment_ID $order" );
        $clauses['groupby'] = "{$wpdb->comments}.comment_ID";

        remove_filter( 'comments_clauses', [ $this, __FUNCTION__ ] );

        return $clauses;
    }

    public function review_sortable_columns_orderby($query) {
        remove_action( 'pre_get_comments', [ $this, __FUNCTION__ ] );
        if( ! is_admin() ) return;

        if( empty( $_GET['orderby'] ) || empty( $_GET['order'] ) ) return;

        if ( 'wtsr_review_rating' === $_GET['orderby']) {
            add_filter( 'comments_clauses', [ $this, 'comments_clauses_callback' ] );
        } elseif ('wtsr_review_source' === $_GET['orderby'] ) {
            add_filter( 'comments_clauses', [ $this, 'comments_clauses_source_callback' ] );
        }
    }

    public function review_source_columns_content( $column, $comment_ID ) {
        global $comment;
        $rating = get_comment_meta( $comment_ID, 'rating', true );

        if ('wtsr_review_rating' === $column && !empty($rating)) {
            $plugin_url = untrailingslashit( plugins_url( '/', WTSR_PLUGIN_FILE ) );
            $content = '';
            $star = 'five_star';
            if (4 == $rating) $star = 'four_star';
            if (3 == $rating) $star = 'three_star';
            if (2 == $rating) $star = 'two_star';
            if (1 == $rating) $star = 'one_star';

            ob_start();
            ?>
            <img src="<?php echo $plugin_url . '/admin/img/'.$star.'.png'; ?>" style="vertical-align:sub;height:14px;width: auto;">
            <?php

            $content .= ob_get_clean();

            echo $content;
        }

        if ('wtsr_review_source' === $column && !empty($rating)) {
            $content = '';

            $ts_source = get_comment_meta( $comment_ID, 'wtsr_ts_uuid', true );
            $aiop_source = get_comment_meta( $comment_ID, 'wtsr_review_id', true );

            if (!empty($ts_source)) {
                $content .= __('Trusted Shops', 'more-better-reviews-for-woocommerce');
            }

            if (!empty($aiop_source)) {
                if (!empty($content)) {
                    $content .= "<br>";
                }

                $content .= __('All-in-one reviews page', 'more-better-reviews-for-woocommerce');
            }

            if (empty($content)) {
                $content = __('Product details page', 'more-better-reviews-for-woocommerce');
            }

            echo $content;
        }
    }

    public function review_source_columns_width() {
        echo '<style>#wtsr_review_source {max-width: 150px;width: 150px;}</style>';
    }
}
