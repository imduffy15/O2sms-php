#!/usr/bin/php
<?php
require_once('o2.class.php');
//print_r($argv);
$username = $argv[1]; //number
$password = $argv[2]; //password
$sendsms = new o2();
$login = $sendsms->login($username,$password);
if($login == 1) {
	echo "Login Successful\n";
	$sms = $sendsms->send_message($argv[3],$argv[4]);
	if($sms == 1) {
		echo "Message sent successfully\n";
	} else if($sms == -1) {
		echo "Connection Issue\n";
	} else if($sms == 0) {
		echo "Message failed to send\n";
	} else if($sms == -2) {
		echo "No Balance\n";
	}
} else if($login == -1) {
	echo "Connection Issue\n";
} else if($login == 0) {
	echo "Invalid Username/Password\n";
}
