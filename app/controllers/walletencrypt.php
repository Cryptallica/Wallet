<?php require_once("../../core/cryptallica.php");

$crypt =  new Cryptallica();
$json_data = $crypt->json_get(["newpassword", "newpasswordcheck"]);

if(strlen($json_data->newpassword) < 6) {echo "Password length less than 6";exit;}
if($json_data->newpassword !== $json_data->newpasswordcheck) {echo "Passwords don't match.";exit;}

//Try Encryptor
if($crypt->walletEncrypt($json_data->newpassword)) {
	echo "Wallet encrypted successfully";
} else {
	echo " Wallet failed to encrypt";
}