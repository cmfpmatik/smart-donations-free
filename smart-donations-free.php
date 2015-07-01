<?php
/**
 * Plugin Name: Smart Donations Free
 * Plugin URI: http://dev.oarces.fr/wordpress/plugins-wp/smart-donations-free-plugin-forked-from-smart-donations-plugin/
 * Description: Place diferent form of donations on your blog...
 * Author: RedNao (original) & Oarces Dev (Free version)
 * Author URI: http://dev.oarces.fr/
 * Version: 4.0.10
 * Text Domain: SmartDonationsFree
 * Domain Path: /languages/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0
 * Slug: smartdonationsfree
 */

/**
 *	Copyright (C) 2012-2013 RedNao (email: contactus@rednao.com)
 *
 *	This program is free software; you can redistribute it and/or
 *	modify it under the terms of the GNU General Public License
 *	as published by the Free Software Foundation; either version 2
 *	of the License, or (at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program; if not, write to the Free Software
 *	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Thanks to:
 * Jakub Stacho (http://www.iconfinder.com/iconsets/checkout-icons#readme)
 * Eggib (http://openclipart.org/detail/174878/)
 * Aha-Soft (http://www.iconfinder.com/iconsets/24x24-free-pixel-icons#readme)
 * Kevin Liew (http://www.queness.com/post/106/jquery-tabbed-interfacetabbed-structure-menu-tutorial)
 * Marcis Gasuns (http://led24.de/iconset/)
 */

require_once('smart-donations-config.php');
require_once('smart-donations-ajax.php');
require_once('smart-donations-widget.php');
require_once('smart-donations-progress-widget.php');


register_activation_hook(__FILE__,'SMARTFREE_DONATIONS_plugin_was_activated');
add_action('admin_init','SMARTFREE_DONATIONS_plugin_was_activated');
add_shortcode('shortdonations','rednao_SMARTFREE_DONATIONS_short_code');
add_shortcode('shortdprogress','rednao_SMARTFREE_DONATIONS_progress_short_code');
add_shortcode('shortddonwall','rednao_SMARTFREE_DONATIONS_wall_short_code');

add_action('init', 'rednao_SMARTFREE_DONATIONS_init');
add_action('admin_menu','SMARTFREE_DONATIONS_create_menu');
add_action( 'wp_ajax_rednao_SMARTFREE_DONATIONS_save', 'rednao_SMARTFREE_DONATIONS_save' );
add_action( 'wp_ajax_rednao_SMARTFREE_DONATIONS_list', 'rednao_SMARTFREE_DONATIONS_list' );
add_action( 'wp_ajax_rednao_SMARTFREE_DONATIONS_add_campaign', 'rednao_SMARTFREE_DONATIONS_add_campaign' );
add_action( 'wp_ajax_rednao_SMARTFREE_DONATIONS_edit_campaign', 'rednao_SMARTFREE_DONATIONS_edit_campaign' );
add_action( 'wp_ajax_rednao_SMARTFREE_DONATIONS_save_progress_bar','rednao_SMARTFREE_DONATIONS_save_progress_bar');
add_action( 'wp_ajax_rednao_SMARTFREE_DONATIONS_execute_analytics','rednao_SMARTFREE_DONATIONS_execute_analytics');
add_action( 'wp_ajax_rednao_SMARTFREE_DONATIONS_execute_analytics_list','rednao_SMARTFREE_DONATIONS_execute_analytics_list');
add_action( 'wp_ajax_rednao_SMARTFREE_DONATIONS_execute_analytics_op','rednao_SMARTFREE_DONATIONS_execute_analytics_op');
add_action( 'wp_ajax_rednao_smart_progress_donations_list','rednao_smart_progress_donations_list');
add_action( 'wp_ajax_rednao_SMARTFREE_DONATIONS_save_form_values','rednao_SMARTFREE_DONATIONS_save_form_values');
add_action( 'wp_ajax_nopriv_rednao_SMARTFREE_DONATIONS_save_form_values','rednao_SMARTFREE_DONATIONS_save_form_values');
add_action( 'wp_ajax_rednao_SMARTFREE_DONATIONS_campaign_list','rednao_SMARTFREE_DONATIONS_campaign_list');

