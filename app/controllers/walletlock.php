<?php require_once("../../core/cryptallica.php");

$crypt =  new Cryptallica();
if($crypt->walletLock()) {
	echo "Wallet Locked";
} else {
	echo " Wallet failed to lock";
}