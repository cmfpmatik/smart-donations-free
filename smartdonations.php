<?php
/**
 * Plugin Name: Smart Donations
 * Plugin URI: http://rednao.com/smart-donations/
 * Description: Place diferent form of donations on your blog...
 * Author: RedNao
 * Author URI: http://rednao.com
 * Version: 0.5.1
 * Text Domain: SmartDonations
 * Domain Path: /languages/
 * Network: true
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0
 * Slug: backwpup
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
 */

require_once('smart-donations-config.php');
require_once('smart-donations-ajax.php');


register_activation_hook(__FILE__,'rednao_smart_donations_plugin_was_activated');
add_action('admin_menu','rednao_smart_donations_create_menu');
add_action( 'wp_ajax_rednao_smart_donations_save', 'rednao_smart_donations_save' );
add_shortcode('sdonations','rednao_smart_donations_short_code');

require_once('smart-donations-widget.php');

function rednao_smart_donations_create_menu(){
    add_menu_page('Smart Donations','Smart Donations','manage_options',__FILE__,'rednao_smartdonations_settings_page');
    add_submenu_page(__FILE__,'Smart Donations - Add New','Add New','manage_options',__FILE__.'addnew', 'rednao_smart_donations_add_new');
    add_submenu_page(__FILE__,'Smart Donations - Wish List','Wish List','manage_options',__FILE__.'wishlist', 'rednao_smart_donations_wish_list');

}

function rednao_smart_donations_plugin_was_activated()
{
    global $wpdb;
    if( $wpdb->get_var("SHOW TABLES LIKE '".SMART_DONATIONS_TABLE_NAME."'") != SMART_DONATIONS_TABLE_NAME )
    {
        $sql="CREATE TABLE ".SMART_DONATIONS_TABLE_NAME."(
        donation_id int AUTO_INCREMENT,
        donation_name VARCHAR(200) NOT NULL,
        email VARCHAR(200) NOT NULL,
        donation_type VARCHAR(20) NOT NULL,
        returning_url VARCHAR(2000),
        options TEXT NOT NULL,
        PRIMARY KEY  (donation_id)
        );";

        require_once(ABSPATH.'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

function rednao_smart_donations_short_code($attr,$content)
{
    return rednao_smart_donations_load_donation($content,null,true);
}


function rednao_smart_donations_add_new()
{
    include(SMART_DONATIONS_DIR.'/smart-donations-add-new.php');

}

function rednao_smartdonations_settings_page()
{
    include(SMART_DONATIONS_DIR.'/smart-donations-list.php');
}

function rednao_smart_donations_wish_list()
{
    include(SMART_DONATIONS_DIR.'/smart-donations-wishlist.php');
}



?>