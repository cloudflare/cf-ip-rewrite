<?php

namespace CloudFlare;

require_once 'vendor/autoload.php';

class IpRewrite
{
    private $is_loaded = false;
    private $original_ip = null;
    private $rewritten_ip = null;

    // Found at https://www.cloudflare.com/ips/
    private $cf_ipv4 = array(
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

    private $cf_ipv6 = array(
        '2400:cb00::/32',
        '2606:4700::/32',
        '2803:f800::/32',
        '2405:b500::/32',
        '2405:8100::/32',
    );

    public function __construct()
    {
        $this->rewrite();
    }

    // Returns boolean
    public function isCloudFlare()
    {
        if (!isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return false;
        }

        return true;
    }

    // Returns IP Address or null on error
    public function getOriginalIP()
    {
        // If $original_ip is not set, return the REMOTE_ADDR
        if (!isset($this->original_ip)) {
            $this->original_ip = $_SERVER['REMOTE_ADDR'];
        }

        return $this->original_ip;
    }

    // Returns IP Address or null on error
    public function getRewrittenIP()
    {
        return $this->rewritten_ip;
    }

    /*
    * Protected function to handle the rewriting of CloudFlare IP Addresses to end-user IP Addresses
    * 
    * ** NOTE: This function will ultimately rewrite $_SERVER["REMOTE_ADDR"] if the site is on CloudFlare
    */
    public function rewrite()
    {
        // only should be run once per page load
        if ($this->is_loaded) {
            return;
        }
        $this->is_loaded = true;

        $is_cf = $this->isCloudFlare();
        if (!$is_cf) {
            return;
        }

        // Store original remote address in $original_ip
        $this->original_ip = $this->getOriginalIP();
        if (!isset($this->original_ip)) {
            return;
        }

        // Process original_ip if on cloudflare
        $ip_ranges = $this->cf_ipv4;
        if (IpUtils::isIpv6($this->original_ip)) {
            $ip_ranges = $this->cf_ipv6;
        }

        foreach ($ip_ranges as $range) {
            if (IpUtils::checkIp($this->original_ip, $range)) {
                $this->rewritten_ip = $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
                break;
            }
        }
    }
}
