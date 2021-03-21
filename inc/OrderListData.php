<?php
/**
 * @package IpracticeApi
 * */


class OrderListData{
    
	// ADDING  NEW COLUMNS WITH THEIR TITLES 
	public static function custom_shop_order_column($columns)
	{
	    $reordered_columns = array();
	
	    // Inserting columns to a specific location
	    foreach( $columns as $key => $column){
	        $reordered_columns[$key] = $column;
	        if( $key ==  'order_status' ){
	            // Inserting after "Status" column
	            $reordered_columns['ipractice-Status'] = __( 'ipractice Status','theme_domain');
	
	        }
	    }
	    return $reordered_columns;
	}
	// Adding custom fields meta data for each new column
	public static function custom_orders_list_column_content( $column, $post_id )
	{
	    switch ( $column )
	    {
	        case 'ipractice-Status' :
	            // Get custom post meta data
	            $ipractice_status= get_post_meta( $post_id, 'ipractice_status', true );
	            if(!empty($ipractice_status))
	               if($ipractice_status=='error') 
	                	echo '<mark class="order-status status-failed tips"><span>'.$ipractice_status.'</span></mark>';
	                else if($ipractice_status=='ok' || $ipractice_status=='OK') 
	                	echo '<mark class="order-status status-processing tips"><span>'.$ipractice_status.'</span></mark>';
	               else 
				echo '<mark class="order-status status-pending tips"><span>'.$ipractice_status.'</span></mark>';
	            // Testing (to be removed) - Empty value case
	            else
	                echo '<mark class="order-status status-pending tips"><span>(<em>no value</em>)</span></mark>';
	
	            break;
	    }
	}
}