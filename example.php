#!/usr/bin/php
<?php
require_once('O2SMS.php');
//print_r($argv);
$username = $argv[1]; //number
$password = $argv[2]; //password
$sendsms = new O2SMS($username,$password);
$sendsms->send($argv[3],$argv[4]);
