<?php


class Wtsr_Required_Plugins {
    public static function get_required_plugins() {
        $required_plugins = array();
        $wp2leads = Wtsr_Required_Plugins::get_required_plugins_wp2leads();
        $woocommerce = Wtsr_Required_Plugins::get_required_plugins_woocommerce();

        if (false && !empty($wp2leads)) $required_plugins['wp2leads'] = $wp2leads;
        if (!empty($woocommerce)) $required_plugins['woocommerce'] = $woocommerce;

        return $required_plugins;
    }

    public static function get_required_plugins_wp2leads() {
        return array(
            'label' => 'WP2LEADS',
            'link' => 'https://wordpress.org/plugins/wp2leads/',
            'zip_path' => 'https://downloads.wordpress.org/plugin/wp2leads.1.1.11.zip',
            'slug' => 'wp2leads/wp2leads.php',
            'author' => __('By Tobias_Conrad', 'more-better-reviews-for-woocommerce'),
            'description' => ''
        );
    }

    public static function get_required_plugins_woocommerce() {
        ob_start();
        ?>
        <p style="line-height:1.3;font-size:13px;">
            <?php echo __("If it's your first WooCommerce installtion, you will be redirected to installation Wizard. You need to complete all steps and create or import at least one product in order to generate requests and see email html template.", 'more-better-reviews-for-woocommerce') ?>
        </p>
        <?php
        $woocommerce_notice = ob_get_clean();

        return array(
            'label' => 'WooCommerce',
            'link' => 'https://wordpress.org/plugins/woocommerce/',
            'zip_path' => 'https://downloads.wordpress.org/plugin/woocommerce.3.8.1.zip',
            'slug' => 'woocommerce/woocommerce.php',
            'author' => __('By <a rel="nofollow" href="https://woocommerce.com">Automattic</a>', 'more-better-reviews-for-woocommerce'),
            'notice' => $woocommerce_notice,
        );
    }

    public static function required_plugins() {
        if( !function_exists('is_plugin_active') ) {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        $required_plugins = Wtsr_Required_Plugins::get_required_plugins();

        foreach ($required_plugins as $slug => $data) {
            if (is_plugin_active($data['slug'])) {
                unset($required_plugins[$slug]);
            }
        }

        return $required_plugins;
    }

    public static function install_and_activate_plugin($plugin) {
        if ('wp2leads' === $plugin) {
            $plugin_data = Wtsr_Required_Plugins::get_required_plugins_wp2leads();
        } else {
            $plugin_data = Wtsr_Required_Plugins::get_required_plugins_woocommerce();
        }

        $plugin_zip = $plugin_data['zip_path'];
        $plugin_slug = $plugin_data['slug'];

        if (!Wtsr_Required_Plugins::is_plugin_installed($plugin_slug)) {
            $installed = Wtsr_Required_Plugins::install_plugin($plugin_zip);

            if (!is_wp_error( $installed ) && $installed) {
                Wtsr_Required_Plugins::upgrade_plugin( $plugin_slug );
                $installed = true;
            }
        } elseif(Wtsr_Required_Plugins::is_plugin_newest_version($plugin_slug)) {
            $installed = true;
        } else {
            Wtsr_Required_Plugins::upgrade_plugin( $plugin_slug );
            $installed = true;
        }

        if ( !is_wp_error( $installed ) && $installed ) {
            $activate = activate_plugin( $plugin_slug );

            if ( is_null($activate) ) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function install_plugin( $plugin_zip ) {
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        wp_cache_flush();

        $upgrader = new Plugin_Upgrader(new Wtsr_Quiet_Skin());

        $installed = $upgrader->install( $plugin_zip );

        return $installed;
    }

    public static function upgrade_plugin( $plugin_slug ) {
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        wp_cache_flush();

        $upgrader = new Plugin_Upgrader(new Wtsr_Quiet_Skin());

        $upgraded = $upgrader->upgrade( $plugin_slug );

        return $upgraded;
    }

    public static function is_plugin_installed( $plugin_slug ) {
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $all_plugins = get_plugins();

        if ( !empty( $all_plugins[$plugin_slug] ) ) {
            return true;
        } else {
            return false;
        }
    }

    public static function is_plugin_active( $plugin_slug ) {
        if( !function_exists('is_plugin_active') ) {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        if (is_plugin_active($plugin_slug)) {
            return true;
        }

        return false;
    }

    public static function is_plugin_newest_version($plugin_slug) {
        $current = get_site_transient( 'update_plugins' );

        if ( ! isset( $current->response[ $plugin_slug ] ) ) {
            return true;
        }

        return false;
    }
}

if (!class_exists('WP_Upgrader_Skin')) {
    include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php';
}



if(version_compare(get_bloginfo('version'),'5.3', '>=') ) {
    class Wtsr_Quiet_Skin extends WP_Upgrader_Skin {
        public function feedback($string, ...$args) {}
    }
} else {
    class Wtsr_Quiet_Skin extends WP_Upgrader_Skin {
        public function feedback($string) {}
    }
}