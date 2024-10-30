<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      0.0.1
 *
 * @package    Wtsr
 * @subpackage Wtsr/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      0.0.1
 * @package    Wtsr
 * @subpackage Wtsr/includes
 * @author     Tobias Conrad <tc@santegra.de>
 */
class Wtsr_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    0.0.1
	 */
	public function load_plugin_textdomain() {

        add_filter( 'plugin_locale', 'Wtsr_i18n::check_de_locale');

		load_plugin_textdomain(
			'more-better-reviews-for-woocommerce',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

        remove_filter( 'plugin_locale', 'Wtsr_i18n::check_de_locale');
	}

    public static function check_de_locale($domain) {
        $site_lang = get_user_locale();
        $de_lang_list = array(
            'de_CH_informal',
            'de_DE_formal',
            'de_AT',
            'de_CH',
            'de_DE'
        );

        if (in_array($site_lang, $de_lang_list)) return 'de_DE';
        return $domain;
    }
}