add_action('http_request_args', 'no_ssl_http_request_args', 10, 2);
function no_ssl_http_request_args($args, $url) {
    $args['sslverify'] = false;
    return $args;
}



function SMARTFREE_DONATIONS_create_menu(){
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('query-ui-dialog');
    add_menu_page('Smart  Free Donations','Smart Free Donations',SMARTFREE_DONATIONS_REQUIRED_ROLE,'SMARTFREE_DONATIONS_menu','rednao_smartfree_donations_donation_buttons',plugin_dir_url(__FILE__).'images/smartDonationsIcon.png');
    add_submenu_page('SMARTFREE_DONATIONS_menu','Campaigns','Campaigns',SMARTFREE_DONATIONS_REQUIRED_ROLE,__FILE__.'campaigns', 'rednao_SMARTFREE_DONATIONS_campaigns');
    add_submenu_page('SMARTFREE_DONATIONS_menu','Progress Indicators','Progress Indicators',SMARTFREE_DONATIONS_REQUIRED_ROLE,__FILE__.'progress_indicators', 'rednao_SMARTFREE_DONATIONS_progress_indicators');
    add_submenu_page('SMARTFREE_DONATIONS_menu','Analytics','Analytics',SMARTFREE_DONATIONS_REQUIRED_ROLE,__FILE__.'analytics', 'rednao_SMARTFREE_DONATIONS_analytics');
    add_submenu_page('SMARTFREE_DONATIONS_menu','Settings','Settings',SMARTFREE_DONATIONS_REQUIRED_ROLE,__FILE__.'settings', 'rednao_SMARTFREE_DONATIONS_settings');


	do_action('add_SMARTFREE_DONATIONS_screens');
}

function rednao_SMARTFREE_DONATIONS_init()
{
    if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') ) {
        return;
    }

    if ( get_user_option('rich_editing') == 'true') {
        add_filter( 'mce_external_plugins', 'rednao_SMARTFREE_DONATIONS_add_plugin' );
        add_filter( 'mce_buttons', 'rednao_SMARTFREE_DONATIONS_register_button' );
    }
}

function rednao_SMARTFREE_DONATIONS_add_plugin($plugin_array)
{
    wp_enqueue_script('isolated-slider',plugin_dir_url(__FILE__).'js/rednao-isolated-jq.js');
    wp_enqueue_style('smart-donations-Slider',plugin_dir_url(__FILE__).'css/smartDonationsSlider/jquery-ui-1.10.2.custom.min.css');
    $plugin_array['rednao_SMARTFREE_DONATIONS_button']=plugin_dir_url(__FILE__).'js/smartDonationsShortCodeButton.js';
    return $plugin_array;
}

function rednao_SMARTFREE_DONATIONS_register_button($buttons)
{
    $buttons[]="rednao_SMARTFREE_DONATIONS_button";
    return $buttons;
}

