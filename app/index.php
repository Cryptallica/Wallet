<?php require_once('../core/cryptallica.php');
$crypt = new Cryptallica();
?>

<!DOCTYPE html>
<html>
<head>
<title>Cryptallica: Heavy Metal Cryptocurrency</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width">

<script src="js/core.js"></script>
<link rel="stylesheet" href="assets/cryptallica.css">

</head>

<body>

<!--Cryptallica app-->
<div id="cryptWrap">

<!--Home login apps and data-->
<div data="home" id="cryptHome">
	<div class="cryptallicaKeys" id="cryptMainKeys">
		<a data="home">Home</a>
		<a data="coin" style="display:none;"></a>
		<a href="javascript:cryptallicaQR.setwebcam();" data="qrreader">QR</a>
		<img src="assets/icons/logo.png">
	</div>

	<span>
	<h2>Login</h2>
	
	<div id="cryptCoinLogins">
	<?php foreach($crypt->coinnames as $coin) {?>
	<span data="<?php echo $coin;?>">
		<img src="assets/icons/<?php echo $coin;?>.png">
		<a href="javascript:;"><?php echo ucfirst($coin);?></a>
	</span>
	<?php }?>
	</div>
	
	<a href="controllers/logout.php" id="cryptLogoutAll">Logout of all</a>

	<h2>Prices</h2>
	
	<div id="cryptCoinPrices">
		<div class="loading-container">
			<div class="loader">
		    <div class="loading-bars">
		      <div class="bar"></div>
		      <div class="bar"></div>
		      <div class="bar"></div>
		      <div class="bar"></div>
		      <div class="bar"></div>
		    </div>
		    </div>
		</div>
	</div>
	</span>
</div>


<!--Brought by loggin in and grabbing-->
<div id="cryptMain" data="coin"></div>


<!--Qr Read-->
<div id="cryptQrReader" data="qrreader">
	<?php include_once("layouts/qrreader.php");?>
</div>

</div>

<!--javascript for all main functions-->
<script src="js/cryptallica.js"></script>
<script src="js/qrcode.js"></script>
<script src="js/webqr.js"></script>
</body>
</html>