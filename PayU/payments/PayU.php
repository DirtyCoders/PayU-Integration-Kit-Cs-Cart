<?php
if ( !defined('AREA') ) { die('Access denied'); }

// Return from payu website
if (defined('PAYMENT_NOTIFICATION')) {
        $txn = array();
        $txn = explode(":",$_REQUEST['txnid']);
        $order = $txn[0];
        
        if ($mode == 'return' && !empty($_REQUEST['order_id'])) {
            
            
		
		if (fn_check_payment_script('PayU.php', $_REQUEST['order_id'], $processor_data)) {

			$pp_response = array();
			
			$order_info = fn_get_order_info($order);
			
			$pp_mc_gross = !empty($_REQUEST['mc_gross']) ? $_REQUEST['mc_gross'] : 0;

                    if (!empty($_POST)) {
                            foreach($_POST as $key => $value) {
                                    $txnRs[$key] = htmlentities($value, ENT_QUOTES);
                                 }
                        if($txnRs['status']=='success'){
                                  $merc_hash_vars_seq = explode('|', "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10");
                                  $merc_hash_vars_seq = array_reverse($merc_hash_vars_seq);
                                  $merc_hash_string = $processor_data['params']['salt'] . '|' . $txnRs['status'];
                                  foreach ($merc_hash_vars_seq as $merc_hash_var) {
                                    $merc_hash_string .= '|';
                                    $merc_hash_string .= isset($txnRs[$merc_hash_var]) ? $txnRs[$merc_hash_var] : '';
                                  }

                                  $merc_hash =strtolower(hash('sha512', $merc_hash_string));
                                  if($merc_hash!=$txnRs['hash'] || $txnRs['amount']<$order_info[''] ) {
                                        $pp_response['order_status'] = 'F';
					$pp_response['reason_text'] = '';
					$pp_response['transaction_id'] = $order;
                                        
                                  }
                                  else {
					$pp_response['order_status'] = 'P';
					$pp_response['reason_text'] = '';
					$pp_response['transaction_id'] = @$order;
                                        
                                  }
			} elseif ($txnRs['status'] =='Pending') {
					$pp_response['order_status'] = 'O';
					$pp_response['reason_text'] = fn_get_lang_var('pending') . (!empty($_REQUEST['field9'])? ": " .  $_REQUEST['field9'] : "");
					$pp_response['transaction_id'] = @order;
	
			} 
                        else {
					$pp_response['order_status'] = 'D';
					$pp_response['reason_text'] = '';
					$pp_response['transaction_id'] = @$order;
			}
                    }
	
			if (!empty($txnRs['email'])) {
				$pp_response['customer_email'] = $txnRs['email'];
			}
			if (!empty($txnRs['bank_ref_no'])) {
				$pp_response['client_id'] = $txnRs['bank_ref_no'];
			}
                        fn_finish_payment($_REQUEST['order_id'], $pp_response);
                        fn_order_placement_routines($_REQUEST['order_id'], false);
			
		}
		exit;

	} elseif ($mode == 'failure') {
		if (fn_check_payment_script('PayU.php', $order)) {
			$pp_response['order_status'] = 'F';
			$pp_response['reason_text'] = '';
			$pp_response['transaction_id'] = $order;
                }
                fn_finish_payment($order, $pp_response);
		fn_order_placement_routines($order, false);

	} elseif ($mode == 'cancel') {
		$order_info = fn_get_order_info($_REQUEST['order_id']);

		$pp_response['order_status'] = 'N';
		$pp_response["reason_text"] = fn_get_lang_var('text_transaction_cancelled');
                if (!empty($_REQUEST['email'])) {
				$pp_response['customer_email'] = $txnRs['email'];
			}
		if (!empty($_REQUEST['bank_ref_no'])) {
				$pp_response['client_id'] = $txnRs['bank_ref_no'];
			}
		
		fn_finish_payment($_REQUEST['order_id'], $pp_response, false);
		fn_order_placement_routines($_REQUEST['order_id']);
	
	}

}
else {

	$payu_account = $processor_data['params']['account'];
        $salt = $processor_data['params']['salt'];
	$current_location = Registry::get('config.current_location');

	if ($processor_data['params']['mode'] == 'test') {
		$payu_url = "https://test.payu.in/_payment.php";
	} else {
		$payu_url = "https://secure.payu.in/_payment.php";
	}

	$payu_currency = $processor_data['params']['currency'];
	$payu_item_name = $processor_data['params']['item_name'];
	//Order Total
	$payu_shipping = fn_order_shipping_cost($order_info);
	$payu_total = fn_format_price($order_info['total'] - $payu_shipping, $payu_currency);
	$payu_shipping = fn_format_price($payu_shipping, $payu_currency);
	$payu_order_id = $processor_data['params']['order_prefix'].(($order_info['repaid']) ? ($order_id .'_'. $order_info['repaid']) : $order_id);
        $payu_order_id .= ":".substr(hash('sha256', mt_rand() . microtime()), 0, 20);
        //echo($oder_info['repaid']);
        //die;
	$_phone = preg_replace('/[^\d]/', '', $order_info['phone']);
	$_ph_a = $_ph_b = $_ph_c = '';
	
	if ($order_info['b_country'] == 'US') {
		$_phone = substr($_phone, -10);
		$_ph_a = substr($_phone, 0, 3);
		$_ph_b = substr($_phone, 3, 3);
		$_ph_c = substr($_phone, 6, 4);
	} elseif ($order_info['b_country'] == 'GB') {
		if ((strlen($_phone) == 11) && in_array(substr($_phone, 0, 2), array('01', '02', '07', '08'))) {
			$_ph_a = '44';
			$_ph_b = substr($_phone, 1);
		} elseif (substr($_phone, 0, 2) == '44') {
			$_ph_a = '44';
			$_ph_b = substr($_phone, 2);
		} else {
			$_ph_a = '44';
			$_ph_b = $_phone;
		}
	} elseif ($order_info['b_country'] == 'AU') {
		if ((strlen($_phone) == 10) && $_phone[0] == '0') {
			$_ph_a = '61';
			$_ph_b = substr($_phone, 1);
		} elseif (substr($_phone, 0, 2) == '61') {
			$_ph_a = '61';
			$_ph_b = substr($_phone, 2);
		} else {
			$_ph_a = '61';
			$_ph_b = $_phone;
		}
	} else {
		$_ph_a = substr($_phone, 0, 3);
		$_ph_b = substr($_phone, 3);
	}
	
	// US states
	if ($order_info['b_country'] == 'US') {
		$_b_state = $order_info['b_state'];
	// all other states
	} else {
		$_b_state = fn_get_state_name($order_info['b_state'], $order_info['b_country']);
	}
        $posted = array();
        $posted['key']=$payu_account;
        $posted['txnid']=$payu_order_id;
        $posted['udf2']=$payu_order_id;
        $posted['amount']=(int)$payu_total;
        $posted['productinfo']="info";
        $posted['firstname']=$order_info['b_firstname'];
        $posted['email']=$order_info['email'];
        $hashSequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";
        $hashVarsSeq = explode('|', $hashSequence);
        $hash_string = '';
        foreach($hashVarsSeq as $hash_var) {
            $hash_string .= isset($posted[$hash_var]) ? $posted[$hash_var] : '';
            $hash_string .= '|';
        }
        $hash_string .= $salt;
	$hash = strtolower(hash('sha512', $hash_string));
	$posted['hash']=$hash;
        
	$msg = fn_get_lang_var('text_cc_processor_connection');
	$msg = str_replace('[processor]', 'PayU', $msg);
	echo <<<EOT
	<html>
	<body onLoad="document.payu_form.submit();">
	<form action="{$payu_url}" method="post" name="payu_form">
	
	<input type=hidden name="txnid" value="{$posted['txnid']}">
	<input type=hidden name="udf2" value="{$posted['udf2']}">
	
	<input type=hidden name="hash" value="{$posted['hash']}">
	<input type=hidden name="email" value="{$posted['email']}">
	<input type=hidden name="firstname" value="{$posted['firstname']}">
	<input type=hidden name="lastname" value="{$order_info['b_lastname']}">
	<input type=hidden name="address1" value="{$order_info['b_address']}">
	<input type=hidden name="address2" value="{$order_info['b_address_2']}">
	<input type=hidden name="country" value="{$order_info['b_country']}">
	<input type=hidden name="city" value="{$order_info['b_city']}">
	<input type=hidden name="state" value="{$_b_state}">
	<input type=hidden name="zipcode" value="{$order_info['b_zipcode']}">
	<input type=hidden name="phone" value="{$_ph_a}">
	<input type=hidden name="service_provider" value="payu_paisa">
	
	<input type=hidden name="key" value="{$posted['key']}">
	<input type=hidden name="productinfo" value="{$posted['productinfo']}">
	<input type=hidden name="amount" value="{$posted['amount']}">
	
	<input type=hidden name="surl" value="$current_location/$index_script?dispatch=payment_notification.return&payment=payu&order_id=$order_id">
	<input type=hidden name="curl" value="$current_location/$index_script?dispatch=payment_notification.cancel&payment=payu&order_id=$order_id" />
	<input type=hidden name="furl" value="$current_location/$index_script?dispatch=payment_notification.failure&payment=payu&order_id=$order_id">
	
EOT;

$i = 1;
// Products
if (empty($order_info['use_gift_certificates']) && !floatval($order_info['subtotal_discount']) && empty($order_info['points_info']['in_use'])) {
	if (!empty($order_info['items'])) {
		foreach ($order_info['items'] as $k => $v) {
			$suffix = '_'.($i++);
			$v['product'] = htmlspecialchars(strip_tags($v['product']));
			$v['price'] = fn_format_price(($v['subtotal'] - fn_external_discounts($v)) / $v['amount'], $payu_currency);
			echo <<<EOT
			<input type="hidden" name="item_name{$suffix}" value="{$v['product']}" />
			<input type="hidden" name="amount{$suffix}" value="{$v['price']}" />
			<input type="hidden" name="quantity{$suffix}" value="{$v['amount']}" />
EOT;
			if (!empty($v['product_options'])) {
				foreach ($v['product_options'] as $_k => $_v) {
					$_v['option_name'] = htmlspecialchars(strip_tags($_v['option_name']));
					$_v['variant_name'] = htmlspecialchars(strip_tags($_v['variant_name']));
					echo <<<EOT
						<input type="hidden" name="on{$_k}{$suffix}" value="{$_v['option_name']}" />
						<input type="hidden" name="os{$_k}{$suffix}" value="{$_v['variant_name']}" />
EOT;
				}
			}
		}
	}
        if (!empty($order_info['taxes']) && Registry::get('settings.General.tax_calculation') == 'subtotal') {
		foreach ($order_info['taxes'] as $tax_id => $tax) {
			if ($tax['price_includes_tax'] == 'Y') {
				continue;
			}
			$suffix = '_' . ($i++);
			$item_name = htmlspecialchars(strip_tags($tax['description']));
			$item_price = fn_format_price($tax['tax_subtotal'], $payu_currency);
			echo <<<EOT
			<input type="hidden" name="item_name{$suffix}" value="{$item_name}" />
			<input type="hidden" name="amount{$suffix}" value="{$item_price}" />
			<input type="hidden" name="quantity{$suffix}" value="1" />
EOT;
		}
	}

	// Gift Certificates
	if (!empty($order_info['gift_certificates'])) {
		foreach ($order_info['gift_certificates'] as $k => $v) {
			$suffix = '_'.($i++);
			$v['gift_cert_code'] = htmlspecialchars($v['gift_cert_code']);
			$v['amount'] = (!empty($v['extra']['exclude_from_calculate'])) ? 0 : fn_format_price($v['amount'], $payu_currency);
			echo <<<EOT
			<input type="hidden" name="item_name{$suffix}" value="{$v['gift_cert_code']}" />
			<input type="hidden" name="amount{$suffix}" value="{$v['amount']}" />
			<input type="hidden" name="quantity{$suffix}" value="1" />
EOT;
		}
	}

	// Payment surcharge
	if (floatval($order_info['payment_surcharge'])) {
		$suffix = '_' . ($i++);
		$name = fn_get_lang_var('surcharge');
		$payment_surcharge_amount = fn_format_price($order_info['payment_surcharge'], $payu_currency);
		echo <<<EOT
		<input type="hidden" name="item_name{$suffix}" value="{$name}" />
		<input type="hidden" name="amount{$suffix}" value="{$payment_surcharge_amount}" />
		<input type="hidden" name="quantity{$suffix}" value="1" />
EOT;
	}
} else {
	$total_description = fn_get_lang_var('total_product_cost');
	echo <<<EOT
	<input type="hidden" name="item_name_1" value="{$total_description}" />
	<input type="hidden" name="amount_1" value="{$payu_total}" />
	<input type="hidden" name="quantity_1" value="1" />
EOT;
}


	echo <<<EOT
	</form>
	<div align=center>{$msg}</div>
	</body>
	</html>
EOT;

	fn_flush();
}
exit;
?>