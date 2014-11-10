<?php
$crypt = new Cryptallica();
if(!$crypt->isLoggedIn()) {exit;}
?>

<!------------------Page Navigation------------------>
<span id="cryptallicaLinks">
	<div id="cryptallicaKeys" class="cryptallicaKeys">
		<a href="javascript:;" data="accounts"><?php echo ucfirst($crypt->current_coin);?></a>
		<a href="javascript:;" data="send">Send</a>
		<a href="javascript:;" data="history">History</a>
		<a href="javascript:;" data="export">I/O</a>
		<a href="javascript:;" data="home" style="display:none"></a>
	</div>

	<div id="cryptallicaCurrencies">
		<img src="assets/icons/<?php echo $crypt->current_coin;?>.png">
		<?php if($crypt->wallet_unlocked === false) {
			echo "<img src='assets/use/walletlock.png' id='cryptWalletSymbol'>";
		} else {
			echo "<img src='assets/use/walletunlock.png' id='cryptWalletSymbol'>";
		}
		?>
		<div>
		<span data="home" onclick="cryptallica.toggler.setPage('home');">
			<img src="assets/icons/logo.png">
			<a href="javascript:">Home</a>
		</span>
		
		<?php foreach($crypt->coinnames as $coin) {?>
		<span onclick="cryptallica.getCoin('<?php echo $coin;?>')">
			<img src="assets/icons/<?php echo $coin;?>.png">
			<a href="javascript:;"><?php echo ucfirst($coin);?></a>
		</span>
		<?php }?>

		</div>
	</div>
</span>

<span id="cryptContent">


<!------------------Accounts/Home------------------>
<div data="accounts">

	<div id="cryptCoinHomeKeys">
		<a href="javascript:;" data="coinaccount" class="selected">
			<img src="assets/use/people.png" alt="people">
		</a>
		
		<a href="javascript:;" data="walletinfo">
			<img src="assets/use/wallet.png" alt="walletinfo">
		</a>
		
		<a href="javascript:cryptallica.reload();">
			<img src="assets/use/reload.png" alt="reload">
		</a>
	
		<a href="controllers/logout.php?id=<?php echo $crypt->current_coin;?>" style="background:rgb(255,50,50)">
			<img src="assets/use/logout.png" alt="logout">
		</a>
	</div>
		
	
	<span id="cryptCoinHomeDivs">
	<div data="coinaccount">	
		<h2>Accounts</h2>
		
		<h3>Balance <span class="btBlue"><?php echo $crypt->coin("total_balance");?></span></h3>
		
		<table class="BitcoinOmTable" id="crptAccountsTable">
			<tr>
				<th>Label</th>
				<th>Current Amount</th>
			</tr>
		
		<?php foreach($crypt->coin("accounts") as $label => $amount) {
			if(trim($label) === "") {$dlabel = "root";}
			else {$dlabel = $label;}
			echo"<tr>";
			echo"<td><a data='".urlencode($label)."'>{$dlabel}</a></td>";
			echo"<td>{$amount}</td>";
			echo"</tr>";
		}
		?>
		</table>
		
		<a href="javascript:;" id="crptAddNewAccount">+</a>
	</div>
	
	<div data="walletinfo">
	
		<div id="cryptWalletActions">
			<a href="javascript:;" id="cryptWalletLockButton">
				<img src="assets/use/walletlock.png">
				Lock Wallet
			</a>
			
			<h2>Wallet Actions</h2>
			
			<span id="cryptWalletKeys">
				<a data="walletunlock">Unlock</a>
				<a data="changewalletpass">Change Pass</a>
				<a data="encryptwallet">Encrypt Wallet</a>
			</span>
			
			<div data="walletunlock">
			<form method="post" action="controllers/walletunlock.php" id="cryptWalletUnlockForm">
				<span>
					<p>Password</p>
					<input type="password" name="password">
				</span>
			
				<button type="submit">Unlock</button>
			</form>
			</div>
			
			<div data="changewalletpass">
			<form method="post" action="controllers/walletchange.php" id="cryptWalletChangeForm">
				<h3>Wallet Password Change</h3>
				
				<span>
					<p>Password</p>
					<input type="password" name="password">
				</span>
				
				<span>
					<p>New Password</p>
					<input type="password" name="newpassword">
				</span>
				
				<span>
					<p>Retype New Password</p>
					<input type="password" name="newpasswordcheck">
				</span>
				
				<button type="submit">Change Password</button>
			</form>
			</div>
			
			<div data="encryptwallet">
			<form method="post" action="controllers/walletencrypt.php" id="cryptWalletEncryptForm">
				<h3>Wallet Encrypt</h3>
				
				<span>
					<p>New Password</p>
					<input type="password" name="newpassword">
				</span>
				
				<span>
					<p>Retype Password</p>
					<input type="password" name="newpasswordcheck">
				</span>
				
				<button type="submit">Encrypt Wallet</button>
			</form>			
			</div>
			
			<p id="cryptWalletResponses"></p>
			
			<h2>Info</h2>
		
			<table class="BitcoinOmTable">
				<?php foreach($crypt->coin("info") as $type =>$value) {?>
				<tr>
					<th><?php echo ucfirst($type);?></th>
					<td><?php echo $value;?></td>
				</tr>
				<?php }?>
			</table>
		</div>
	</div>
	
	</span>
