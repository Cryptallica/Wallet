<?php require_once("../../core/cryptallica.php");

foreach(["toaccount", "label", "amount", "message", "type"] as $field) {
	if(!isset($_POST[$field])) {
		//Check type if not set
		if($field === "type") {$_POST[$field] = "qr";} 
		else {$_POST[$field] = "";	}	
	}
}

$crypt = new Cryptallica();


if($_POST["type"] === "qr") {
	$img_url = $crypt->createQR(
		$_POST["toaccount"],
		$_POST["amount"],
		$_POST["label"],
		$_POST["message"]
	);
	$filename = "QR.png";
} else {
	$img_url = $crypt->createRequestCheck(
		$_POST["toaccount"],
		$_POST["amount"],
		$_POST["label"],
		$_POST["message"]
	);
	$filename = "QR-Check.png";
}

$crypt->download($img_url, $filename);
