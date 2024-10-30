<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      0.0.1
 *
 * @package    Wtsr
 * @subpackage Wtsr/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) exit;
$filename = plugin_dir_path( WTSR_PLUGIN_FILE ) . 'admin/partials/welcome-page/wizard-tutorial-'.$locale_short.'.php';

if (!file_exists($filename)) {
    $filename = plugin_dir_path( WTSR_PLUGIN_FILE ) . 'admin/partials/welcome-page/wizard-tutorial-en.php';
}

?>

<div id="overview-template">
    <?php
    ob_start();
    include $filename;
    echo apply_filters('the_content', ob_get_clean());
    ?>
</div>
