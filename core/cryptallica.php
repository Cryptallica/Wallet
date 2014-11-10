<?php 

//JSON RPC connects to bitcoin cli
!defined("DS")? define("DS", DIRECTORY_SEPARATOR):null;

$dir_space = dirname(__FILE__).DS;
define("CRYPTALLICA_DIR", $dir_space);
require_once($dir_space."lib".DS."jsonRPCClient.php");

//Fonts
define("C_OPENSANS", $dir_space."fonts".DS."OpenSans-Light.ttf");


$back_dir = preg_replace("/[a-zA-Z0-9]*?\/$/i", "", $dir_space);
define("CRYPTALLICA_CHECK", $back_dir."app".DS."assets".DS."checkimage.jpg");
define("CRYPT_ICON", $back_dir."app".DS."assets".DS."icons".DS);
define("CRYPT_LAYOUT", $back_dir."app".DS."layouts".DS);


class Cryptallica {
	
	public $bt = array();
	public $info = array();
	public $username;
	private $password;
	private $connectUrl;
	
	//Account and transaction data
	public $total_balance = 0;
	public $accountNames = array();
	public $accounts = array();
	
	//Wallet actions
	public $wallet_unlocked = false;
	public $wallet_timeout = 4350;//3 Hours
	
	//Coin details
	private $json_config;
	public $current_coin = false;
	public $current_sym = false;
	public $coin_data = array();
	public $available_coins = array(
		"bitcoin" => [
			"symbol" => "BTC",
			"deviations" => [
				"3" => "mBTC",
				"6" => "µBTC",
				"8" => "satoshi" 
			]
		], 
		
		"litecoin" => [
			"symbol" => "LTC",
			"deviations" => [
				"3" => "mLTC",
				"6" => "µLTC",
				"8" => "smallest"
			]
		], 
		
		"dogecoin" => [
			"symbol" => "DOGE",
			"deviations" => [
				"3" => "mDOGE",
				"6" => "µDOGE",
				"8" => "shibe" 
			]
		], 
		
		"cannabiscoin" => [
			"symbol" => "CANN",
			"deviations" => [
				"3" => "mCANN",
				"6" => "µCANN",
				"8" => "smallest"
			]
		],
		
		"peercoin" => [
			"symbol" => "PPC",
			"deviations" => [
				"3" => "mPPC",
				"6" => "µPPC",
				"8" => "smallest"
			]
		],
		
		"darkcoin" => [
			"symbol" => "DRK",
			"deviations" => [
				"3" => "mDRK",
				"6" => "µDRK",
				"8" => "smallest"
			]
		]
	);	
	public $coinnames = [];
	
	
/****************************SETUP METHODS****************************/

	//Constructor simple setups session and checks if current coin
	function Cryptallica() {
		
		//Set timezone to LA because that's metal
		date_default_timezone_set('America/Los_Angeles');
		
		//Setup and check session
		if(function_exists("session_status")) {
			if (session_status() == PHP_SESSION_NONE) {session_start();}
		} else {
			if(session_id() == "") {session_start();}
		}
		//Create session array
		if(!isset($_SESSION["cryptallica"])) {
			$_SESSION["cryptallica"] = array();
		}
		
		//Setup available coinnames
		$this->coinnames = array_keys($this->available_coins);
		foreach($this->available_coins as $coinname => $data) {
			if(!isset($_SESSION["cryptallica"][$data["symbol"]])) {
				$_SESSION["cryptallica"][$data["symbol"]] = array();
			}
		}
		
		$this->coin_data = $_SESSION["cryptallica"];
		
		$this->getcoin();
	}
	