function SMARTFREE_DONATIONS_plugin_was_activated()
{
    $dbversion=get_site_option("REDNAO_SMARTFREE_DONATIONS_DB_VERSION");

    global $wpdb;
    if(!$dbversion|| $dbversion<SMARTFREE_DONATIONS_LATEST_DB_VERSION )
    {
        delete_transient("SMARTFREE_DONATIONS_check_again");

        require_once(ABSPATH.'wp-admin/includes/upgrade.php');


        $sql="CREATE TABLE ".SMARTFREE_DONATIONS_TABLE_NAME." (
        donation_id int AUTO_INCREMENT,
        donation_name VARCHAR(200) NOT NULL,
        email VARCHAR(200) NOT NULL,
        donation_type VARCHAR(20) NOT NULL,
        returning_url VARCHAR(2000),
        options TEXT NOT NULL,
        donation_provider VARCHAR(20),
        styles TEXT,
        PRIMARY KEY  (donation_id)
        );";
        dbDelta($sql);

        $wpdb->query("ALTER TABLE ".SMARTFREE_DONATIONS_TRANSACTION_TABLE." DROP INDEX idx_payer_email");
        $wpdb->query("ALTER TABLE ".SMARTFREE_DONATIONS_TRANSACTION_TABLE." DROP INDEX idx_campaign_id");
        $sql="CREATE TABLE ".SMARTFREE_DONATIONS_TRANSACTION_TABLE." (
        transaction_id double AUTO_INCREMENT,
        txn_id VARCHAR(200),
        payer_email VARCHAR(200),
        first_name VARCHAR(50),
        last_name VARCHAR(200),
        mc_fee float NOT NULL,
        mc_gross VARCHAR(20),
        date datetime,
        additional_fields TEXT,
        status char(1),
        campaign_id int,
        form_information MEDIUMTEXT,
        reference_id VARCHAR(200),
        subscr_id VARCHAR(200),
        PRIMARY KEY  (transaction_id),
        KEY idx_payer_email(payer_email),
        is_anonymous tinyint,
        KEY idx_campaign_id(campaign_id)
        );";
        dbDelta($sql);


        $sql="CREATE TABLE ".SMARTFREE_DONATIONS_CAMPAIGN_TABLE." (
        campaign_id double AUTO_INCREMENT,
        name VARCHAR(200) NOT NULL,
        description VARCHAR(200),
        goal double ,
        thank_you_email TEXT,
        email_subject VARCHAR(200),
        email_from VARCHAR(200),
        PRIMARY KEY  (campaign_id)
        );";
        dbDelta($sql);



        $sql="CREATE TABLE ".SMARTFREE_DONATIONS_PROGRESS_TABLE." (
        progress_id int AUTO_INCREMENT,
        progress_name VARCHAR(200) NOT NULL,
        campaign_id VARCHAR(200) NOT NULL,
        progress_type VARCHAR(20) NOT NULL,
        options TEXT NOT NULL,
        styles TEXT,
        PRIMARY KEY  (progress_id)
        );";
        dbDelta($sql);

        update_site_option("REDNAO_SMARTFREE_DONATIONS_DB_VERSION",SMARTFREE_DONATIONS_LATEST_DB_VERSION);
    }
}

function rednao_SMARTFREE_DONATIONS_short_code($attr,$content)
{
    return rednao_SMARTFREE_DONATIONS_load_donation($content,null,true);
}

function rednao_SMARTFREE_DONATIONS_progress_short_code($attr,$content)
{
    return rednao_SMARTFREE_DONATIONS_load_progress($content,null,true);
}

function rednao_SMARTFREE_DONATIONS_wall_short_code($attr,$content)
{
    return rednao_SMARTFREE_DONATIONS_load_wall($content,"",$attr["numberofdonors"],$attr["currencysign"],$attr["decimalsign"],$attr["thousandseparator"],true);
}

function rednao_SMARTFREE_DONATIONS_settings()
{
    include(SMARTFREE_DONATIONS_DIR.'/smart-donations-settings.php');
}

function rednao_smartfree_donations_donation_buttons()
{
    include(SMARTFREE_DONATIONS_DIR.'/smart-donations-list.php');
}

function rednao_SMARTFREE_DONATIONS_campaigns()
{
    include(SMARTFREE_DONATIONS_DIR.'/smart-donations-campaigns.php');
}

function rednao_SMARTFREE_DONATIONS_progress_indicators()
{
        include(SMARTFREE_DONATIONS_DIR.'/smart-donations-progress-indicators.php');
}

function rednao_SMARTFREE_DONATIONS_analytics()
{
        include(SMARTFREE_DONATIONS_DIR.'/smart-donations-analytics.php');
}

require_once('smart-donations-wall-widget.php');

add_filter('query_vars','SMARTFREE_DONATIONS_add_trigger');
function SMARTFREE_DONATIONS_add_trigger($vars) {
	$vars[] = 'smart_donation_ipn_trigger';
	return $vars;
}

add_action('template_redirect', 'SMARTFREE_DONATIONS_check_if_ipn');
function SMARTFREE_DONATIONS_check_if_ipn()
{
	$val=get_query_var('sd_ipn_trigger')?get_query_var('sd_ipn_trigger'):"";
	if($val==""&&isset($_GET["sd_ipn_trigger"]))
		$val=$_GET["sd_ipn_trigger"];
	if(intval($val)==1) {
		require_once SMARTFREE_DONATIONS_DIR.'/ipn/rednao_paypal_ipn.php';
		exit;
	}
}