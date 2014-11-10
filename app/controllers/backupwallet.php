<?php
require_once("../../core/cryptallica.php");

$crypt = new Cryptallica();
if(!isset($_POST["password"])) {return false;}

//Check wallet password
if($crypt->walletDownload($_POST["password"])) {
	echo "Wallet Backed up";
} else {
	echo "Wallet Failed to Download";
}