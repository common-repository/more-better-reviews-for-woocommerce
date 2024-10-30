<?php
class Wtsr_License_Fake {
    // TODO - Remove it afterwards
    public static function server_request($license_email, $license_key, $site = '', $event = 'test') {
        return Wtsr_License_Fake::$event();
    }

    public static function activate() {
        return array(
            'code'      =>  200,
            'status'    => 'active',
            'message'   => 'Your site have been activated successfully',
            'body'      => array(
                'version'     =>  'pro'
            )
        );
    }

    public static function deactivate() {
        // return false;

        return array(
            'code'      =>  200,
            'status'    => 'active',
            'message'   => 'Your site have been activated successfully',
            'body'      => array(
                'version'     =>  'pro'
            )
        );
    }

    public static function get_all() {
        // return false;
        $site_list = array(
            0 => array(
                'site_url' => 'wp2leads.loc',
                'status' => '1',
            ),
            1 => array(
                'site_url' => 'wp2leads.loc',
                'status' => '1',
            ),
            2 => array(
                'site_url' => 'wp2leads.loc',
                'status' => '1',
            ),
        );

        return array (
            'code'      =>  200,
            'status'    => 'site_list',
            'message'   => 'List of active sites',
            'body'      => array(
                'site_list'      => $site_list
            )
        );
    }

    public static function count_all() {
        $total = 3;

        return array(
            'code'      =>  200,
            'status'    => 'number_all_licenses',
            'message'   => $total . ' sites available',
            'body'      => array(
                'total'     =>  $total
            )
        );
    }

    public static function count_available() {
        $total = 0;

        return array(
            'code'      =>  200,
            'status'    => 'number_all_licenses',
            'message'   => $total . ' sites available',
            'body'      => array(
                'total'     =>  $total
            )
        );
    }

    public static function get_fake_product_reviews_json() {
        global $wpdb;
        $sql = "SELECT * FROM " .$wpdb->prefix . "postmeta WHERE `meta_key` LIKE '%sku%'";
        $result = $wpdb->get_results($sql, ARRAY_A);

        // var_dump($result);
        ob_start();
        ?>{
  "response" : {
    "code": 200,
    "data": {
      "shop": {
        "tsId": "XDCA254DEFA014E61F035DF1412E17FDD",
        "url": "www.barf-alarm.de",
        "name": "barf-alarm.de",
        "languageISO2": "de",
        "targetMarketISO3": "DEU",
        "products": [

        ]
      }
    },
    "message": "SUCCESS",
    "responseInfo": {
    "apiVersion": "2.4.18"
    },
    "status": "SUCCESS"
  }
}<?php
        $buffer = ob_get_clean();
        return $result;
        return $buffer;
    }
}