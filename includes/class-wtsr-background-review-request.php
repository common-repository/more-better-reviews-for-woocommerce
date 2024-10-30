<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wtsr_Background_Review_Request {
    protected static $bg_process;

    public static function init() {
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wtsr-logger.php';
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/background/abstract-class-wtsr-background.php';
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/background/class-wtsr-background-review-request-process.php';

        self::$bg_process = new Wtsr_Background_Review_Request_Process();
    }

    public static function bg_process($order_id) {

        if (is_array($order_id)) {
            foreach ($order_id as $item) {
                self::$bg_process->push_to_queue( array (
                    $item
                ) );
            }
        } else {
            self::$bg_process->push_to_queue( array (
                $order_id
            ) );
        }

        self::$bg_process->save()->dispatch();

        return true;
    }
}

add_action( 'init', array( 'Wtsr_Background_Review_Request', 'init' ) );