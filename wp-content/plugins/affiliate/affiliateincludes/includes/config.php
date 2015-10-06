<?php
// Uncomment to have the system check all pages and referrers
if(!defined('AFFILIATE_CHECKALL')) define('AFFILIATE_CHECKALL', 'yes');
// Uncomment to have the system set a 'browser-session' cookie if no referrer is found - this reduces server load
// and is recommended if the above setting is un-commented
if(!defined('AFFILIATE_SETNOCOOKIE')) define('AFFILIATE_SETNOCOOKIE', 'yes');
// Pay the affiliate only once
if(!defined('AFFILIATE_PAYONCE')) define('AFFILIATE_PAYONCE', 'yes');
// Force the system to use global tables
if(!defined('AFFILIATE_USE_BASE_PREFIX_IF_EXISTS')) define('AFFILIATE_USE_BASE_PREFIX_IF_EXISTS', 'no');
// Force users using the advanced settings and URL have to validate their URL's before they can use them.
if(!defined('AFFILIATE_VALIDATE_REFERRER_URLS')) define('AFFILIATE_VALIDATE_REFERRER_URLS','no');
// Use Global tables if network activated
if(!defined('AFFILIATE_USE_GLOBAL_IF_NETWORK_ACTIVATED')) define('AFFILIATE_USE_GLOBAL_IF_NETWORK_ACTIVATED','yes');
// The number of days to keep the cookie for
if(!defined('AFFILIATE_COOKIE_DAYS')) define('AFFILIATE_COOKIE_DAYS',30);

if(!defined('AFFILIATE_REPLACE_COOKIE')) define('AFFILIATE_REPLACE_COOKIE','no');

// The key used to make the affiliate reference code
if(!defined('AFFILIATE_REFERENCE_KEY')) define('AFFILIATE_REFERENCE_KEY',35);

if(!defined('AFFILIATE_REFERENCE_PREFIX')) define('AFFILIATE_REFERENCE_PREFIX', '');


?>