</div>


<!------------------Sending coins------------------>
<div data="send" id="BitcoinOmSend">
	<h2>Send <?php echo ucfirst($crypt->current_coin)."s";?></h2>
	<div id="BitcoinOmSendAddress">
		<p><span>From </span> <select id="cryptFromAccount">
			<?php foreach($crypt->coin("accountNames") as $accountName) {
				if($accountName === "") {$daccountName = "root";}
				else {$daccountName = $accountName;}
				echo "<option value='{$accountName}'>{$daccountName}</option>";
			}?>
		</select></p>
		<hr/>
		<div id="cryptPayTo">
		<p><span>Pay to </span> 
		<input type="text" name="sendAddress" placeholder="1JqBmoRwSZjrAe6q8wHNQ4aYmh75WjHbma"><select name="toAccount">
			<?php foreach($crypt->coin("accountNames") as $accountName) {
				if($accountName === "") {$daccountName = "select a move";}
				else {$daccountName = $accountName;}
				echo "<option value='{$accountName}'>{$daccountName}</option>";
			}?>
			</select>
		</p>
		<p><span>Label </span> <input type="text" name="sendLabel" placeholder="Bitcoin payee"></p>
		<p><span>Amount </span> 
			<input type="text" name="sendAmount" value="0.00" onkeydown="onlyNumbers(this);">
			<select id="denominatorType">
				<option value="0"><?php echo $crypt->current_sym;?></option>
			</select> 
		</p>
		</div>
		
		<a href="javascript:;" id="cryptAddAddress">+</a>
		<p id="cryptSendError"></p>
	</div>
	

	<span id="BitcoinOmSendBottom">
		<h3>Balance <?php echo $crypt->coin("total_balance");?></h3>
		<button type="button" id="cryptSendButton">Send</button>
	</span>
</div>


<!------------------History and transactions------------------>
<div data="history" id="cryptTrans">

	<div id="CryptTransAll" data="alltransactions">
	<h2>Recent Transactions</h2>
		
	<table class="BitcoinOmTable">
	<tr>
		<th>Account</th>
		<th>Type</th>
		<th>Category</th>
		<th>Amount</th>
		<th>Time Received</th>
	</tr>
	
	<?php foreach($crypt->transactionsListAll() as $trans) {?>
	<tr>
		<td><?php echo $trans["account"];?></td>
		<td><?php 
		if($trans["category"] === "send") {
			echo "<cred>{$trans['category']}</cred>";
		} else {echo "<cgreen>{$trans['category']}</cgreen>";}?></td>
		<td><?php echo floatval($trans["amount"]);?></td>
		
		<?php if($trans["category"] === trim("move")) {?>
		<td><?php echo $trans["otheraccount"];?></td>
		<td><?php echo date("m/d/Y H:i:s", $trans["time"]);?></td>
			
		<?php } else { ?>
		
		<td><a href="javascript:;"><?php echo $trans["address"];?></a></td>
		<td><?php echo date("m/d/Y H:i:s", $trans["timereceived"]);?></td>

		<?php }?>
	</tr>
	<?php } ?>
	</table>
	</div>
	
	<div data=""></div>

