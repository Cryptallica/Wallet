<?php
require_once("../../core/cryptallica.php");
$json_data = json_decode($_POST["json_data"]);

$bitcoin = new Cryptallica();
$bitcoin->createaddress($json_data->accountName);