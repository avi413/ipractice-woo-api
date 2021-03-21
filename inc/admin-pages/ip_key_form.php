<?php
/**
    * PART 4. Form for adding andor editing row
    *
    * In this part you are going to add admin page for adding andor editing items
    * You cant put all form into this function, but in this example form will
    * be placed into meta box, and if you want you can split your form into
    * as many meta boxes as you want
    *
    * http://codex.wordpress.org/Data_Validation
    * http://codex.wordpress.org/Function_Reference/seleipracticekeysd
    */

/**
    * Form page handler checks is there some data posted and tries to save it
    * Also it renders basic wrapper in which we are callin meta box render
    */
function ipractice_table_ip_key_form_page_handler()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'ipracticekeys'; // do not forget about tables prefix

    $message = '';
    $notice = '';

    // this is default $item which will be used for new records
    $default = array(
        'id' => 0,
        'ip_key' => '',
        'order_id' => '',
        'sku' => '',
    );

    // here we are verifying does this request is post back and have correct nonce
    if ($_POST && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        // combine our default item with request params
        $item = shortcode_atts($default, $_REQUEST);
        // validate data, and if all ok save item to database
        // if id is zero insert otherwise update
        $item_valid = ipractice_table_validate_person($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Item was successfully saved', 'ipractice_table');
                } else {
                    $notice = __('There was an error while saving item', 'ipractice_table');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('Item was successfully updated', 'ipractice_table');
                } else {
                    $notice = __('There was an error while updating item', 'ipractice_table');
                }
            }
        } else {
            // if $item_valid not true it contains error message(s)
            $notice = $item_valid;
        }
    }
    else {
        // if this is not post back we load item to edit or give new one to create
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'ipractice_table');
            }
        }
    }

    // here we adding our custom meta box
    add_meta_box('ip_key_form_meta_box', 'ip_key data', 'ipractice_table_ip_key_form_meta_box_handler', 'ip_key', 'normal', 'default');

    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('key Table', 'ipractice_table')?> <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=keyTable');?>"><?php _e('back to list', 'ipractice_table')?></a>
    </h2>

    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>

    <form id="form" method="POST">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
        <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
        <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    <?php /* And here we call our custom meta box */ ?>
                    <?php do_meta_boxes('ip_key', 'normal', $item); ?>
                    <input type="submit" value="<?php _e('Save', 'ipractice_table')?>" id="submit" class="button-primary" name="submit">
                </div>
            </div>
        </div>
    </form>
</div>
<?php
}

/**
    * This function renders our custom meta box
    * $item is row
    *
    * @param $item
    */
function ipractice_table_ip_key_form_meta_box_handler($item)
{
    ?>

<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
    <tbody>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="ip_key"><?php _e('ip_key', 'ipractice_table')?></label>
        </th>
        <td>
            <input id="ip_key" name="ip_key" type="text" style="width: 95%" value="<?php echo esc_attr($item['ip_key'])?>"
                    size="50" class="code" placeholder="<?php _e('Your ip_key', 'ipractice_table')?>" required>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="order_id"><?php _e('order_id', 'ipractice_table')?></label>
        </th>
        <td>
            <input id="order_id" name="order_id" type="text" style="width: 95%" value="<?php echo esc_attr($item['order_id'])?>"
                    size="50" class="code" placeholder="<?php _e('Your order_id', 'ipractice_table')?>" >
        </td>
        <td>
            <input id="sku" name="sku" type="text" style="width: 95%" value="<?php echo esc_attr($item['sku'])?>"
                    size="50" class="code" placeholder="<?php _e('Your sku', 'ipractice_table')?>" >
        </td>
    </tr>

    </tbody>
</table>
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
ipractice_table_ip_key_form_page_handler();