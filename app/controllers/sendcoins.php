<?php
require_once("../../core/cryptallica.php");

$json_data = json_decode($_POST["json_data"]);

$crypt = new Cryptallica();

if(!$crypt->isLoggedIn()) {die("Not logged in");}

//print_r($json_data);
$moves = [];
$transfers = [];
$cost = (float) $crypt->coin("info")["paytxfee"];

//Check from address
if(!$crypt->isAccount($json_data->fromAccount)) {
	echo "From Account not valid ".$json_data->fromAccount;
	exit;
}

//Check data
foreach($json_data->sends as $sendArr) {
	
	$cost = $cost + floatval($sendArr->sendAmount);
	
	if($crypt->isAccount($sendArr->sendAddress)) {
		array_push($moves, $sendArr);
	} else {	
		array_push($transfers, [$sendArr->sendAddress => $sendArr->sendAmount]);
	}
}

//Check sufficent funds
if(!$crypt->hasAmount($json_data->fromAccount, $cost)) {
	echo "Account has insufficient funds for total of {$cost}";
	exit;
}

//Do moves from account
foreach($moves as $move) {
	$crypt->accountMove($json_data->fromAccount, $move->sendAddress, $move->sendAmount);
	echo "Account move sent<br/>";
}

//do Transactions
if(!empty($transfers)) {
	if($crypt->transfer($json_data->fromAccount, $transfers)) {
		echo "Transfers sent<br/>";	
	} else {
		echo "Transfers Failed";
	}
	
}