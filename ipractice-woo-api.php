<?php
/**
 * @package IpracticeApi
 * */
/*
Plugin Name: WooCommerce ipractice api
Plugin URI:  https://avidalal.net
Description: Send an order data each time an order with is completed.
Version:     1.0.0
Author:      Avi Dalal
Author URI:  https://avidalal.net
License:     GPL2
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

 if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
if( file_exists( dirname(__FILE__). '/vendor/autoload.php')){
	require_once dirname(__FILE__). '/vendor/autoload.php';
}



class IpracticeAPI
{
	public $plugin;
	
	function __construct(){
		$this->plugin = plugin_basename(__FILE__);
	}
	
	function enqueue(){
		wp_enqueue_style( 'ipracticestyle' , plugins_url( '/assets/style.css', __FILE__));
		
	}
	function register_admin(){
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue') );
		add_action( 'woocommerce_order_status_changed', array( 'IpracticeCall','woo_call_ipractice_api'), 10, 1);
		add_filter( 'manage_edit-shop_order_columns', array( 'OrderListData','custom_shop_order_column'), 20 );
		add_action( 'manage_shop_order_posts_custom_column' , array( 'OrderListData','custom_orders_list_column_content'), 20, 2 );
		add_filter( "plugin_action_links_$this->plugin", array($this, 'settings_link') );
	}

	function settings_link( $links ){
		$settings_link = '<a href="admin.php?page=ipracticeAPI">Settings</a>';
		array_push($links, $settings_link);
		return $links;
	}


}
// Create a new table
function plugin_table(){

	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
 
	$tablename = $wpdb->prefix."ipracticekeys";
 
	$sql = "CREATE TABLE $tablename (
	  id mediumint(11) NOT NULL AUTO_INCREMENT,
	  ip_key varchar(30) NOT NULL,
	  order_id int(15) NOT NULL,
	  PRIMARY KEY (id)
	) $charset_collate;";
 
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
 dbDelta( $sql );
 
 }
 register_activation_hook( __FILE__, 'plugin_table' );

require_once plugin_dir_path( __FILE__ ) . 'inc/IpracticeCall.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/OrderListData.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/Pages.php';

if( class_exists('IpracticeAPI')){
	$ipracticeApi = new IpracticeAPI();
	$ipracticeApi->register_admin();
	$pages = new Pages();
	$pages->register();
}







