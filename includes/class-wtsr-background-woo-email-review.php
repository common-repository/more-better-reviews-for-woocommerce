<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wtsr_Background_Woo_Email_Review {
    protected static $bg_process;

    public static function init() {
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wtsr-logger.php';
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/background/abstract-class-wtsr-background.php';
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/background/class-wtsr-background-woo-email-review-process.php';

        self::$bg_process = new Wtsr_Background_Woo_Email_Review_Process();
    }

    public static function bg_process($schedule) {
        $i = 0;
        foreach($schedule as $order_id => $schedule_data) {
            self::$bg_process->push_to_queue( array (
                $order_id, $schedule_data
            ) );

            $i++;
        }

        self::$bg_process->save()->dispatch();

        return $i;
    }
}

add_action( 'init', array( 'Wtsr_Background_Woo_Email_Review', 'init' ) );