	//Check if coin is use in session for quick access
	public function getcoin() {
		if(!isset($_SESSION["cryptallica"]["current coin"])) {return true;}
		if($this->iscoin($_SESSION["cryptallica"]["current coin"])) {
			$this->current_coin = $_SESSION["cryptallica"]["current coin"];
			$this->current_sym = $this->available_coins[$this->current_coin]["symbol"];
			$this->coin_data[$this->current_sym] = $_SESSION["cryptallica"][$this->current_sym];
			$this->checkWalletLock();
		}
	}
	
	//Config loaded from config.json
	private function getconfig() {
		if(empty($this->json_config)) {
		$this->json_config = json_decode(
			file_get_contents(CRYPTALLICA_DIR."config.json")
		);
			if(!is_object($this->json_config)) {die("Config not valid json");}
		}
	}
	
	//Set current coin to this coin. requires login username and pass
	public function usecoin($coinname, $username, $password) {
		if(!$this->isCoin($coinname)) {return false;}
		if(!$this->iscoin($coinname)) {return "Coin not found";}
		$username = strval($username);
		$password = strval($password);
		$symbol = $this->available_coins[$coinname]["symbol"];
		
		//Set coin
		$this->current_coin = $_SESSION["cryptallica"]["current coin"] = $coinname;
		$this->current_sym = $symbol;
		
		//Attempt login die on failure
		if(isset($_SESSION["cryptallica"][$symbol])) {
			$this->coin_data[$symbol] = $_SESSION["cryptallica"][$symbol];
			if(empty($this->coin("info"))) {
				$this->connect($coinname, $username, $password);
			}
		} else {
			$this->connect($coinname, $username, $password);
		}
	}
	
	//Logout uses param 1 as specifiy or false for all
	public function logout($coin=false) {
		if(!$coin) {
			$_SESSION["cryptallica"] = null;
			$this->coin_data = false;
		} else {
			if($this->isCoin($coin)) {
				$sym = $this->available_coins[$coin]["symbol"];
				$_SESSION["cryptallica"][$sym] = $this->coin_data[$sym] = array();	
			}
		}
	}
	
	//Connect to jsonRPC client and die on failure
	private function connect($coinname='', $username='', $password='') {
		$this->getconfig();
		try {
			$port = $this->json_config->$coinname->port;
			$this->connectUrl = "http://{$username}:{$password}@127.0.0.1:{$port}/";
			$rsrc = new jsonRPCClient($this->connectUrl);
			$info = $rsrc->getinfo();
			if(empty($info)) {
				throw new Exception("Failed to capture info");
			}
			
			$this->set("rsrc", $rsrc);
			$this->extract();
		} catch(Exception $e) {
			die("Connection failed.<br/> Check username or password and check coin server is running");
		}
	}
	
	//Check if json-rpc is working
	public function can_connect() {
    	try {
	    	$r = $this->getinfo();
	    } catch (Exception $e) {
	    	return false;
	    }
	   	return true;
    }
	
	//Checks login based on any login or string of coin
	public function isLoggedIn($coin=false) {
	
		if(!$coin) {
			if($this->coin("info")) {return true;} 
			else {return false;}
		}
				
		$sym = $this->available_coins[$coin]["symbol"];
		if(!empty($this->coin_data[$sym]["info"])) {
			return true;
		} else {
			return false;
		}
	}
	
	//$this->coinnames coins array
	public function iscoin($coinname='') {
		if(in_array($coinname, $this->coinnames)) {return true;}
		else {return false;}
	}
	
	//Set coin if loggedIn
	public function setCoin($coin) {
		if(!$this->isCoin($coin)) {return false;}
		$sym = $this->available_coins[$coin]["symbol"];
		
		$_SESSION["cryptallica"]["current coin"] = $coin;
		$this->current_coin = $coin;
		$this->current_sym = $sym;
	}

	//Getter function for current coin in use
	public function coin($field='') {
		if(!isset($this->current_coin)) {return false;}
		
		//Return data or return false
		if(!isset($this->coin_data[$this->current_sym][$field])) {return false;}
		else {
			return $this->coin_data[$this->current_sym][$field];
		}
	}
	
