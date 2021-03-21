<?php
/**
 * @package IpracticeApi
 * */


class Pages{
    public $plugin;

    public $settings = array();
     
    public $sections = array();
       
    public $fields   = array();

	function __construct(){
        $this->plugin = plugin_basename(__FILE__);
        
        
	}   
	public function register(){
        add_action('admin_menu', array($this,'add_admin_pages'));
        add_action( 'admin_init', array( $this, 'setup_sections' ) );
        add_action( 'admin_init', array( $this, 'setup_fields' ) );
        add_action( 'admin_init', array( $this,'register_my_cool_plugin_settings') );
   
    }

    public function add_admin_pages(){
        add_menu_page( 'ipractice API',            // Page title
        'ipractice API',            // Menu title
        'manage_options',       // Minimum capability (manage_options is an easy way to target administrators)
        'ipracticeAPI',            // Menu slug
        array($this,'ipractice_plugin_options'),     // Callback that prints the markup
        'dashicons-welcome-learn-more',
        '10'
        );
        add_submenu_page( 'ipracticeAPI', //parent_slug
        'ipractice key table',            // Page title
        'ipractice key table',            // Menu title
        'manage_options',       // Minimum capability (manage_options is an easy way to target administrators)
        'keyTable',           // Menu slug
        array($this,'keyTable_options')     // Callback that prints the markup
        );
        add_submenu_page( 'ipracticeAPI', //parent_slug
        __('Add new', 'ip_key_table'),            // Page title
        __('Add new', 'ip_key_table'),            // Menu title
        'manage_options',       // Minimum capability (manage_options is an easy way to target administrators)
        'ip_key_form',           // Menu slug
        array($this,'keyTable_form_options')     // Callback that prints the markup
        );
    }


    
    public function ipractice_plugin_options()
    {
        require_once plugin_dir_path( __FILE__ ) . 'admin-pages/admin.php';
    }
    public function keyTable_options()
    {
        require_once plugin_dir_path( __FILE__ ) . 'admin-pages/KeysTable.php';
    }
    public function keyTable_form_options()
    {
        require_once plugin_dir_path( __FILE__ ) . 'admin-pages/ip_key_form.php';
    }
    public function setup_sections() {
        add_settings_section( 'ipractice_settings_section', 'ipractice Settings', array( $this, 'ipractice_settings_callback' ), 'ipractice_fields' );
    }
    public function ipractice_settings_callback( $arguments ) {
       
        echo 'Ipractice Setitngs fields';
    }
    public function setup_fields() {
        add_settings_field( 'ipractice_ep_field', 'Ipractice End Point URL:', array( $this, 'field_callback' ), 'ipractice_fields', 'ipractice_settings_section' );
    }
    public function field_callback( $arguments ) {
        $ipractice_ep = esc_attr( get_option('ipractice_ep'));
        echo '<input size="80" placeholder="https://example.com" name="ipractice_ep" id="ipractice_ep" type="url" value="' .  $ipractice_ep . '" />';

      
    }
    function register_my_cool_plugin_settings() {
        register_setting( 'ipractice_fields', 'ipractice_ep' );
    }

 

	
}