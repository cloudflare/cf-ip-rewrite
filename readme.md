# CloudFlare PHP IP Rewriting 

This module makes it easy for developers to add rewrite CloudFlare IP Addresses for actual end-user IP Addresses at the application layer. It is recommended to either install mod_cloudflare for Apache or use nginx rewrite rules (https://support.cloudflare.com/hc/en-us/articles/200170706-Does-CloudFlare-have-an-IP-module-for-Nginx-) if possible.

For those cases, where the IP can not be guaranteed to be rewritten by one of these alternate means, this module can be used to rewrite the IP address.

### How it works
    
    $is_cf = CloudFlare\IpRewrite::isCloudFlare();
    $rewritten_ip = CloudFlare\IpRewrite::getRewrittenIP();
    $original_ip = CloudFlare\IpRewrite::getOriginalIP();
    
The class exposes three methods for interaction. Calling `getRewrittenIP()` will try to rewrite the IP. If the IP is rewritten, `$_SERVER["REMOTE_ADDR"]` will be updated to reflect the end-user's IP address.

`CloudFlare\IpRewrite::isCloudFlare();` returns `true` if the `CF_CONNECTING_IP` header is present in the request.

`CloudFlare\IpRewrite::getRewrittenIP()` triggers rewrite action. Without triggering rewrite action no rewrite will happen. Returns the rewritten ip address if a rewrite occurs, otherwise it will return `null`. 

`CloudFlare\IpRewrite::getOriginalIP()` returns the original ip address from `$_SERVER["REMOTE_ADDR"]`. Must be called after rewrite action is triggered. 

### How to use

```

    if (CloudFlare\IpRewrite::isCloudFlare()) {
        // First call getRewrittenIP() to trigger the rewrite action.
        $rewritten_ip = CloudFlare\IpRewrite::getRewrittenIP();
        if (!isset($rewritten_ip)) {
            // Something wrong happend. Rewrite was not successful.

        }

        // Get original ip after rewrite action
        $original_ip = CloudFlare\IpRewrite::getOriginalIP();
    }

```

#### Caution
`getRewrittenIP()` triggers rewrite action only once per lifetime. If it's called multiple times it'll return the first result regardless if a change happend after the first call. Since rewrite action was not triggered `getOriginalIP()` will return the first the original ip.

### Testing this module

This module comes with a set of tests that can be run using phpunit. To run the tests, run `composer install` on the package and then one of the following commands:

#### Basic Tests

    composer test
    
#### With code coverage report in `coverage` folder

    vendor/bin/phpunit -c phpunit.xml.dist --coverage-html coverage
