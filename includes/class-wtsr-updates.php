<?php

class Wtsr_Updates {
    public static function wp2leads_3_0_0_update() {
        $woocommerce_only_mode_enabled = get_option('wtsr_woocommerce_only_mode_enabled', false);
        if (!empty($woocommerce_only_mode_enabled)) return;

        $check_ts_credentials_empty = TSManager::is_credentials_empty();

        if ($check_ts_credentials_empty) {
            ReviewServiceManager::activate_woocommerce_only_mode();
            return;
        }

        $ts_cred_error = get_option('wtsr_check_ts_credentials');

        if (empty($ts_cred_error)) {
            update_option('wtsr_ts_mode_enabled', 'on');
        }

        return;
    }

    public static function wp2leads_4_0_0_update() {
        delete_option('wtsr_trustpilot_credentials');
        delete_option('wtsr_trustpilot_mode_enabled');
        delete_option('wtsr_trustpilot_product_reviews_import');
        delete_option('wtsr_check_trustpilot_credentials_empty');
        delete_option('wtsr_check_trustpilot_credentials');
        delete_option('wtsr_check_trustpilot_domain_credentials');

        delete_option('wtsr_trustpilot_credential_empty_set');
        delete_option('wtsr_trustpilot_domain_credentials_set');

        // update_option('wtsr_3_0_0_update', 1);

        // delete this wtsr_product_trustpilot_reviews_
        // $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%wtsr_product_trustpilot_reviews_%'" );
    }
}