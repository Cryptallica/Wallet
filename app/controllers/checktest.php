<?php require_once("../../core/cryptallica.php");

$crypt = new Cryptallica();

$img_url = $crypt->createRequestCheck(
	"DSgZFrknhLrQhzYPDPzmQ5i6GsRtZ4bShU",
	50,
	"Test",
	"Test"
);
//$filename = "QR-Check.png";

echo $img_url;

//$crypt->download($img_url, $filename);

?>