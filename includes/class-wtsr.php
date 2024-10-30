<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @since      0.0.1
 *
 * @package    Wtsr
 * @subpackage Wtsr/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.0.1
 * @package    Wtsr
 * @subpackage Wtsr/includes
 * @author     Tobias Conrad <tc@santegra.de>
 */
class Wtsr {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      Wtsr_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    0.0.1
	 */
	public function __construct() {
		if ( defined( 'WTSR_VERSION' ) ) {
			$this->version = WTSR_VERSION;
		} else {
			$this->version = '0.0.1';
		}
		$this->plugin_name = 'wp2leads-wtsr';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wtsr_Loader. Orchestrates the hooks of the plugin.
	 * - Wtsr_i18n. Defines internationalization functionality.
	 * - Wtsr_Admin. Defines all hooks for the admin area.
	 * - Wtsr_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function load_dependencies() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/lib/Wtsr_Notices.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/lib/Wtsr_Settings.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/lib/Wtsr_Review_Request.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/lib/Wtsr_Required_Plugins.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/lib/Wtsr_Template.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/lib/Wtsr_Cron.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/lib/Wtsr_Wc_Review.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wtsr-wizard.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wtsr-license-fake.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wtsr-license.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wtsr-rest.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wtsr-background-review-request.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wtsr-background-review-check.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wtsr-background-woo-email-review.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/transfer-modules/class-wtsr-transfer-review-request.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wtsr-wc-custom-email.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wtsr-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wtsr-i18n.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/lib/ReviewsModel.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/lib/ReviewServiceManager.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wtsr-admin-wizard.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wtsr-admin-ajax.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wtsr-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wtsr-public.php';

		$this->loader = new Wtsr_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wtsr_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wtsr_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_admin_hooks() {
	    $is_wizard = Wtsr_Wizard::is_wizard();

		$plugin_admin = new Wtsr_Admin( $this->get_plugin_name(), $this->get_version() );
        $wizard_handler = new Wtsr_Admin_Wizard;
        $ajax_handler = new Wtsr_Admin_Ajax;
        $cron_manager = new Wtsr_Cron;

        $cron_manager->setScheduleHook();

        $this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'plugin_menu_optin', 50 );
        $this->loader->add_filter('plugin_action_links_' . plugin_basename(dirname(dirname(__FILE__))) . '/wtsr.php', $plugin_admin, 'add_plugin_screen_link');
        $this->loader->add_filter( 'display_post_states', $plugin_admin, 'display_post_states', 10, 2 );

        if(isset($_GET['page']) && $_GET['page'] == 'wp2leads-wtsr') {
            $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
            $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        }

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_global_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_global_scripts' );

        $this->loader->add_action( 'admin_init', $wizard_handler, 'setup_wizard' );

        if (!$is_wizard) {
            // Hooks only after wizard finished
            $this->loader->add_action( 'admin_init', $plugin_admin, 'set_ts_credentials', 11 );
            $this->loader->add_action( 'admin_init', $plugin_admin, 'set_reviews_limitation', 12 );
            $this->loader->add_action( 'admin_init', $plugin_admin, 'set_license', 13 );
            $this->loader->add_action( 'admin_init', $plugin_admin, 'check_settings', 14 );
            $this->loader->add_action( 'init', $plugin_admin, 'save_settings' );
            // $this->loader->add_action( 'wp', $plugin_admin, 'product_reviews' );
            $this->loader->add_action( 'init', $plugin_admin, 'product_reviews', 60 );
        } else {
            $this->loader->add_action( 'admin_init', $plugin_admin, 'set_reviews_limitation', 12 );
            $this->loader->add_action( 'init', $wizard_handler, 'save_settings' );
            $this->loader->add_action( 'admin_init', $wizard_handler, 'set_ts_credentials', 11 );

            $load_tp_footer = false;

            if ($load_tp_footer) {
                $this->loader->add_action( 'admin_footer', $wizard_handler, 'add_tracking_pixel' );
            } else {
                $this->loader->add_action( 'admin_head', $wizard_handler, 'add_tracking_pixel' );
            }
        }

        if (!empty($_GET['wizard_completed'])) {
            $load_tp_footer = false;

            if ($load_tp_footer) {
                $this->loader->add_action( 'admin_footer', $wizard_handler, 'add_tracking_pixel' );
            } else {
                $this->loader->add_action( 'admin_head', $wizard_handler, 'add_tracking_pixel' );
            }
        }

        $this->loader->add_action( 'admin_notices', $plugin_admin, 'add_admin_notices' );
        $this->loader->add_action( 'init', $plugin_admin, 'init' );
        $this->loader->add_action( 'init', $plugin_admin, 'woocommerce_order' );
        $this->loader->add_action( 'rest_api_init', $plugin_admin, 'rest_api_init' );

        // Woocommerce Hooks hooks
        $this->loader->add_action( 'comment_post', $plugin_admin, 'new_product_review', 15, 3 );
        // $this->loader->add_action( 'wtsr_review_created', $plugin_admin, 'new_product_review', 15, 3 );
        $this->loader->add_action( 'wp_set_comment_status', $plugin_admin, 'edit_product_review', 15, 2 );
        $this->loader->add_action( 'woocommerce_admin_order_data_after_billing_address', $plugin_admin, 'checkbox_for_ts_display' );
        $this->loader->add_action( 'woocommerce_checkout_update_order_meta', $plugin_admin, 'checkbox_for_ts_review_save' );
        $this->loader->add_action( 'woocommerce_review_order_before_submit', $plugin_admin, 'checkbox_for_ts_review' );
        $this->loader->add_filter( 'manage_edit-comments_columns', $plugin_admin, 'add_review_source_columns' );
        $this->loader->add_filter( 'manage_edit-comments_sortable_columns', $plugin_admin, 'add_review_sortable_columns' );
        $this->loader->add_action( 'pre_get_comments', $plugin_admin, 'review_sortable_columns_orderby' );
        $this->loader->add_action( 'manage_comments_custom_column', $plugin_admin, 'review_source_columns_content', 10, 2 );
        $this->loader->add_action( 'admin_head', $plugin_admin, 'review_source_columns_width' );

        // WTSR hooks
        $this->loader->add_action( 'wtsr_new_woo_review_item_created', $plugin_admin, 'new_review_woo_item_created', 15, 2 );
        $this->loader->add_action( 'wtsr_woo_review_request_email_sent', $plugin_admin, 'woo_review_request_email_sent', 15, 2 );
        $this->loader->add_action( 'wtsr_new_review_item_created', $plugin_admin, 'new_review_item_created', 15, 2 );
        $this->loader->add_action( 'wtsr_review_status_changed', $plugin_admin, 'maybe_generate_thankyou_email', 10, 3 );
        $this->loader->add_action( 'wtsr_review_status_changed', $plugin_admin, 'review_status_changed', 15, 3 );
        $this->loader->add_action( 'wtsr_license_version_changed', $plugin_admin, 'license_version_changed', 15 );

        // WP2LEADS hooks
        $this->loader->add_action( 'wp2leads_transfer_user_created', $plugin_admin, 'user_transfered_to_kt', 15, 5 );
        $this->loader->add_action( 'wp2leads_transfer_user_updated', $plugin_admin, 'user_transfered_to_kt', 15, 5 );

        // AJAX Hooks
        $this->loader->add_action('wp_ajax_wtsr_settings_save', $ajax_handler, 'settings_save');
        $this->loader->add_action('wp_ajax_wtsr_save_ts_credentials', $ajax_handler, 'save_ts_credentials');
        $this->loader->add_action('wp_ajax_wtsr_save_thankyou_settings', $ajax_handler, 'save_thankyou_settings');
        $this->loader->add_action('wp_ajax_wtsr_cancel_schedule', $ajax_handler, 'cancel_schedule');
        $this->loader->add_action('wp_ajax_wtsr_test_spammyness', $ajax_handler, 'test_spammyness');
        $this->loader->add_action('wp_ajax_wtsr_send_selected_reviews', $ajax_handler, 'send_selected_reviews');
        $this->loader->add_action('wp_ajax_wtsr_send_review', $ajax_handler, 'send_review');
        $this->loader->add_action('wp_ajax_wtsr_install_plugin', $ajax_handler, 'install_plugin');
        $this->loader->add_action('wp_ajax_wtsr_wizard_go_to_step', $ajax_handler, 'wizard_go_to_step');
        $this->loader->add_action('wp_ajax_wtsr_generate_review', $ajax_handler, 'wtsr_generate_review');
        $this->loader->add_action('wp_ajax_wtsr_generate_selected_reviews', $ajax_handler, 'generate_selected_reviews');
        $this->loader->add_action('wp_ajax_wtsr_delete_review', $ajax_handler, 'wtsr_delete_review');
        $this->loader->add_action('wp_ajax_wtsr_delete_selected_reviews', $ajax_handler, 'delete_selected_reviews');
        $this->loader->add_action('wp_ajax_wtsr_delete_all_reviews', $ajax_handler, 'delete_all_reviews');
        $this->loader->add_action('wp_ajax_wtsr_license_action', $ajax_handler, 'license_action');
        $this->loader->add_action('wp_ajax_wtsr_remove_license', $ajax_handler, 'remove_license');
        $this->loader->add_action('wp_ajax_wtsr_dissmiss_notice', $ajax_handler, 'dissmiss_notice');
        $this->loader->add_action('wp_ajax_wtsr_ts_enable_review_request', $ajax_handler, 'ts_enable_review_request');
        $this->loader->add_action('wp_ajax_wtsr_woocommerce_only_mode_enable', $ajax_handler, 'woocommerce_only_mode_enable');
        $this->loader->add_action('wp_ajax_wtsr_woocommerce_reviews_enable', $ajax_handler, 'woocommerce_reviews_enable');
        $this->loader->add_action('wp_ajax_wtsr_woocommerce_reviews_per_product_enable', $ajax_handler, 'woocommerce_reviews_per_product_enable');
        $this->loader->add_action('wp_ajax_wtsr_all_reviews_page_create', $ajax_handler, 'all_reviews_page_create');
        $this->loader->add_action('wp_ajax_wtsr_all_reviews_page_colors_save', $ajax_handler, 'all_reviews_page_colors_save');
        $this->loader->add_action('wp_ajax_wtsr_generate_dummy_orders', $ajax_handler, 'generate_dummy_orders');
        $this->loader->add_action( 'wp_ajax_wtsr_activate_wizard', $ajax_handler, 'activate_wizard', 1000 );
        $this->loader->add_action('wp_ajax_wtsr_get_ts_reviews', $ajax_handler, 'get_ts_reviews');
        $this->loader->add_action('wp_ajax_wtsr_map_ts_reviews', $ajax_handler, 'map_ts_reviews');
        $this->loader->add_action('wp_ajax_wtsr_import_ts_reviews', $ajax_handler, 'import_ts_reviews');
        $this->loader->add_action('wp_ajax_wtsr_delete_ts_reviews', $ajax_handler, 'delete_ts_reviews');

        // Wizard Hooks
        $this->loader->add_action('wp_ajax_wtsr_wizard_set_ts_credentials', $wizard_handler, 'ajax_set_ts_credentials');
        $this->loader->add_action('wp_ajax_wtsr_wizard_activate_woocommerce_only', $wizard_handler, 'ajax_activate_woocommerce_only');
        $this->loader->add_action('wp_ajax_wtsr_wizard_save_general_settings', $wizard_handler, 'ajax_save_general_settings');
        $this->loader->add_action('wp_ajax_wtsr_wizard_save_email_template_settings', $wizard_handler, 'ajax_save_email_template_settings');
        $this->loader->add_action('wp_ajax_wtsr_wizard_save_star_rating_settings', $wizard_handler, 'ajax_save_star_rating_settings');
        $this->loader->add_action('wp_ajax_wtsr_wizard_generate_dummy_orders', $wizard_handler, 'ajax_generate_dummy_orders');
        $this->loader->add_action('wp_ajax_wtsr_wizard_import_activate_map', $wizard_handler, 'ajax_import_activate_map');
        $this->loader->add_action('wp_ajax_wtsr_wizard_activate_map', $wizard_handler, 'ajax_activate_map');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Wtsr_Public( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'wp_loaded', $plugin_public, 'manage_cookies' );
        $this->loader->add_filter('show_admin_bar', $plugin_public, 'show_admin_bar');

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $plugin_public, 'add_shortcodes' );
		$this->loader->add_action( 'init', $plugin_public, 'add_rewrite_rules' );
		// $this->loader->add_action( 'init', $plugin_public, 'fake_page_redirect' );
        $this->loader->add_filter( 'document_title_parts', $plugin_public, 'display_all_in_one_review_page_title', 30 );
		$this->loader->add_action( 'template_redirect', $plugin_public, 'template_redirect' );

		$this->loader->add_action( 'wp_loaded', $plugin_public, 'maybe_apply_coupon' );
		$this->loader->add_action( 'wtsr_first_review_created', $plugin_public, 'first_review_created', 15, 2 );
		$this->loader->add_action( 'wtsr_review_created', $plugin_public, 'review_created', 15, 2 );

        $this->loader->add_action('wp_ajax_wtsr_ajax_review_submit', $plugin_public, 'ajax_review_submit');
        $this->loader->add_action('wp_ajax_nopriv_wtsr_ajax_review_submit', $plugin_public, 'ajax_review_submit');

        $this->loader->add_action( 'woocommerce_review_meta', $plugin_public, 'woocommerce_review_display_meta', 15 );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    0.0.1
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     0.0.1
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     0.0.1
	 * @return    Wtsr_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     0.0.1
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