	//Sets data to session and coin_data
	private function set($key="", $bitdata="") {
		$_SESSION["cryptallica"][$this->current_sym][$key] = $bitdata;
		$this->coin_data[$this->current_sym][$key] = $bitdata;
	}

	//Extract initial info from wallet
	private function extract() {
		$this->set("info", $this->coin("rsrc")->getinfo());
		$this->set("accounts", $this->coin("rsrc")->listaccounts());
		$this->set("accountNames", array_keys($this->coin("accounts")));
		$this->set("total_balance", $this->coin("rsrc")->getbalance());
	}
	
	//Reload Data
	public function reload() {
		if($this->isLoggedIn()) {
			$this->extract();
		}
	}

	//Stop Logged Coin Server
	public function stopServer() {
		if($this->isLoggedIn()) {
			$this->coin("rsrc")->stop();
			return true;
		}
		return false;
	}
	
	//Controller json get data
	public function json_get($validations=[]) {
		
		$json_data='';
		if(isset($_POST["json_data"])) {
			$json_data = json_decode($_POST["json_data"]);
		}
		
		if(gettype($json_data) !== "object") {
			$json_data = json_decode(json_encode($_POST));
		}
		
		if(!empty($validations)) {
			foreach($validations as $field) {
				if(!isset($json_data->$field)) {echo $field." Not given";exit;}
			}
		}
		return $json_data;
	}
	
	
/****************************CONNECTION METHODS****************************/
	
	//Wallet unlock for default 3 hours
	public function walletUnlock($password) {
		try {
			if($this->isLoggedIn()) {
				$this->coin("rsrc")->walletpassphrase($password, $this->wallet_timeout);
				$this->set("walletlock", ($this->wallet_timeout + time()));
				return true;
			}
			return false;
		} catch(Exception $e) {
			return false;
		}
	}
	
	//Lock current wallet
	public function walletLock() {
		try {
			$this->coin("rsrc")->walletlock();
			$this->set("walletlock", false);
			return true;
		} catch(Exception $e) {
			return false;
		}
	}
	
	//Encrypt if no wallet encryption
	public function walletEncrypt($password) {
		try {
			if($this->isLoggedIn()) {
				$this->coin("rsrc")->encryptwallet($password);
				return true;
			}
		} catch(Exception $e) {
			return false;
		}
		return false;
	}
	
	//Change wallet password of current wallet
	public function walletPassChange($password, $newpass) {
		try {
			if($this->isLoggedIn()) {
				$this->coin("rsrc")->walletpassphrasechange($password, $newpass);
				return true;
			}
		} catch(Exception $e) {
			return false;
		}
		return false;
	}
	
	//download wallet straight to browser
	public function walletDownload($password) {
		$this->walletLock();
		if(!$this->walletUnlock($password)) {die(" Password incorrect");}
		
		$tmpfile = tempnam("/tmp","WALLET");
		chmod($tmpfile, 0777);
		$this->coin("rsrc")->backupwallet($tmpfile);	
		$this->download($tmpfile, "backup.dat");
	}
	
	//Check if wallet lock is still valid
	private function checkWalletLock() {
		if($this->coin("walletlock") > time()) {
			$this->wallet_unlocked = true;
		}
	}
	
	//Import wallet
	public function importPrivateKey($key, $label="") {
		if($this->isLoggedIn()) {
			try {
				$this->coin("rsrc")->importprivkey($key, $label);
				return true;
			} catch(Exception $e) {
				return false;
			}
		}
	}
	
	//Test to see if it is a valid account
	public function isAccount($accountName="") {
		if(in_array($accountName, $this->coin("accountNames"))) {return true;} 
		else {return false;}
	}
	
	public function clean_address($accountName="") {
		return preg_replace("/[ $%^#&@*!\(\)-+\\\<\>]/i", "", $accountName);
	}
	
