<?php
require_once("../../core/cryptallica.php");

$crypt = new Cryptallica();
$accounts = $crypt->getaccounts();
?>

<tr>
	<th>Label</th>
	<th>Current Amount</th>	
</tr>

<?php foreach($accounts as $label => $current_amount) {
	echo"<tr>";
	echo"<td><a data='".urlencode($label)."'>{$label}</a></td>";
	echo"<td>{$current_amount}</td>";
	echo"</tr>";	
} ?> 