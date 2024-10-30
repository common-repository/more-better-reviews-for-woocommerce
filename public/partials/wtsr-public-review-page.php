<?php
global $post;

$wtsr_all_reviews_page_footer_template_editor = get_option('wtsr_all_reviews_page_footer_template_editor', '');
$wtsr_uploaded_image = get_option('wtsr_uploaded_image', '');
$sample = !empty($_GET['sample']) ? sanitize_text_field($_GET['sample']) : false;
$is_sample_page = false;

if (empty($post)) {
    $wtsr_all_reviews_page = get_option('wtsr_all_reviews_page', false);

    if ($wtsr_all_reviews_page) {
        $post = get_post($wtsr_all_reviews_page);
    }
}

if (!empty($sample)) {
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;

    if (md5($current_user_email) === $sample) {
        $is_sample_page = true;
    }
}

$logo_exists = false;
$logo_exists_class = '';

if (!empty($wtsr_uploaded_image)) {
    $image_attributes = wp_get_attachment_image_src( $wtsr_uploaded_image, 'full' );
    $image_src = $image_attributes[0];
    $logo_exists = true;
    $logo_exists_class = ' wtsr_all_reviews_page_logo_exists';
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="profile" href="https://gmpg.org/xfn/11" />
    <?php wp_head(); ?>
</head>

<body <?php body_class('wtsr_all_reviews_page_template' . $logo_exists_class); ?>>
    <?php
    if (!empty($logo_exists)) {
        ?>
        <div id="wtsr_all_reviews_page_header_wrapper"><div id="wtsr_all_reviews_page_logo"><img src="<?php echo $image_src ?>" alt=""></div></div>
        <?php
    }
    ?>

    <div id="wtsr_all_reviews_page_wrapper">
        <div id="wtsr_all_reviews_page_main">
            <div id="wtsr_all_reviews_page_header">
                <?php
                if (!empty($post)) {
                    ?>
                    <div class="wtsr_all_reviews_page_inner">
                        <h1 style="text-align:center;"><?php echo $post->post_title; ?></h1>
                    </div>
                    <?php
                }
                ?>
            </div>

            <div id="wtsr_all_reviews_page_content">
                <div class="wtsr_all_reviews_page_inner">
                    <?php
                    if ($is_sample_page) {
                        ?>
                        <div class="warning_message">
                            <strong>
                                <?php _e( 'This view is only for demonstration how this page will look like for your customers, you can try to send requests and check scrolling to next review product! No comments will be posted!', 'more-better-reviews-for-woocommerce' ) ?>
                            </strong>
                        </div>
                        <?php
                    }

                    if (!empty($post)) {
                        if ( has_shortcode( $post->post_content, 'wtsr_custom_woo_review_page' ) ) {
                            echo do_shortcode($post->post_content);
                        } else {
                            echo wpautop($post->post_content);

                            echo do_shortcode('[wtsr_custom_woo_review_page]');
                        }
                    } else {
                        echo do_shortcode('[wtsr_custom_woo_review_page]');
                    }
                    ?>
                </div>
            </div>
        </div>

        <div id="wtsr_all_reviews_page_footer">
            <div class="wtsr_all_reviews_page_inner">
                <?php
                if (!empty($wtsr_all_reviews_page_footer_template_editor)) {
                    ?>
                    <div class="wtsr_all_reviews_page_site_info">
                        <?php echo wpautop($wtsr_all_reviews_page_footer_template_editor); ?>
                    </div>
                    <?php
                }

                if (false) {
                    ?>
                    <div class="wtsr_all_reviews_page_site_info">
                        <?php $blog_info = get_bloginfo( 'name' ); ?>
                        <?php if ( ! empty( $blog_info ) ) : ?>
                            <a class="site-name" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
                        <?php endif; ?>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>

    <?php wp_footer(); ?>
</body>
</html>