	//Functions to grab data
	public function get_transactions($accountName, $number=10) {
		if(!$this->isAccount($accountName)) {return false;}
		return $this->bt->listtransactions($accountName, (int) $number);
	}
	
	public function createaddress($accountName="") {
		if($this->isAccount($accountName) || trim($accountName) === "") {
			return false;
		}
		
		$accountName = $this->clean_address($accountName);
		return $this->coin("rsrc")->getnewaddress($accountName);
	}
	
	public function getAccounts() {
		$this->set("accounts", $this->coin("rsrc")->listaccounts());
		return $this->coin("accounts");
	}	
	
	public function getAccount($accountName="") {
		if($this->isAccount($accountName)) {
			$this->set("currentAccount", $this->coin("rsrc")->listtransactions($accountName));
			return $this->coin("currentAccount");
		}
	}
	
	public function hasAmount($accountFrom='', $amount=0) {
		if(floatval($this->coin("accounts")[$accountFrom]) > floatval($amount) && $amount != 0) {
			return true;
		} 
		else {return false;}
	}
	
	//Validate address or return true anyway
	public function validateAddress($toaddress='') {
		try {
			$validate = $this->coin("rsrc")->validateaddress($toaddress);
			if($validate["isvalid"]) {return true;} 
			else {return false;}
		} catch(Exception $e) {
			//go anyway
			return true;
		}
	}
	
	//Send from account to address only one
	private function sendFrom($accountFrom='', $toaddress='', $amount=0) {
		if(!$this->isLoggedIn()) {return false;}
		if(!$this->isAccount($accountFrom)) {return false;}
		if(!$this->validateAddress($toaddress)) {return false;}
		if((int)$amount == 0) {return false;}
		
		if(!$this->hasAmount($accountFrom, $amount)) {
			echo $accountFrom." does not have enough to send.";
			return false;
		}
		
		try {
			$this->coin("rsrc")->sendfrom($accountFrom, $toaddress, (float) $amount);
			return true;
		} catch(Exception $e) {
			return false;
		}
	}
	
	//Transfer many from account to "address":"amount"
	public function transfer($fromAccount='', $sendData=[]) {
		if(!$this->isAccount($fromAccount)) {
			return $fromAccount." not valid account";
		}
		
		if(empty($sendData)) {
			return "Transfer data empty";
		}
		
		$count = (float) $this->coin("info")["paytxfee"];
		$postSend = array();
		foreach($sendData as $amount) {
		
			$address = array_keys($amount)[0];
			if((float) $amount[$address] == 0) {
				echo "Amount cannot be 0<br/>";
				return false;
			}
			
			if(!$this->validateAddress($address)) {
				echo "Address not valid<br/>";
				return false;
			}
						
			$count = $count + (float) $amount;
			$postSend[$address] = (float) $amount[$address];
		}
		
		if(!$this->hasAmount($fromAccount, $count)) {
			return false;
		}
				
		try {
			$this->coin("rsrc")->sendmany((string)$fromAccount, $postSend);
			return true;
		} catch(Exception $e) {
			echo $e->getmessage();
			return false;
		}
	}
	
	private function dumpPrivateKey($address) {
		try {
			return $this->coin("rsrc")->dumpprivkey($address);
		} catch(Exception $e) {
			return false;
		}
	}
	
	//Move amount to another account
	public function accountMove($accountFrom='', $newAccount='', $amount) {
		if($this->isAccount($accountFrom) && $this->isAccount($newAccount)) {
			$this->coin("rsrc")->move($accountFrom, $newAccount, floatval($amount));
		}
	}

	//List all transactions from no arguments
	public function transactionsListAll() {
		if($this->coin("alltrans")) {
			return $this->coin("alltrans");}
		
		$this->set("alltrans", $this->coin("rsrc")->listtransactions());
		
		return $this->coin("alltrans");
	}
	
