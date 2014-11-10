<?php require_once("../../core/cryptallica.php");

$crypt =  new Cryptallica();
$json_data = $crypt->json_get(["password", "newpassword", "newpasswordcheck"]);

if($json_data->newpassword !== $json_data->newpasswordcheck) {echo "New password did not match up";}

if($crypt->walletPassChange($json_data->password, $json_data->newpassword)) {
	echo "Wallet Password Changed Succesfully";
} else {
	echo " Wallet password failed to change.";
}