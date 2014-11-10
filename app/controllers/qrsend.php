<?php require_once("../../core/cryptallica.php");

$crypt = new Cryptallica();
$json_data = $crypt->json_get(["fromaccount", "amount"]);
$result = $crypt->sendQRCreate($json_data->password, $json_data->fromaccount, $json_data->amount, $json_data);

if($result) {
	echo " Dump complete.";
} else {
	echo " Failed to dump";
}