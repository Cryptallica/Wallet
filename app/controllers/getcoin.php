<?php require_once("../../core/cryptallica.php");

if(!isset($_GET["id"])) {return false;}
$coinname = strval($_GET["id"]);

$crypt = new Cryptallica();

//Check if valid coin
if(!$crypt->isCoin($coinname)) {
	echo "Coin ".htmlentities($_GET["id"])." not found"; 
	exit;
}

if(!$crypt->isLoggedIn($coinname)) {
	$crypt->setCoin($coinname);
	//Initialize login
	include(CRYPT_LAYOUT."login.php");
	exit;
} else {
	$crypt->setCoin($coinname);
	include(CRYPT_LAYOUT."coin.php");
	exit;
}