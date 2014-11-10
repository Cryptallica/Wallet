<?php

if(!isset($_POST["json_data"])) {
	echo "No Data Set";
	return false;	
}

$json_data = json_decode($_POST["json_data"]);

if(!isset($json_data->coin, $json_data->username, $json_data->password)) {
	echo "Username coin or password not set";
	return false;
}


require_once("../../core/cryptallica.php");
$crypt =  new Cryptallica();

if(!$crypt->current_coin) {return false;}


$crypt->usecoin($json_data->coin, $json_data->username, $json_data->password);
if($crypt->isLoggedIn($json_data->coin)) {
	echo "%%%%TRUE%%%%";
	return true;
} else {
	echo "Login Failed. Check username and password";
	return false;
}
?>