</div>


<!------------------I/O Exporting and QRing------------------>
<div data="export" id="cryptExports">

	<span id="cryptExportWallet">
		<img src="assets/use/wallet.png">
		<h3>Wallet</h3>
		
		<div>
		<div id="cryptWalletIOKeys">
			<a href="javascript:;" data="export-wallet">Export</a>
			<a href="javascript:;" data="import-wallet">Import</a>
		</div>
		
		<div data="export-wallet">
		<form action="controllers/backupwallet.php" method="POST" target="_blank">
			<h3>Wallet Password <input type="password" name="password"></h3>
			<button type="submit">Download Wallet</button>
		</form>
		</div>
		
		<div data="import-wallet">
		<form action="controllers/importkey.php" method="POST" id="cryptImportKeyForm">
			<h3>Label <input type="text" name="label" placeholder="Optional"></h3> 
			<h3>Paste Key </h3>
			<textarea name="privatekey"></textarea>
			<button type="submit" id="cryptImportKeyButton">Import Key</button>
		</form>
		<p id="cryptWalletImportMessage"></p>
		</div>
		</div>
	</span>

	<span id="cryptQRCreatorDiv">
		<img src="assets/use/transaction.png">
		<h3>QR and QR Check</h3>
		
		<div>
		<div id="cryptQRCreateKeys">
			<a href="javascript:;" data="request-coin"><cgreen>Request</cgreen></a>
			<a href="javascript:;" data="send-coin"><cred>Send</cred></a>
		</div>
			
		
		<div data="request-coin">
		<form action="controllers/qrcreate.php" method="POST" target="_blank">
			<h3>Send to <select name="toaccount">
			<?php foreach($crypt->coin("accountNames") as $accountName) {
				if($accountName === "") {$daccountName = "root";}
				else {$daccountName = $accountName;}
				echo "<option value='{$accountName}'>{$daccountName}</option>";
			}?>	
			</select></h3>
		
			<h3>Amount <?php echo $crypt->current_sym;?> 
				<input type="text" name="amount" onkeypress="onlyNumbers(this)" value="0.00">
			</h3>
			
			<hr/>
			
			<h3>Label <input type="text" name="label" placeholder="Label Name"></h3>
			
			<h3>Message</h3>
			<textarea name="message"></textarea>
			
			<h3>Type</h3>
			<h4>QR <input type="radio" name="type" value="qr" checked> || QR Check <input type="radio" name="type" value="check"></h4>
			
			<button type="submit">Download</button>
		</form>
		</div>
		
		<div data="send-coin">
		<form action="controllers/qrsend.php" method="POST" target="_blank">
			<h3>From Account <select name="fromaccount">
			<?php foreach($crypt->coin("accountNames") as $accountName) {
				if($accountName === "") {$daccountName = "root";}
				else {$daccountName = $accountName;}
				echo "<option value='{$accountName}'>{$daccountName}</option>";
			}?>	
			</select></h3>
		
			<h3>Amount <?php echo $crypt->current_sym;?> 
				<input type="text" name="amount" onkeypress="onlyNumbers(this)" value="0.00">
			</h3>
			
			<p><cred>Warning: This will remove this amount and send it to an address on the account which will dump the key used for importing. It is wise to do this not connected to the internet.</cred></p>
			
			<h3>Wallet Password <input type="password" name="password"></h3>
			<button type="submit">Download Check</button>
		</form>
		
		</div>
		</div>
	</span>
	
	
	<span>
		<img src="assets/use/memory.png">
		<h3>Transactions</h3>
		<div>
			<a href="controllers/downloadtransactions.php?type=csv">Download CSV</a>
		</div>
	</span>
</div>
</span>