<?php
/*

*/


global $ipractice_table_db_version;
$ipractice_table_db_version = '1.1'; // version changed from 1.0 to 1.1

/**
    * register_activation_hook implementation
    *
    * will be called when user activates plugin first time
    * must create needed database tables
    */
function ipractice_table_install()
{
    global $wpdb;
    global $ipractice_table_db_version;

    $table_name = $wpdb->prefix . 'ipracticekeys'; // do not forget about tables prefix

    // sql to create your table
    // NOTICE that:
    // 1. each field MUST be in separate line
    // 2. There must be two spaces between PRIMARY KEY and its name
    //    Like this: PRIMARY KEY[space][space](id)
    // otherwise dbDelta will not work
    $sql = "CREATE TABLE " . $table_name . " (
        id int(11) NOT NULL AUTO_INCREMENT,
        api_key  VARCHAR(100) NOT NULL,
        order_id int(11),
        sku VARCHAR(30)
        PRIMARY KEY  (id)
    );";

    // we do not execute sql directly
    // we are calling dbDelta which cant migrate database
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // save current database version for later use (on upgrade)
    add_option('ipractice_table_db_version', $ipractice_table_db_version);

    /**
        * [OPTIONAL] Example of updating to 1.1 version
        *
        * If you develop new version of plugin
        * just increment $ipractice_table_db_version variable
        * and add following block of code
        *
        * must be repeated for each new version
        * in version 1.1 we change email field
        * to contain 200 chars rather 100 in version 1.0
        * and again we are not executing sql
        * we are using dbDelta to migrate table changes
        */
    $installed_ver = get_option('ipractice_table_db_version');
    if ($installed_ver != $ipractice_table_db_version) {
        $sql = "CREATE TABLE " . $table_name . " (
            id int(11) NOT NULL AUTO_INCREMENT,
            api_key  VARCHAR(100) NOT NULL,
            order_id int(11),
            sku VARCHAR(30)
            PRIMARY KEY  (id)
            );";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // notice that we are updating option, rather than adding it
        update_option('ipractice_table_db_version', $ipractice_table_db_version);
    }
}

register_activation_hook(__FILE__, 'ipractice_table_install');



/**
    * Trick to update plugin database, see docs
    */
function ipractice_table_update_db_check()
{
    global $ipractice_table_db_version;
    if (get_site_option('ipractice_table_db_version') != $ipractice_table_db_version) {
        ipractice_table_install();
    }
}

add_action('plugins_loaded', 'ipractice_table_update_db_check');

/**
    * PART 2. Defining Custom Table List
    * ============================================================================
    *
    * In this part you are going to define custom table list class,
    * that will display your database records in nice looking table
    *
    * http://codex.wordpress.org/Class_Reference/WP_List_Table
    * http://wordpress.org/extend/plugins/custom-list-table-example/
    */

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
    * Ipractice_Table_List_Table class that will display our custom table
    * records in nice table
    */
class Ipractice_Table_List_Table extends WP_List_Table
{
    /**
        * [REQUIRED] You must declare constructor and give some basic params
        */
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'person',
            'plural' => 'ip_key',
        ));
    }

    /**
        * [REQUIRED] this is a default column renderer
        *
        * @param $item - row (key, value array)
        * @param $column_name - string (key)
        * @return HTML
        */
    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    /**
        * [OPTIONAL] this is example, how to render specific column
        *
        * method name must be like this: "column_[column_name]"
        *
        * @param $item - row (key, value array)
        * @return HTML
        */
    function column_ip_key($item)
    {
        return '<em>' . $item['ip_key'] . '</em>';
    }
    function column_sku($item)
    {
        return '<em>' . $item['sku'] . '</em>';
    }
    /**
        * [OPTIONAL] this is example, how to render column with actions,
        * when you hover row "Edit | Delete" links showed
        *
        * @param $item - row (key, value array)
        * @return HTML
        */
    function column_order_id($item)
    {
        // links going to /admin.php?page=[your_plugin_page][&other_params]
        // notice how we used $_REQUEST['page'], so action will be done on curren page
        // also notice how we use $this->_args['singular'] so in this example it will
        // be something like &person=2
        $actions = array(
            'edit' => sprintf('<a href="?page=ip_key_form&id=%s">%s</a>', $item['id'], __('Edit', 'ipractice_table')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete', 'ipractice_table')),
        );

        return sprintf('%s %s',
            $item['order_id'],
            $this->row_actions($actions)
        );
    }

    /**
        * [REQUIRED] this is how checkbox column renders
        *
        * @param $item - row (key, value array)
        * @return HTML
        */
    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }

    /**
        * [REQUIRED] This method return columns to display in table
        * you can skip columns that you do not want to show
        * like content, or description
        *
        * @return array
        */
    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'id' => __('id', 'ipractice_table'),
            'ip_key' => __('ip_key', 'ipractice_table'),
            'order_id' => __('order_id', 'ipractice_table'),
            'sku' => __('sku', 'ipractice_table'),
        );
        return $columns;
    }

    /**
        * [OPTIONAL] This method return columns that may be used to sort table
        * all strings in array - is column names
        * notice that true on name column means that its default sort
        *
        * @return array
        */
    function get_sortable_columns()
    {
        $sortable_columns = array(
            'id' => array('id', true),
            'ip_key' => array('ip_key', true),
            'order_id' => array('order_id', false),
            'sku' => array('sku', false),

        );
        return $sortable_columns;
    }

    /**
        * [OPTIONAL] Return array of bult actions if has any
        *
        * @return array
        */
    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    /**
        * processes bulk actions
        * it can be outside of class
        * it can not use wp_redirect coz there is output already
        * in this example we are processing delete action
        * message about successful deletion will be shown on page in next part
        */
    function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ipracticekeys'; // do not forget about tables prefix

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
    }

    /**
        * [REQUIRED] This is the most important method
        *
        * It will get rows from database and prepare them to be showed in table
        */
    function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ipracticekeys'; // do not forget about tables prefix

        $per_page = 20; // constant, how much records will be shown per page

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        // here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);

        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();

        // will be used in pagination settings
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");

        // prepare query params, as usual current page, order by and order direction
        $paged = isset($_REQUEST['paged']) ? ($per_page * max(0, intval($_REQUEST['paged']) - 1)) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'order_id';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';

        // [REQUIRED] define $items array
        // notice that last argument is ARRAY_A, so we will retrieve array
       
        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);
        //print_r($this->items);
        // [REQUIRED] configure pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
    }
}


