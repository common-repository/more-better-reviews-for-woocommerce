<?php

class Wtsr_Wc_Review {
    /**
     * @param $data {
     *  @type string $post_ID
     *  @type string $author
     *  @type string $author_email
     *  @type string $author_url
     *  @type string $content
     *  @type string $type
     *  @type string $user_ID
     * }
     *
     * @return int|false|WP_Error The ID of the comment on success, false or WP_Error on failure.
     */
    public static function add_new_review($data) {
        if (
            empty($data['post_ID']) ||
            !isset($data['content']) ||
            empty($data['author_email'])
        ) {
            return false;
        }

        $comment_type = !empty($data['type']) ? $data['type'] : 'review';

        $commentdata = array(
            'comment_post_ID'   => $data['post_ID'],
            'comment_type'  => $comment_type,
            'comment_content'   => sanitize_textarea_field( $data['content'] ) ,
            'comment_author_email'   => $data['author_email'] ,
            'comment_author_url'   => '' ,
            'comment_approved'   => 0 ,
        );

        if (!empty($data['author'])) {
            $commentdata['comment_author'] = $data['author'];
        }

        if (!empty($data['comment_date'])) {
            $commentdata['comment_date'] = $data['comment_date'];
        }

        $comment_id = wp_new_comment( $commentdata, true );

        if (is_wp_error($comment_id)) return false;

        if (!empty($data['meta'])) {
            foreach ($data['meta'] as $name => $value) {
                update_comment_meta( $comment_id, $name, $value );
            }
        }

        return $comment_id;
    }

    public static function get_ts_reviews_uuid($meta_key = 'wtsr_ts_uuid') {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->commentmeta} AS commentmeta LEFT JOIN {$wpdb->comments} AS comments ON commentmeta.comment_id = comments.comment_ID WHERE meta_key = '{$meta_key}'";
        $result = $wpdb->get_results($sql, ARRAY_A);

        return $result;
    }

    public static function get_ts_reviews_uuid_array() {
        $uuids = array();
        $result = self::get_ts_reviews_uuid();

        if (empty($result)) {
            return $uuids;
        }


        foreach ($result as $item) {
            $uuids[] = $item['meta_value'];
        }

        return $uuids;
    }

    public static function get_ts_reviews_uuid_comments_array($service = 'trustedshops') {
        $uuids = array();
        $meta_key = 'wtsr_ts_uuid';
        $result = self::get_ts_reviews_uuid($meta_key);

        if (empty($result)) {
            return $uuids;
        }


        foreach ($result as $item) {
            $uuids[$item['meta_value']] = $item;
        }

        return $uuids;
    }
}