	//Download transactions as CSV format
	public function downloadCSV() {
		$tmp_file = tempnam("/tmp", "CSV");
		$transaction = $this->transactionsListAll();
		
		$csv = fopen($tmp_file, "w");
		fputcsv($csv, array_keys($transaction[0]));
		foreach($transaction as $trans) {fputcsv($csv, $trans);}
		fclose($csv);
		
		$this->download($tmp_file, "transactions.csv");
	}
	
	//Download takes a file url and filename and downloads it to browser
	//Deletes file after download
	public function download($url, $filename='download.txt') {
		if(!is_file($url)) {return "File not Found";}
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header("Content-Disposition: attachment; filename={$filename}");
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($url));
		readfile($url);
		unlink($url);
		exit;
	}
	
	
/****************************Check Creationg METHODS****************************/	
	
	public function sendQRCreate($password, $fromAccount, $amount=0, $json_data) {
		if(!$this->isLoggedIn()) {return false;}
		
		$newaddress = $this->coin("rsrc")->getnewaddress($fromAccount);
		echo "Address: ".$newaddress."</hr>";
		if(!$newaddress) {return false;}
		
		if(!$this->sendFrom($fromAccount, $newaddress, $amount)) {
			echo " Send failed";
			return false;
		}
		
		$privatekey = $this->dumpPrivateKey($newaddress);
		
		if(!$privatekey) {return false;}
		
		$amount = $amount." ".$this->current_sym;
		
		//Create check
		require_once(CRYPTALLICA_DIR."lib".DS."checkcreator.php");
		$check_creator = new CheckCreator();
		$check = $check_creator->createSendCheck($this->current_coin, $amount, $newaddress, $privatekey);
		
		header('Content-Type: image/png');
		imagepng($check);
		imagedestroy($check);
	}
	
	//Create check for requesting coins
	public function createRequestCheck($account='', $amount='', $label='', $message='') {
				
		if(empty($account)) {
			$coin_address = $this->coin("rsrc")->getnewaddress();
		} else {
			$coin_address = $this->coin("rsrc")->getnewaddress($account);
		}
		
		$url = $this->createUrlQR($coin_address, $amount, $label, $message);
		if(!$url) {return false;}

		$amount = $amount." ".$this->current_sym;
	
		//Create check
		require_once(CRYPTALLICA_DIR."lib".DS."checkcreator.php");
		$check_creator = new CheckCreator();
		$check = $check_creator->createRequestCheck($url, $this->current_coin, $coin_address, $amount, $label, $message);

		header('Content-Type: image/png');
		imagepng($check);
		imagedestroy($check);
	}
	
	//Creates url for qr creation. ex bitcoin:fjk4DSF23sjfk
	public function createUrlQR($address='', $amount=0, $label='', $message='') {
		
		//Peercoin fix
		if($this->current_coin === "peercoin") {$coin = "ppcoin";} 
		else {$coin = $this->current_coin;}
		
		//Create QR Code data
		$qrcodedata = $coin.":".$address."?amount=".$amount;
		!empty($label)? $qrcodedata.="&label=".urlencode($label):null;
		!empty($message)? $qrcodedata.="&message=".urlencode($message):null;
		return $qrcodedata;
	}
	
		
/****************************Coin Prices METHODS****************************/	
	
	public function get_prices() {
		$cCex = "https://c-cex.com/t/prices.json";
		$cCex_json = $this->get_coin_json($cCex);
		$cCex_data = array();

		foreach($this->available_coins as $coinname => $data) {
			$dattr = strtolower($data["symbol"]."-usd");
			if(isset($cCex_json[$dattr])) {
				$cCex_data[$data["symbol"]] = $cCex_json[$dattr];
			}
		}
		
		return $cCex_data;
	}

	public function get_coin_json($url) {
		$ch = curl_init($url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
		$response = json_decode(curl_exec($ch), true);
		curl_close($ch);
		return $response;
	}
}