/**
    * List page handler
    *
    * This function renders our custom table
    * Notice how we display message about successfull deletion
    * Actualy this is very easy, and you can add as many features
    * as you want.
    *
    * Look into /wp-admin/includes/class-wp-*-list-table.php for examples
    */
function ipractice_table_ip_key_page_handler()
{
    global $wpdb;

    $table = new Ipractice_Table_List_Table();
    $table->prepare_items();

    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'ipractice_table'), count(array('id' =>$_REQUEST['id']))) . '</p></div>';
    }
    ?>
<div class="wrap">

    <?php
    global $wpdb;

    // Table name
    $tablename = $wpdb->prefix."ipracticekeys";

    // Import CSV
    if(isset($_POST['butimport'])){

    // File extension
    $extension = pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION);

    // If file extension is 'csv'
    if(!empty($_FILES['import_file']['name']) && $extension == 'csv'){

        $totalInserted = 0;

        // Open file in read mode
        $csvFile = fopen($_FILES['import_file']['tmp_name'], 'r');

        fgetcsv($csvFile); // Skipping header row

        // Read file
        while(($csvData = fgetcsv($csvFile)) !== FALSE){
            $csvData = array_map("utf8_encode", $csvData);
            //print_r($csvData);
            // Row column length
            $dataLen = count($csvData);

            // Skip row if length != 4
            //if( !($dataLen == 4) ) continue;

            // Assign value to variables
            $ip_key = trim($csvData[0]);
            
            // Check record already exists or not
            $cntSQL = "SELECT count(*) as count FROM {$tablename} where ip_key='".$ip_key."'";
            
            $record = $wpdb->get_results($cntSQL, OBJECT);

            if($record[0]->count==0){

                // Check if variable is empty or not
                if(!empty($ip_key)) {
                  
                    
                    $wpdb->show_errors();
                    // Insert Record
                    $wpdb->insert($tablename, array(
                        'ip_key' =>$ip_key
                        
                    ),array( '%s'));
                    if($wpdb->insert_id > 0){
                        $totalInserted++;
                    }
                    
                }

            }

        }
        echo "<h3 style='color: green;'>Total record Inserted : ".$totalInserted."</h3>";


    }else{
        echo "<h3 style='color: red;'>Invalid Extension</h3>";
    }

    }

    ?>
    <h2>All Entries</h2>

    <!-- Form -->
    <form method='post' action='<?= $_SERVER['REQUEST_URI']; ?>' enctype='multipart/form-data'>
    <input type="file" name="import_file" >
    <input type="submit" name="butimport" value="Import">
    </form>


    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('ip_key', 'ipractice_table')?> <a class="add-new-h2"
                                    href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=ip_key_form');?>"><?php _e('Add new', 'ipractice_table')?></a>
    </h2>
    <?php echo $message; ?>

    <form id="ip_key-table" method="GET">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <?php $table->display() ?>
    </form>

</div>
<?php
}


/**
    * Simple function that validates data and retrieve bool on success
    * and error message(s) on error
    *
    * @param $item
    * @return bool|string
    */
function ipractice_table_validate_person($item)
{
    $messages = array();

    if (empty($item['ip_key'])) $messages[] = __('ip_key is required', 'ipractice_table');
    //...

    if (empty($messages)) return true;
    return implode('<br />', $messages);
}

/**
    * Do not forget about translating your plugin, use __('english string', 'your_uniq_plugin_name') to retrieve translated string
    * and _e('english string', 'your_uniq_plugin_name') to echo it
    * in this example plugin your_uniq_plugin_name == ipractice_table
    *
    * to create translation file, use poedit FileNew catalog...
    * Fill name of project, add "." to path (ENSURE that it was added - must be in list)
    * and on last tab add "__" and "_e"
    *
    * Name your file like this: [my_plugin]-[ru_RU].po
    *
    * http://codex.wordpress.org/Writing_a_Plugin#Internationalizing_Your_Plugin
    * http://codex.wordpress.org/I18n_for_WordPress_Developers
    */
function ipractice_table_languages()
{
    load_plugin_textdomain('ipractice_table', false, dirname(plugin_basename(__FILE__)));
}

add_action('init', 'ipractice_table_languages');

    ipractice_table_ip_key_page_handler();

