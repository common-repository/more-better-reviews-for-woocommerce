<?php
/**
 * Class Wtsr_Notices
 */
class Wtsr_Notices {

    public static function admin_notices() {
        $is_wizard = Wtsr_Wizard::is_wizard();
        Wtsr_Notices::get_ts_dev_notices();

        if (empty($_GET['tab']) || 'wizard' !== $_GET['tab']) {
            if ($is_wizard) {
                Wtsr_Notices::get_wizard_notices();
            } else {
                Wtsr_Notices::get_ts_error_notices();
                Wtsr_Notices::get_ts_warning_notices();
                Wtsr_Notices::get_ts_credentials_notices();
                Wtsr_Notices::get_ts_domain_notices();
                Wtsr_Notices::get_ts_notices();
                Wtsr_License::licenses_notices();
            }
        }
    }

    public static function get_wizard_notices() {
        ?>
        <div class="notice notice-warning">
            <p>
                <strong><?php _e('Better Reviews for WooCommerce', 'more-better-reviews-for-woocommerce') ?>:</strong> <?php _e('Please finish installation wizard.', 'more-better-reviews-for-woocommerce') ?>
                <a href="?page=wp2leads-wtsr&tab=wizard" class="button button-primary button-small">
                    <?php _e('Continue installation', 'more-better-reviews-for-woocommerce') ?>
                </a>
            </p>
        </div>
        <?php
    }

    public static function get_ts_error_notices() {
        // Credentials errors
        $woocommerce_only_mode_enabled = get_option('wtsr_woocommerce_only_mode_enabled', false);
        $check_ts_credentials = get_transient('wtsr_check_ts_credentials');

        if (!empty($check_ts_credentials) && empty($woocommerce_only_mode_enabled)) {
            ?>
            <div class="notice notice-error">
                <p>
                    <strong><?php _e('Better Reviews for WooCommerce', 'more-better-reviews-for-woocommerce') ?></strong>:
                    <?php echo $check_ts_credentials; ?>.
                    <?php _e('Please input correct credentials or enable WooCommerce only mode <strong><a href="?page=wp2leads-wtsr&tab=settings">here</a></strong>', 'more-better-reviews-for-woocommerce'); ?>.
                </p>
            </div>
            <?php
        }

        // Settings errors
        $settings_errors = get_transient('wtsr_settings_errors');

        if (!empty($settings_errors)) {
            ?>
            <div class="notice notice-error">
                <p>
                    <strong><?php _e('Better Reviews for WooCommerce', 'more-better-reviews-for-woocommerce') ?></strong>:
                    <?php echo $settings_errors; ?>
                </p>
            </div>
            <?php
        }
    }

