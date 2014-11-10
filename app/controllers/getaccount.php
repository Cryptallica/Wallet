<?php
require_once("../../core/cryptallica.php");
$account = isset($_GET["id"]) ? $_GET["id"]:"";

$crypt = new Cryptallica();
$account_data = $crypt->getAccount($account);
if(empty($account_data)) {
	echo "No Data found for account ".$_GET["id"];
	exit;
}
?>

<h2>Account <a href="javascript:;"><?php echo $account;?></a></h2>

<table class="BitcoinOmTable">
	<tr>
		<th>Type</th>
		<th>Amount</th>
		<th>Address</th>
		<th>Time Received</th>
	</tr>

	<?php foreach($account_data as $transaction) {?>
	<tr>
		<td><?php 
		if($transaction["category"] === "send") {
			echo "<cred>{$transaction['category']}</cred>";
		} else {echo "<cgreen>{$transaction['category']}</cgreen>";}?></td>
		<td><?php echo floatval($transaction["amount"]);?></td>
		
		<?php if($transaction["category"] === trim("move")) {?>
		<td><?php echo $transaction["otheraccount"];?></td>
		<td><?php echo date("m/d/Y H:i:s", $transaction["time"]);?></td>
			
		<?php } else { ?>
		
		<td><a href="javascript:;"><?php echo $transaction["address"];?></a></td>
		<td><?php echo date("m/d/Y H:i:s", $transaction["timereceived"]);?></td>

		<?php }?>
	</tr>
	<?php }?>
</table>