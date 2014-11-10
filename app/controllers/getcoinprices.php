<?php require_once("../../core/cryptallica.php");
$crypt = new Cryptallica();
$prices_data = $crypt->get_prices();
?>

<div id="cryptCoinPricesData">
<a href="javascript:cryptallica.getPrices();">
	<img src="assets/use/reload.png">
	Reload
</a>

<h3>Updated: <span class="btBlue"><?php echo date("m/d/Y H:i:s");?></span></h3>
<h3><a href="https://c-cex.com/" target="_blank">https://c-cex.com/</a></h3>
<table class="BitcoinOmTable">
	<tr>
		<th>Symbol</th>
		<th>Amount per USD</th>
	</tr>
		
<?php foreach($prices_data as $symbol => $data) { ?>
	<tr>
		<td><?php echo $symbol;?></td>
		<td>$<?php echo $data["lastprice"];?></td>
	</tr>
<?php } ?>

</table>

</div>