    public static function get_ts_warning_notices() {
        $is_woocommerce_reviews_disabled_globally = TSManager::is_woocommerce_reviews_disabled_globally();
        $is_ts_review_request_enabled = get_option('wtsr_ts_review_request_enabled');
        $is_wizard = Wtsr_Wizard::is_wizard();

        if ($is_woocommerce_reviews_disabled_globally) {
            $dismissed_warning = get_transient('wtsr_dismiss_woocommerce_reviews_disabled_globally_warning');

            if (empty($dismissed_warning)) {
                ?>
                <div id="woocommerce-reviews-disabled-globally-warning" class="notice notice-warning notice-to-dismiss" data-slug="woocommerce_reviews_disabled_globally_warning">
                    <p>
                        <strong><?php _e('Better Reviews for WooCommerce', 'more-better-reviews-for-woocommerce') ?></strong>:
                        <?php _e('Your products reviews disabled globally. If you want to use product page review link in your html template enable reviews, please', 'more-better-reviews-for-woocommerce') ?>
                        <button id="ts-woocommerce-reviews-enable" class="button button-primary button-small" type="button"><?php _e('Enable Reviews', 'more-better-reviews-for-woocommerce') ?></button>
                    </p>

                    <div class="dismiss-holder">
                        <span class="dismiss-btn" data-dismiss="dismiss-completely"><?php _e('Dismiss completely', 'more-better-reviews-for-woocommerce') ?> <strong>&times;</strong></span>
                        <span class="dismiss-btn" data-dismiss="dismiss-week"><?php _e('Dismiss for week', 'more-better-reviews-for-woocommerce') ?> <strong>&times;</strong></span>
                    </div>
                </div>
                <?php
            }
        } else {
            $is_woocommerce_reviews_disabled_per_product = TSManager::is_woocommerce_reviews_disabled_per_product();
            $all_products = TSManager::is_woocommerce_reviews_disabled_per_product(true);

            $is_star_rating_type_exists = TSManager::is_star_rating_type_exists('wtsr_product_url');

            if (!empty($is_woocommerce_reviews_disabled_per_product) && !empty($is_star_rating_type_exists)) {
                $dismissed_warning = get_transient('wtsr_dismiss_woocommerce_reviews_disabled_per_product_warning');

                if (empty($dismissed_warning)) {
                    $i = 1;
                    $count = count($is_woocommerce_reviews_disabled_per_product);
                    $count_all = count($all_products);
                    ?>
                    <div id="woocommerce-reviews-disabled-per-product-warning" class="notice notice-warning notice-to-dismiss" data-slug="woocommerce_reviews_disabled_per_product_warning">
                        <p>
                            <strong><?php _e('Better Reviews for WooCommerce', 'more-better-reviews-for-woocommerce') ?></strong>:
                            <?php echo sprintf(__('Your have <strong>%s</strong> out of <strong>%s</strong> products reviews disabled.', 'more-better-reviews-for-woocommerce') , $count, $count_all ) ?>
                            <?php _e('This products <strong>will not be included</strong> to html template for product reviews request as far as you have <strong>"Product page link"</strong>  enabled in <strong>"Start rating links"</strong>.', 'more-better-reviews-for-woocommerce') ?>
                            <?php _e('You can enable each products review on product edit page or change settings on <strong>"<a href="?page=wp2leads-wtsr&tab=email#wtsr_rating_links_settings_table">Email</a>"</strong> tab.', 'more-better-reviews-for-woocommerce') ?>
                            <br>
                            <strong><?php _e('Products list', 'more-better-reviews-for-woocommerce') ?></strong>:
                            <?php
                            foreach ($is_woocommerce_reviews_disabled_per_product as $product) {
                                ?><a href="/wp-admin/post.php?post=<?php echo $product['ID'] ?>&action=edit" target="_blank">
                                <?php echo $product['post_title'] ?></a><?php echo $i < $count ? ', ' : ''; ?><?php
                                $i++;
                            }
                            ?><br><br>
                            <button id="ts-woocommerce-reviews-per-product-enable" class="button button-primary button-small" type="button"><?php _e('Enable All Products Reviews', 'more-better-reviews-for-woocommerce') ?></button> or <a href="?page=wp2leads-wtsr&tab=email#wtsr_rating_links_settings_table" class="button button-primary button-small"><?php _e('Change Start rating links settings', 'more-better-reviews-for-woocommerce') ?></a>
                        </p>

                        <div class="dismiss-holder">
                            <span class="dismiss-btn" data-dismiss="dismiss-completely"><?php _e('Dismiss completely', 'more-better-reviews-for-woocommerce') ?> <strong>&times;</strong></span>
                            <span class="dismiss-btn" data-dismiss="dismiss-week"><?php _e('Dismiss for week', 'more-better-reviews-for-woocommerce') ?> <strong>&times;</strong></span>
                        </div>
                    </div>
                    <?php
                }
            }
        }

        $is_wtsr_module_enabled = TSManager::is_wtsr_module_enabled();
        $wtsr_map_notice = '';
        $required_plugins_wp2leads = Wtsr_Required_Plugins::get_required_plugins_wp2leads();
        $is_wp2leads_installed = Wtsr_Required_Plugins::is_plugin_installed( $required_plugins_wp2leads['slug'] ) && Wtsr_Required_Plugins::is_plugin_active( $required_plugins_wp2leads['slug'] );

        if ($is_wp2leads_installed) {
            $default_send_via = 'klick-tipp';
        } else {
            $default_send_via = 'woocommerce';
        }

        $wtsr_email_send_via = get_option('wtsr_email_send_via', $default_send_via);

        if ('woocommerce' !== $wtsr_email_send_via) {
            if (!$is_wtsr_module_enabled) {
                $is_wtsr_map_exists = TSManager::is_wtsr_map_exists();

                if (!$is_wtsr_map_exists) {
                    $wtsr_map_notice = 'no_map_exists';
                } else {
                    if (count($is_wtsr_map_exists) > 1) {
                        $wtsr_map_notice = 'multiple_no_module_enabled';
                    } else {
                        $wtsr_map_notice = 'no_module_enabled';
                    }
                }
            } else {
                $count_modules = count($is_wtsr_module_enabled);

                if ($count_modules > 1) {
                    $wtsr_map_notice = 'multiple_module_enabled';
                }
            }
        }

        if (!empty($wtsr_map_notice)) {
            ?>
            <div id="woocommerce-reviews-disabled-globally-warning" class="notice notice-warning">
                <p>
                    <strong><?php _e('Better Reviews for WooCommerce', 'more-better-reviews-for-woocommerce') ?></strong>:
                    <?php
                    if ('no_module_enabled' === $wtsr_map_notice) {
                        echo sprintf(__('You have a map for Better Reviews for WooCommerce, but transfer module is disabled. Please, <a href="?page=wp2l-admin&tab=map_to_api&active_mapping=%s" target="_blank">enable module here</a> in order to transfer reviews instantly.', 'more-better-reviews-for-woocommerce') , $is_wtsr_map_exists[0]['id']);
                    } elseif ('no_map_exists' === $wtsr_map_notice) {
                        _e('You do not have any map for Better Reviews for WooCommerce. Please, <a href="?page=wp2l-admin&tab=map_port" target="_blank">download map here</a> in order to transfer reviews to Klick Tipp.', 'more-better-reviews-for-woocommerce');
                    } elseif ('multiple_module_enabled' === $wtsr_map_notice) {
                        _e('You have more than one Better Reviews for WooCommerce maps with transfer modules are enabled. Please, <a href="?page=wp2l-admin&tab=map_to_api" target="_blank">deactivate extra modules here</a> in order to prevent conflicts while transfering reviews to Klick Tipp.', 'more-better-reviews-for-woocommerce');
                    } elseif ('multiple_no_module_enabled' === $wtsr_map_notice) {
                        _e('You have more then one maps for Better Reviews for WooCommerce, but transfer modules are disabled. Please, <a href="?page=wp2l-admin&tab=map_to_api" target="_blank">enable module for just one map here</a> in order to transfer reviews instantly.', 'more-better-reviews-for-woocommerce');
                    }
                    ?>
                </p>
            </div>
            <?php
        }

        if (empty($is_ts_review_request_enabled) && !$is_wizard) {
            ?>
            <div id="woocommerce-reviews-disabled-globally-warning" class="notice notice-warning">
                <p>
                    <strong><?php _e('Better Reviews for WooCommerce', 'more-better-reviews-for-woocommerce') ?></strong>:
                    <?php
                    _e('Auto generation and sending reviews request disabled. Please <a href="?page=wp2leads-wtsr&tab=generate" target="_blank">enable it here</a> in order to send reviews automatically.', 'more-better-reviews-for-woocommerce');
                    ?>
                </p>
            </div>
            <?php
        }
    }

