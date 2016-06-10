<?php

namespace CloudFlare;

require_once 'vendor/autoload.php';

class IpRewrite
{
    protected static $is_cf = null;
    protected static $original_ip = false;
    protected static $rewritten_ip = false;

    // Found at https://www.cloudflare.com/ips/
    protected static $cf_ipv4 = array(
        '199.27.128.0/21',
        '173.245.48.0/20',
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '141.101.64.0/18',
        '108.162.192.0/18',
        '190.93.240.0/20',
        '188.114.96.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '162.158.0.0/15',
        '104.16.0.0/12',
        '172.64.0.0/13',
        '131.0.72.0/22',
    );

    protected static $cf_ipv6 = array(
        '2400:cb00::/32',
        '2606:4700::/32',
        '2803:f800::/32',
        '2405:b500::/32',
        '2405:8100::/32',
    );

    // Returns boolean
    public static function isCloudFlare()
    {
        self::rewrite();

        return self::$is_cf;
    }

    // Returns IP Address or false on error
    public static function getRewrittenIP()
    {
        self::rewrite();

        return self::$rewritten_ip;
    }

    // Returns IP Address or false on error
    public static function getOriginalIP()
    {
        self::rewrite();

        return self::$original_ip;
    }

    // Helper method for testing, should not be used in production
    public static function reset()
    {
        self::$is_cf = null;
        self::$original_ip = false;
        self::$rewritten_ip = false;
    }

    /*
    * Protected function to handle the rewriting of CloudFlare IP Addresses to end-user IP Addresses
    * 
    * ** NOTE: This function will ultimately rewrite $_SERVER["REMOTE_ADDR"] if the site is on CloudFlare
    */
    protected static function rewrite()
    {
        // only should be run once per page load
        if (self::$is_cf !== null) {
            return;
        }

        // Set this based on the presence of the header, so it is true even if IP has already been rewritten
        self::$is_cf = isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? true : false;

        // Store original remote address in $original_ip
        self::$original_ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;

        // Process original_ip if on cloudflare
        if (self::$is_cf && self::$original_ip) {
            $ip_ranges = self::$cf_ipv4;
            if (IpUtils::isIpv6(self::$original_ip)) {
                $ip_ranges = self::$cf_ipv6;
            }

            foreach ($ip_ranges as $range) {
                if (IpUtils::checkIp(self::$original_ip, $range)) {
                    self::$rewritten_ip = $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
                    break;
                }
            }
        }
    }
}
