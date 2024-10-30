<?php


class Wtsr_Default_Templates {
    public static function get_default_stars($template = 'one_col') {
        $align = 'left';
        $url = untrailingslashit( plugins_url( '/', WTSR_PLUGIN_FILE ) );
        $star_review_title = TSManager::get_star_review_title();
        $button_colors = TSManager::get_button_colors();
        ob_start();
        if ('one_col' === $template) {
            ?><p style="text-align:left;font-weight:bold;margin-top:0;"><?php _e('Your rating', 'more-better-reviews-for-woocommerce') ?></p><?php
        } elseif ('two_col' === $template) {
            $align = 'center';
            ?><p style="text-align:center;font-weight:bold;margin-top:0;"><?php _e('Your rating', 'more-better-reviews-for-woocommerce') ?></p><?php
        }
        ?><div
                style="text-align:<?php echo $align ?>;"><div style="margin-bottom:15px;">
                    <img title="<?php echo $star_review_title['five_star'] ?>" src="<?php echo $url . '/admin/img/five_star.png'; ?>" alt=""> <span class="five_star_rating" title="<?php echo $star_review_title['five_star'] ?>" style="display:inline-block!important;width:200px!important;max-width:100%!important;background-color:<?php echo $button_colors['bg_color']; ?>!important;color:<?php echo $button_colors['text_color']; ?>!important;text-decoration:none!important;line-height: 16px!important;font-size: 16px!important;padding:8px 0!important;border-radius:20px!important;text-align:center!important;margin-right:10px!important;"><?php echo $star_review_title['five_star'] ?></span>
                </div>
                <div style="margin-bottom:15px;">
                    <img title="<?php echo $star_review_title['four_star'] ?>" src="<?php echo $url . '/admin/img/four_star.png'; ?>" alt=""> <span class="four_star_rating" title="<?php echo $star_review_title['four_star'] ?>" style="display:inline-block!important;width:200px!important;max-width:100%!important;background-color:<?php echo $button_colors['bg_color']; ?>!important;color:<?php echo $button_colors['text_color']; ?>!important;text-decoration:none!important;line-height: 16px!important;font-size: 16px!important;padding:8px 0!important;border-radius:20px!important;text-align:center!important;margin-right:10px!important;"><?php echo $star_review_title['four_star'] ?></span>
                </div>
                <div style="margin-bottom:15px;">
                    <img title="<?php echo $star_review_title['three_star'] ?>" src="<?php echo $url . '/admin/img/three_star.png'; ?>" alt=""> <span class="three_star_rating" title="<?php echo $star_review_title['three_star'] ?>" style="display:inline-block!important;width:200px!important;max-width:100%!important;background-color:<?php echo $button_colors['bg_color']; ?>!important;color:<?php echo $button_colors['text_color']; ?>!important;text-decoration:none!important;line-height: 16px!important;font-size: 16px!important;padding:8px 0!important;border-radius:20px!important;text-align:center!important;margin-right:10px!important;"><?php echo $star_review_title['three_star'] ?></span>
                </div>
                <div style="margin-bottom:15px;">
                    <img title="<?php echo $star_review_title['two_star'] ?>" src="<?php echo $url . '/admin/img/two_star.png'; ?>" alt=""> <span class="two_star_rating" title="<?php echo $star_review_title['two_star'] ?>" style="display:inline-block!important;width:200px!important;max-width:100%!important;background-color:<?php echo $button_colors['bg_color']; ?>!important;color:<?php echo $button_colors['text_color']; ?>!important;text-decoration:none!important;line-height: 16px!important;font-size: 16px!important;padding:8px 0!important;border-radius:20px!important;text-align:center!important;margin-right:10px!important;"><?php echo $star_review_title['two_star'] ?></span>
                </div>
                <div style="margin-bottom:0;">
                    <img title="<?php echo $star_review_title['one_star'] ?>" src="<?php echo $url . '/admin/img/one_star.png'; ?>" alt=""> <span class="one_star_rating" title="<?php echo $star_review_title['one_star'] ?>" style="display:inline-block!important;width:200px!important;max-width:100%!important;background-color:<?php echo $button_colors['bg_color']; ?>!important;color:<?php echo $button_colors['text_color']; ?>!important;text-decoration:none!important;line-height: 16px!important;font-size: 16px!important;padding:8px 0!important;border-radius:20px!important;text-align:center!important;margin-right:10px!important;"><?php echo $star_review_title['one_star'] ?></span>
                </div></div><?php
        return ob_get_clean();
    }
}