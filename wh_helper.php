<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error.log'); 
date_default_timezone_set("Asia/Kolkata");

include 'config/db.php';
function getAllUsers(){
	$sql = "SELECT * FROM user_info";
	try {
		$db = getDB();
		$stmt = $db->prepare($sql); 
		$stmt->execute();
		$users = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo '{"users": ' . json_encode($users) . '}';
	} catch(PDOException $e) {
	    //error_log($e->getMessage(), 3, '/var/tmp/php.log');
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function addUser($fb_id){
	$sql = "INSERT IGNORE user_info (fb_id, created_at) VALUES (:fb_id, :timestamp);";
	try {
		$db = getDB();
		$stmt = $db->prepare($sql); 
		$stmt->bindParam("fb_id", $fb_id);
		$time=date('Y-m-d H:i:s');
		$stmt->bindParam("timestamp", $time);
		$stmt->execute();
		$db = null;
		$sql = "SELECT * FROM user_info WHERE fb_id=:fb_id;";
		try {
			$db = getDB();
			$stmt = $db->prepare($sql); 
			$stmt->bindParam("fb_id", $fb_id);
			$stmt->execute();
			$users = $stmt->fetch(PDO::FETCH_OBJ);
			$db = null;
			return json_encode($users);
		} catch(PDOException $e) {
		    //error_log($e->getMessage(), 3, '/var/tmp/php.log');
			return '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
	} catch(PDOException $e) {
	    //error_log($e->getMessage(), 3, '/var/tmp/php.log');
		return '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function checkUser($fb_id){
	$sql = "SELECT * FROM user_info WHERE fb_id=:fb_id;";
	try {
		$db = getDB();
		$stmt = $db->prepare($sql); 
		$stmt->bindParam("fb_id", $fb_id);
		$stmt->execute();
		$row = $stmt->rowCount();
		$result['exists'] = $row > 0 ? true : false;
		$result['user'] = $stmt->fetch(PDO::FETCH_OBJ);
		$db = null;
		return json_encode($result);
	} catch(PDOException $e) {
	    //error_log($e->getMessage(), 3, '/var/tmp/php.log');
		return '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function checkConv($user_id){
	$sql = "SELECT * FROM conv_info WHERE user_id=:user_id AND created_at >= DATE_SUB(NOW(),INTERVAL 15 MINUTE) ORDER BY created_at DESC LIMIT 1;";
	try {
		$db = getDB();
		$stmt = $db->prepare($sql); 
		$stmt->bindParam("user_id", $user_id);
		$stmt->execute();
		$row = $stmt->rowCount();
		$result['exists'] = $row > 0 ? true : false;
		$result['conv'] = $stmt->fetch(PDO::FETCH_OBJ);
		$db = null;
		return json_encode($result);
		// return $row > 0;
	} catch(PDOException $e) {
	    //error_log($e->getMessage(), 3, '/var/tmp/php.log');
		return '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function getConv($conv_id){
	$sql = "SELECT * FROM conv_info WHERE id=:conv_id;";
	try {
		$db = getDB();
		$stmt = $db->prepare($sql); 
		$stmt->bindParam("conv_id", $conv_id);
		$stmt->execute();
		$row = $stmt->rowCount();
		$result = $stmt->fetch(PDO::FETCH_OBJ);
		$db = null;
		return json_encode($result);
		// return $row > 0;
	} catch(PDOException $e) {
	    //error_log($e->getMessage(), 3, '/var/tmp/php.log');
		return '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function getAllConv(){
	$sql = "SELECT * FROM conv_info ORDER BY created_at desc LIMIT 30;";
	try {
		$db = getDB();
		$stmt = $db->prepare($sql); 
		$stmt->execute();
		$row = $stmt->rowCount();
		$result = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		return json_encode($result);
		// return $row > 0;
	} catch(PDOException $e) {
	    //error_log($e->getMessage(), 3, '/var/tmp/php.log');
		return '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function newConv($user_id){
	$sql = "INSERT into conv_info (user_id, created_at) VALUES (:user_id, :timestamp);";
	try {
		$db = getDB();
		$stmt = $db->prepare($sql); 
		$stmt->bindParam("user_id", $user_id);
		$time=date('Y-m-d H:i:s');
		$stmt->bindParam("timestamp", $time);
		$stmt->execute();
		$conv_id = $db->lastInsertId();
		$db = null;
		$sql = "SELECT * FROM conv_info WHERE id=:conv_id;";
		try {
			$db = getDB();
			$stmt = $db->prepare($sql); 
			$stmt->bindParam("conv_id", $conv_id);
			$stmt->execute();
			$conv = $stmt->fetch(PDO::FETCH_OBJ);
			$db = null;
			return json_encode($conv);
		} catch(PDOException $e) {
		    //error_log($e->getMessage(), 3, '/var/tmp/php.log');
			return '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
	} catch(PDOException $e) {
	    //error_log($e->getMessage(), 3, '/var/tmp/php.log');
		return '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function updateConvEMType($conv_id, $em_type){
	$sql = "UPDATE conv_info SET em_type=:em_type WHERE id=:conv_id;";
	try {
		$db = getDB();
		$stmt = $db->prepare($sql); 
		$stmt->bindParam("conv_id", $conv_id);
		$stmt->bindParam("em_type", $em_type);
		$stmt->execute();
		$row = $stmt->rowCount();
		$db = null;
		return $row > 0;
	} catch(PDOException $e) {
	    //error_log($e->getMessage(), 3, '/var/tmp/php.log');
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function updateConvLocation($conv_id, $lat, $long){
	$sql = "UPDATE conv_info SET latitude=:lat, longitude=:long WHERE id=:conv_id;";
	try {
		$db = getDB();
		$stmt = $db->prepare($sql); 
		$stmt->bindParam("conv_id", $conv_id);
		$stmt->bindParam("lat", $lat);
		$stmt->bindParam("long", $long);
		$stmt->execute();
		$row = $stmt->rowCount();
		$db = null;
		return $row > 0;
	} catch(PDOException $e) {
	    //error_log($e->getMessage(), 3, '/var/tmp/php.log');
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function updateConvMobile($conv_id, $mobile){
	$sql = "UPDATE conv_info SET mob_num=:mobile WHERE id=:conv_id;";
	try {
		$db = getDB();
		$stmt = $db->prepare($sql); 
		$stmt->bindParam("conv_id", $conv_id);
		$stmt->bindParam("mobile", $mobile);
		$stmt->execute();
		$row = $stmt->rowCount();
		$db = null;
		return $row > 0;
	} catch(PDOException $e) {
	    //error_log($e->getMessage(), 3, '/var/tmp/php.log');
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}