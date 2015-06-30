<?php
global $wpdb;
if(!defined('ABSPATH'))
    die('Forbidden');

define('SMARTFREE_DONATIONS_PLUGIN_NAME',dirname(plugin_basename(__FILE__)));
define('SMARTFREE_DONATIONS_DIR',WP_PLUGIN_DIR.'/'.SMARTFREE_DONATIONS_PLUGIN_NAME);
define('SMARTFREE_DONATIONS_TABLE_NAME',$wpdb->prefix . "SMARTFREE_DONATIONS_donation_item");
define('SMARTFREE_DONATIONS_TRANSACTION_TABLE',$wpdb->prefix . "SMARTFREE_DONATIONS_transaction_table");
define('SMARTFREE_DONATIONS_CAMPAIGN_TABLE',$wpdb->prefix . "SMARTFREE_DONATIONS_campaign_table");
define('SMARTFREE_DONATIONS_PROGRESS_TABLE',$wpdb->prefix . "SMARTFREE_DONATIONS_progress_table");
define('SMARTFREE_DONATIONS_LATEST_DB_VERSION',11);
define('DEVOARCES_URL',"http://rednao.com/");
define('SMARTFREE_DONATIONS_VERSION',11);
define('SMARTFREE_DONATIONS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SMARTFREE_DONATIONS_SANDBOX','n');
define('SMARTFREE_DONATIONS_REQUIRED_ROLE','manage_options')
?>