    public static function get_ts_dev_notices() {
        $wtsr_is_dev_env = defined( 'WTSR_DEV_ENV' ) && WTSR_DEV_ENV;

        if ($wtsr_is_dev_env) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong><?php _e('Better Reviews for WooCommerce', 'more-better-reviews-for-woocommerce') ?> DEV env:</strong>  version <strong><?php echo WTSR_VERSION ?></strong>

                    <?php
                    if (defined( 'WTSR_BRANCH' ) && WTSR_BRANCH) {
                        ?>
                        current branch <strong><?php echo WTSR_BRANCH ?></strong>
                        <?php
                    }
                    ?>
                </p>

                <?php
                if (!Wtsr_Wizard::is_wizard()) {
                    ?>
                    <p>
                        <button id="wtsr-activate-wizard" class="button button-primary" type="button"><?php _e('Activate Wizard', 'more-better-reviews-for-woocommerce') ?></button>
                    </p>
                    <?php
                }
                ?>
            </div>
            <?php
        }
    }

    public static function get_ts_notices() {
        $dismissed_warning = get_transient('wtsr_dismiss_empty_product_title_warning');

        if (empty($dismissed_warning)) {
            global $wpdb;

            $sql = "SELECT COUNT(*) as num FROM " . $wpdb->posts . " WHERE post_type = 'product' AND post_title = ' ';";
            $result = (int) $wpdb->get_var($sql);

            if (!empty($result)) {
                ?>
                <div id="empty-product-title-warning" class="notice notice-warning notice-to-dismiss" data-slug="empty_product_title_warning">
                    <p>
                        <strong><?php _e('Better Reviews for WooCommerce', 'more-better-reviews-for-woocommerce') ?></strong>:
                        <?php _e('You have empty product titles and we will transfer the product slug instead of the title!', 'more-better-reviews-for-woocommerce') ?>
                        <?php _e('Or you add title and we use it. See list of products without title', 'more-better-reviews-for-woocommerce') ?>
                        <?php _e('<strong><a href="edit.php?post_type=product&orderby=title&order=asc">here</a></strong>', 'more-better-reviews-for-woocommerce') ?>
                    </p>

                    <div class="dismiss-holder">
                        <span class="dismiss-btn" data-dismiss="dismiss-completely"><?php _e('Dismiss completely', 'more-better-reviews-for-woocommerce') ?> <strong>&times;</strong></span>
                        <span class="dismiss-btn" data-dismiss="dismiss-week"><?php _e('Dismiss for week', 'more-better-reviews-for-woocommerce') ?> <strong>&times;</strong></span>
                    </div>
                </div>
                <?php
            }
        }
    }

    public static function get_ts_credentials_notices() {
        $check_ts_credentials_empty = get_transient('wtsr_check_ts_credentials_empty');
        $woocommerce_only_mode_enabled = get_option('wtsr_woocommerce_only_mode_enabled', false);
        $dismissed_woocommerce_only_warning = get_transient('wtsr_dismiss_check_ts_woocommerce_only_warning');

        if ((!empty($check_ts_credentials_empty) || $woocommerce_only_mode_enabled) && empty($dismissed_woocommerce_only_warning)) {
            ?>
            <div id="check-ts-woocommerce-only-warning" class="notice notice-info notice-to-dismiss" data-slug="check_ts_woocommerce_only_warning">
                <p>
                    <strong><?php _e('Better Reviews for WooCommerce', 'more-better-reviews-for-woocommerce') ?></strong>:
                    <?php _e('WooCommerce only mode is enabled', 'more-better-reviews-for-woocommerce'); ?>.
                    <?php _e('If you want more, you find all possibilities of the plugin in the<strong> <a href="?page=wp2leads-wtsr&tab=overview">Overview</a> </strong>.', 'more-better-reviews-for-woocommerce'); ?>
                </p>

                <div class="dismiss-holder">
                    <span class="dismiss-btn" data-dismiss="dismiss-completely"><?php _e('Dismiss completely', 'more-better-reviews-for-woocommerce') ?> <strong>&times;</strong></span>
                    <span class="dismiss-btn" data-dismiss="dismiss-week"><?php _e('Dismiss for week', 'more-better-reviews-for-woocommerce') ?> <strong>&times;</strong></span>
                </div>
            </div>
            <?php
        }
    }

    public static function get_ts_domain_notices() {
        $check_ts_domain_credentials = get_transient('wtsr_check_ts_domain_credentials');
        $dismissed_check_ts_domain_credentials_warning = get_transient('wtsr_dismiss_check_ts_domain_credentials_warning');

        if (!empty($check_ts_domain_credentials) && empty($dismissed_check_ts_domain_credentials_warning)) {
            ?>
            <div id="check-ts-domain-credentials-warning" class="notice notice-info notice-to-dismiss" data-slug="check_ts_domain_credentials_warning">
                <p>
                    <strong><?php _e('Better Reviews for WooCommerce', 'more-better-reviews-for-woocommerce') ?></strong>:
                    <?php echo $check_ts_domain_credentials; ?>
                </p>

                <div class="dismiss-holder">
                    <span class="dismiss-btn" data-dismiss="dismiss-completely"><?php _e('Dismiss completely', 'more-better-reviews-for-woocommerce') ?> <strong>&times;</strong></span>
                    <span class="dismiss-btn" data-dismiss="dismiss-week"><?php _e('Dismiss for week', 'more-better-reviews-for-woocommerce') ?> <strong>&times;</strong></span>
                </div>
            </div>
            <?php
        }
    }
}