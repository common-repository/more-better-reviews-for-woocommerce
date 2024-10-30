<?php
/**
 * @var $locale_short
 */


$filename = plugin_dir_path( WTSR_PLUGIN_FILE ) . 'admin/partials/welcome-page/tutorial-'.$locale_short.'.php';

if (!file_exists($filename)) {
    $filename = plugin_dir_path( WTSR_PLUGIN_FILE ) . 'admin/partials/welcome-page/tutorial-en.php';
}

?>

<div id="overview-template">
    <?php
    ob_start();
    include $filename;
    echo apply_filters('the_content', ob_get_clean());
    ?>
</div>
