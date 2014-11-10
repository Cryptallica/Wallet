<?php require_once("../../core/cryptallica.php");

//Unlock password based on POST->json_data->password
$crypt = new Cryptallica();
$json_data = $crypt->json_get(["password"]);
if($crypt->walletUnlock($json_data->password)) {
	echo "Wallet Unlocked";
} else {
	echo " Failed to unlock wallet.";
}