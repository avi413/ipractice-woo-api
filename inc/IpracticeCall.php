<?php
/**
 * @package IpracticeApi
 * */



class IpracticeCall
{
    public static function woo_call_ipractice_api($order_id) {          
	
		$order = new WC_Order( $order_id );
		
		$currentStatus = $order->status;
		
		update_post_meta( $order_id, 'ipractice_currentStatus', $currentStatus );
		
		if($currentStatus == 'completed' && get_post_meta( $order_id, 'ipractice_status', true ) != "OK")
		{
			global $wpdb;
			$tablename = $wpdb->prefix."ipracticekeys";


			// Iterate Through Items
			$items = $order->get_items(); 
			
			$username = explode("@",$order->billing_email);
			foreach ( $items as $item ) {	
				//$product_id 	= $item['product_id'];
			    $product 		= new WC_Product($item['product_id']);
				$username		= $username[0];
				$email			= $order->billing_email; 
				$tz				= $order->billing_tz; 
				$projectsku     = $product->get_sku();


				if ( empty(get_post_meta( $order_id, 'ipractice_key_sku_'.$projectsku, true )) ) {

					$cntSQL = "SELECT count(*) as count FROM {$tablename} where order_id='".$order_id."' and  sku ='".$projectsku."'";
					$record = $wpdb->get_results($cntSQL, OBJECT);

					if($record[0]->count==0){
						$cntSQL = "SELECT *  FROM {$tablename} where order_id ='0'";	
					}
					else {
						$cntSQL = "SELECT *  FROM {$tablename} where order_id='".$order_id."' and  sku ='".$projectsku."'";

					}				
					$curKey = $wpdb->get_row($cntSQL, ARRAY_A);
					$ipKey = $curKey["ip_key"];
				}
				else {
					
					$ipKey = get_post_meta($order_id, 'ipractice_key_sku_'.$projectsku, true );
				}
				
		        // API Callout to URL
				$url = esc_attr( get_option('ipractice_ep'));
				update_post_meta( $order_id, 'ipractice_ep', $url );
				$body = array(
					"tz"			=> $tz,
					"name" 			=> $username,
					"email"			=> $email,
					"password"		=>  "0",
					"unique_key"	=> $ipKey,
					"package"  		=> $projectsku
					
				);
				$sqlUpdate = "UPDATE $tablename SET order_id='$order_id', sku='$projectsku' WHERE ip_key='$ipKey'";

				$updateRes = $wpdb->query($wpdb->prepare($sqlUpdate));

	
				// Data should be passed as json format
				$data_json = json_encode($body);

				update_post_meta( $order_id, 'ipractice_data_sku_'.$projectsku, $data_json );
				update_post_meta( $order_id, 'ipractice_key_sku_'.$projectsku, $ipKey  );
				
				// curl initiate
				$ch = curl_init();
		
				curl_setopt($ch, CURLOPT_URL, $url);
		
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		
				// SET Method as a POST
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
				curl_setopt($ch, CURLOPT_TIMEOUT, 400); //timeout in seconds
				// Pass user data in POST command
				curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);
		
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

				// Execute curl and assign returned data
				$response  = curl_exec($ch);
				update_post_meta( $order_id, 'ipractice_curl_exec', 'after'  );
				// Close curl
				curl_close($ch);
				$iprers = array();
				$iprers = json_decode($response);
				update_post_meta( $order_id, 'ipractice_curl_exec1', $iprers  );
				

				//get response 
				foreach ($iprers as $key => $val) {
					update_post_meta( $order_id, 'ipractice_'.$key, $val );
				}

		    }
	    }
	}
}
