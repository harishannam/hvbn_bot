<?php
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'error.log'); 
	include 'wh_helper.php';

	$MODE="PROD";

	if($MODE=="PROD"){
		$challenge = $_REQUEST['hub_challenge'];
		$verify_token = $_REQUEST['hub_verify_token'];

		if ($verify_token === 'hvbn_dev_token') {
		  echo $challenge;
		}
		$file_input = file_get_contents('php://input');
		$input = json_decode(file_get_contents('php://input'), true);

		file_put_contents("test.txt",$file_input);
	} else { 
		// MOCK DATA for testing

		// $input = json_decode('{"object":"page","entry":[{"id":"1098998120188141","time":1470746469198,"messaging":[{"sender":{"id":"1156128887791043"},"recipient":{"id":"1098998120188141"},"timestamp":1470746469148,"message":{"mid":"mid.1470746469141:0f5d83572f99a53221","seq":99,"text":"my mobile number is 9884828882"}}]}]}', true); // Generic

		// $input = json_decode('{"object":"page","entry":[{"id":"1098998120188141","time":1470747406780,"messaging":[{"sender":{"id":"1156128887791035"},"recipient":{"id":"1098998120188141"},"timestamp":1470747406720,"message":{"quick_reply":{"payload":"ALERT_POLICE"},"mid":"mid.1470747406711:20da9adcfac50dbf30","seq":103,"text":"Police"}}]}]}', true); // quick reply

		// $input = json_decode('{"object":"page","entry":[{"id":"1098998120188141","time":1470748965556,"messaging":[{"sender":{"id":"1156128887791035"},"recipient":{"id":"1098998120188141"},"timestamp":1470748965515,"message":{"mid":"mid.1470748965336:dad2e8c128e5c69592","seq":115,"attachments":[{"title":"Harishs Location","url":"https:\/\/www.facebook.com\/l.php?u=https\u00253A\u00252F\u00252Fwww.bing.com\u00252Fmaps\u00252Fdefault.aspx\u00253Fv\u00253D2\u002526pc\u00253DFACEBK\u002526mid\u00253D8100\u002526where1\u00253D12.90935\u0025252C\u00252B80.2273\u002526FORM\u00253DFBKPL1\u002526mkt\u00253Den-US&h=4AQGvdfSv&s=1&enc=AZNObIE6ZNQ6VIXGOzQP_i0N7I8QywjwFAXSiXWXopcOiO_6M5lkQtuFXv4NSkNkH6LTg4lW_kf6Y_e3eDWujYBt-T-VpY5DEtkugbzxSrAdCg","type":"location","payload":{"coordinates":{"lat":12.90935,"long":80.2273}}}]}}]}]}', true); // Location message
	}

	$sender = $input['entry'][0]['messaging'][0]['sender']['id'];
	$message = $input['entry'][0]['messaging'][0]['message'];

	isset($message) && isset($message['quick_reply']) ? $msg_type="QUICK_REPLY" : (array_key_exists('attachments', $message) && array_key_exists('type', $message['attachments'][0]) ? (($message['attachments'][0]['type'] === 'location') ? $msg_type="LOCATION_REPLY" : $msg_type="UNKNOWN_REPLY") : $msg_type="GENERIC_REPLY");

	switch ($msg_type) {
		case 'QUICK_REPLY':
			handle_quick_reply($sender, $message, $msg_type);
			break;
		case 'GENERIC_REPLY':
			handle_generic_reply($sender, $message, $msg_type);
			break;
		case 'LOCATION_REPLY':
			handle_location_reply($sender, $message, $msg_type);
			break;
		default:
			print_r("UNKNOWN MESSAGE TYPE");
			break;
	}


	function handle_generic_reply($sender, $message, $msg_type){
		$pattern = "/(?:(?:\+|0{0,2})91(\s*[\ -]\s*)?|[0]?)?[789]\d{9}|(\d[ -]?){10}\d/";
		if (strtoupper($message['text']) == "HELP"){
			$response = resolve_user($sender, $message, $msg_type);
			$data['recipient']['id'] = $sender;
			$data['message'] = json_decode($response, true);
			$payload = json_encode($data);
			send_response($payload);
		} else if(preg_match_all($pattern, $message['text'], $matches)) {
			$mobile = $matches[0][0];
			$msg_type = "MOBILE_UPDATE";
			$response = resolve_user($sender, $mobile, $msg_type);
			$data['recipient']['id'] = $sender;
			$data['message'] = json_decode($response, true);
			$payload = json_encode($data);
			send_response($payload);
		} else {
			$data['recipient']['id'] = $sender;
			$data['message'] = json_decode('{"text":"Message format is incorrect. For emergency help, please send \"HELP\""}', true);
			$payload = json_encode($data);
			send_response($payload);
		}
	}

	function handle_quick_reply($sender, $message, $msg_type){
		$response = resolve_user($sender, $message, $msg_type);
		$qr_type = $message['quick_reply']['payload'];
		$qr_text = $message['text'];
		switch ($qr_type) {
			case 'ALERT_POLICE':
			case 'ALERT_FIRE':
			case 'ALERT_AMBULANCE':
				$data['recipient']['id'] = $sender;
				$data['message'] = json_decode($response, true);
				$payload = json_encode($data);
				send_response($payload);
				break;
			default:
				print_r("INVALID QUICK_REPLY REQUEST");
				break;
		}
	}

	function handle_location_reply($sender, $message, $msg_type){
		$response = resolve_user($sender, $message, $msg_type);
		$data['recipient']['id'] = $sender;
		$data['message'] = json_decode($response, true);
		$payload = json_encode($data);
		send_response($payload);
	}

	function send_response ($payload){
		$url = 'https://graph.facebook.com/v2.6/me/messages?access_token=EAAR0Lzf1x0MBAC6V1VaJZCKlp5eko1OpZASKLzRTX5tdwfgPWG2clUgZAsoxeaLKNj72UWtZA96BdifyLkENj1SPFLTZC7RVqvsrrnAGN48vwxYGEQKNZAxZBO8eL8wcIo6ncBdIpPkV000E5W1xHZAHyFhZB9wRkPNdZC3Ph99hUpz7JkA9ZBKR8H7';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		$result = curl_exec($ch);
	}

	// internal resolvers
	function resolve_user($sender, $message, $msg_type){
		$check_user = json_decode(checkUser($sender), true);
		$check_user['exists'] ? $user_info=$check_user['user'] : $user_info = json_decode(addUser($sender), true);
		$user_id = $user_info['id'];
		if($check_user['exists'] && $msg_type !="GENERIC_REPLY"){
			$checkConv=json_decode(checkConv($user_id), true);
			if($checkConv['exists']){
				$conv_info = $checkConv['conv'];
			} else {
				$conv_info = json_decode(newConv($user_id), true);
			}
			$updated_conv = json_decode(resolve_conv($conv_info, $message, $msg_type), true);

			switch ($updated_conv['em_type']) {
				case 'ALERT_POLICE':
					$qr_text='Police department';
					break;
				case 'ALERT_FIRE':
					$qr_text='Fire service';
					break;
				case 'ALERT_AMBULANCE':
					$qr_text='Ambulance service';
					break;
				default:
					$qr_text='Emergency service';
					break;
			}
			$need_em_type = '{"text":"Select an option below menu. Do you want to contact","quick_replies": [{"content_type": "text","title": "Police","payload": "ALERT_POLICE"}, {"content_type": "text","title": "Fire Service","payload": "ALERT_FIRE"}, {"content_type": "text","title": "Ambulance","payload": "ALERT_AMBULANCE"}]}';
			$need_location = '{"text":"To alert the '. $qr_text .' please share your GPS location using Messenger app"}';
			$need_mobile = '{"text":"We have alerted the '. $qr_text .'. Meanwhile please provide your mobile number."}';
			$need_none = '{"text":"Thanks for all the information. We have passed your details to the '. $qr_text .'. They will reach out to you shortly."}';
			
			if(empty($updated_conv['em_type']) || is_null($updated_conv['em_type']))
				$response = $need_em_type;
			else if(empty($updated_conv['latitude']) || is_null($updated_conv['latitude']))
				$response = $need_location;
			else if(empty($updated_conv['mob_num']) || is_null($updated_conv['mob_num']))
				$response = $need_mobile;
			else
				$response = $need_none;

			return $response;
		} else {
			$conv_info = json_decode(newConv($user_id), true);
			if($msg_type=='MOBILE_UPDATE')
				updateConvMobile($conv_info['id'], $message); // message will only have the user's mobile number
			$response = '{"text":"Select an option below menu. Do you want to contact","quick_replies": [{"content_type": "text","title": "Police","payload": "ALERT_POLICE"}, {"content_type": "text","title": "Fire Service","payload": "ALERT_FIRE"}, {"content_type": "text","title": "Ambulance","payload": "ALERT_AMBULANCE"}]}';
			return $response;
		}
	}

	function resolve_conv($conv_info, $message, $msg_type) {
		switch ($msg_type) {		
			case 'QUICK_REPLY':
				$em_type = $message['quick_reply']['payload'];
				updateConvEMType($conv_info['id'], $em_type);
				break;	
			case 'MOBILE_UPDATE':
				$mobile = $message; // message will only have the user's mobile number
				updateConvMobile($conv_info['id'], $mobile);
				break;
			case 'LOCATION_REPLY':
				$payload = $message['attachments'][0]['payload'];
				$lat = $payload['coordinates']['lat'];
				$long = $payload['coordinates']['long'];
				updateConvLocation($conv_info['id'], $lat, $long);
				break;
			default:
				break;
		}
		return getConv($conv_info['id']);
	}

?>