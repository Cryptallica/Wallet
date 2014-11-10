<?php 

require_once('../../core/cryptallica.php');
$crypt = new Cryptallica();
$id = isset($_GET["id"])? $_GET["id"]: false;
$crypt->logout($id);
header("Location: ../");