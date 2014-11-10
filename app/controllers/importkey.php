<?php require_once("../../core/cryptallica.php");

$crypt =  new Cryptallica();
$json_data = $crypt->json_get(["privatekey"]);

if(!isset($json_data->label)) {$json_data->label = '';}

if($crypt->importPrivateKey($json_data->privatekey, $json_data->label)) {
	echo "Wallet imported key. Commence reload to see changes.";
} else {
	echo " Wallet failed to import key";
}