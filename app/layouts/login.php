<?php 
	
$crypt = new Cryptallica();	
if(!$crypt->current_coin) {return false;}
?>


<div id="cryptLogin">

	<a href="javascript:cryptallica.toggler.setPage('home');">&lt; &lt;</a>

	<div id="cryptLoginCurrency">
	<img src="assets/icons/<?php echo $crypt->current_coin;?>.png">
	</div>

	<span>
	<form action="controllers/rpclogin.php" method="post" id="cryptLoginForm">
		
		<input type="hidden" name="coin" value="<?php echo $crypt->current_coin;?>">
		
		<h2><?php echo ucfirst($crypt->current_coin);?> Login</h2>
		
		<span id="cryptLoginError"></span>
	
		<div>
			<h3>Username</h3>
			<input type="text" name="username" placeholder="Username">
		</div>
	
		<div>
			<h3>Password</h3>
			<input type="password" name="password" placeholder="Password">
		</div>
		
		<button type="submit" onclick="cryptallica.loginAni.show();">Login</button>
		
	</form>
	</span>
	
	<div id="cryptLoginLoading">
		<h2>Attempting login</h2>
		<p id="cryptLoadiingText">Might take a while</p>
	
		<div class='loading-container'>
			<div class='loader'>
		    <div class='loading-bars'>
		      <div class='bar'></div>
		      <div class='bar'></div>
		      <div class='bar'></div>
		      <div class='bar'></div>
		      <div class='bar'></div>
		    </div>
		    </div>
		</div>
	</div>
</div>

<script>
ajaxForm("cryptLoginForm", function(responseText) {
	if(trim(responseText) === "%%%%TRUE%%%%") {
		this.cryptHome.innerHTML = responseText;		
		setTimeout(function(){coinJS();}, 25);
	} else {
		alert(responseText);
	}
});
</script>