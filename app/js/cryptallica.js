//Utils////
function onlyNumbers(e){
	var that = e;
    setTimeout(function(){
    	that.value = that.value.replace(/[^0-9.]/, "");   
    },300);
}
var cryptControl = "controllers/";
var cryptLoader = "<div class='loading-container'><div class='loader'><div class='loading-bars'><div class='bar'></div><div class='bar'></div><div class='bar'></div><div class='bar'></div><div class='bar'></div></div></div></div>";


/****************************Cryptallica Object****************************/
//Main wrapper for all main data use
var cryptallica = {

	init: function() {
		this.cryptLoginUrl = "controllers/login.php";
		this.loadHTML();	
		
		var crypt = this;
		setTimeout(function() {
			crypt.getPrices();
		}, 600);
	},

	loadHTML: function() {
		var doc = document;
		var crypt = this;
		
		this.cryptWrap = doc.getElementById("cryptWrap");
		this.cryptHome = doc.getElementById("cryptHome");
		this.cryptCoinDiv = doc.getElementById("cryptMain");
		this.coinPricesDiv = doc.getElementById("cryptCoinPrices");
		
		//Main page toggle
		var cryptallicaKeys = doc.getElementById("cryptMainKeys");
		
		this.toggler = new PageToggler(cryptallicaKeys, this.cryptWrap, {
			keyAttribute:"data"
		});
		
		//Setup coin gets
		this.cryptLogins = doc.getElementById("cryptCoinLogins");
		var cspans = this.cryptLogins.getElementsByTagName("span");
		var l = cspans.length;
		for(var i = 0; i < l; i++) {
			thisSpan = cspans[i];
			addEvent("click", thisSpan, function(e){crypt.getCoin(e);}, true);
		}
	},
	
	loadCoin: function() {},
	
	login: function(responseText) {	
		if(trim(responseText) === "%%%%TRUE%%%%") {
			this.loginAni.hide();
			this.getCoin(this.currentCoin);	
			setTimeout(function(){coinJS();}, 25);
			return true;
		} 
			
		var crypt = this;	
		crypt.loginError.innerHTML = responseText;	
		setTimeout(function() {
			crypt.loginAni.hide();
		}, 1000);
	},
	
	loginJS: function() {
		//Login
		var crypt = this;
		crypt.loginWrap = document.getElementById("cryptLoginLoading");
		crypt.loginAni = new animateHTML(crypt.loginWrap, {
			classOn: "sizeUp",
			classOff: "sizeDown",
			duration: 500
		});
		
		crypt.loginError = document.getElementById("cryptLoginError");
		
		setTimeout(function() {
			ajaxForm("cryptLoginForm", function(responseText) {
				setTimeout(function(){
					crypt.login(responseText);
				}, 800);
			});
		}, 50);
	},

	getAccounts: function(callback) {
		ajaxGetPage((cryptControl + "getaccounts.php"), function(responseText) {
			console.log(responseText);
			if(callback) {
				callback(responseText);
			}
		});
	},
	
	getAccount: function(accountName, callback) {
		if(!isset(accountName)) {return false;}
		var url = cryptControl + "getaccount.php?id=" + accountName;
		ajaxGetPage(url, function(responseText) {
			if(callback) {callback(responseText);}
		});
	},
	
	getCoin: function(e) {
		if(typeof(e) === "string") {var coinName = e;}
		else if(typeof(e)==="object") {
			thisTarget = e.target;
			if(e.target.tagName === "A" || e.target.tagName === "IMG") {
				thisTarget = e.target.parentNode;
			}
			
			var coinName = thisTarget.getAttribute("data");
		} else {
			return false;	
		}
		
		var crypt = this;
		var url = "controllers/getcoin.php?id="+coinName;
		ajaxGetPage(url, function(responseText) {
			crypt.cryptCoinDiv.innerHTML = responseText;
			crypt.toggler.setPage("coin");
			
			var login = document.getElementById("cryptLoginForm");
			
			if(!login) {
				coinJS();
				return true;
			}
			
			crypt.loginJS();
		});
		
		this.currentCoin = coinName;
	},
	
	reload: function() {
		var crypt = this;
		ajaxGetPage((cryptControl + "reload.php"), function() {
			crypt.getCoin(crypt.currentCoin);
		});
	},
	
	getPrices: function() {
		var crypt = this;
		ajaxGetPage((cryptControl + "getcoinprices.php"), function(responseText) {
			crypt.coinPricesDiv.innerHTML = responseText;
		});	
	},
	
	backupWallet: function() {
		window.location = (cryptControl + "backupwallet.php");
	}
};

//Load coin getting function
addEvent("load", window, function() {cryptallica.init();});

/****************************CoinJS Wallet js****************************/
function coinJS() {

    var doc = document;

    //Main page toggle
    var cryptallicaKeys = doc.getElementById("cryptallicaKeys");
    var cryptallicaDivs = doc.getElementById("cryptContent");
    var cryptallicaToggler = new PageToggler(cryptallicaKeys, cryptallicaDivs, {
        keyAttribute:"data",
        homeName: "accounts"
    });
    
    //Coin navigation
    var cryptallicaCurrencies = doc.getElementById("cryptallicaCurrencies");
    var cryptCurrentImg = cryptallicaCurrencies.getElementsByTagName("img")[0];
    var cryptallicaCurPull = cryptallicaCurrencies.getElementsByTagName("div")[0];
    var cryptPulldown = new animateHTML(cryptallicaCurPull, {
        classOn: "heightOn",
        classOff: "heightOff",
        duration: 500   
    });
    
    cryptCurrentImg.onclick = function() {
        cryptPulldown.toggle();
    };
    
    
    
    
    /****************************Account and settings page****************************/
    //Accounts toggle
    var cryptAccountsToggle = new PageToggler("cryptCoinHomeKeys", "cryptCoinHomeDivs", {
        keyAttribute: "data",
        homeName: "coinaccount"
    });
    
    //Transactions find
    var crptAccountsTable = doc.getElementById("crptAccountsTable");
    addEvent("click", crptAccountsTable, function(e) {
        var thisTarget = e.target;
        if(thisTarget.tagName !== "A") {return false;}
        
        var accountName = thisTarget.getAttribute("data");
        cryptallica.getAccount(accountName, function(responseText) {
            crytTrans.innerHTML = responseText;
            cryptallicaToggler.setPage("history");
        });
    });
    
    //Create new account
    doc.getElementById("crptAddNewAccount").onclick = function() {
        var doc = document;
        var addTr = doc.createElement("tr");
        var inputTd = doc.createElement("td");
        var input = doc.createElement("input");
        input.type = "text";
        input.onblur = function() {
            setTimeout(function() {
                crptAccountsTable.removeChild(addTr);
            }, 750);        
        };
        
        inputTd.appendChild(input);
        
        var createTd = doc.createElement("td");
        var createB = doc.createElement("button");
        createB.innerHTML = "Create Account";
        createB.onclick = function() {
            var postData = {accountName: input.value};
            
            ajaxPost(postData,(cryptControl + "newaccount.php"), function(responseText) {
                console.log(responseText);
                cryptallica.getAccounts(function(responseText){
                    crptAccountsTable.innerHTML = responseText;
                });
            });
        };
        createTd.appendChild(createB);
        
        addTr.appendChild(inputTd);
        addTr.appendChild(createB);
        crptAccountsTable.appendChild(addTr);
        input.focus();
    };
    
    //Wallet Toggle
    var cryptWalletToggle = new PageToggler("cryptWalletKeys", "cryptWalletActions", {
        keyAttribute: "data",
        homeName: "walletunlock"
    });
    
    //Wallet html elements
    var cryptWalletSymbol = document.getElementById("cryptWalletSymbol");
    
    //Wallet page ajax
    var walletResponseDiv =  document.getElementById("cryptWalletResponses");
    function walletResponse(responseText) {
        walletResponseDiv.innerHTML = responseText;
    }
    
    //Unlock Wallet
    ajaxForm("cryptWalletUnlockForm", function(responseText) {
        walletResponse(responseText);
        if(responseText === "Wallet Unlocked") {
            cryptWalletSymbol.src = "assets/use/walletunlock.png";
        }
    }); 
    
    ajaxForm("cryptWalletChangeForm", walletResponse); //Change Wallet Password
    ajaxForm("cryptWalletEncryptForm", walletResponse); //Encrypt Wallet
    
    //Wallet Lock
    doc.getElementById("cryptWalletLockButton").onclick = function() {
        ajaxGetPage((cryptControl + "walletlock.php"), function(responseText) {
            walletResponse(responseText);
            if(trim(responseText) === "Wallet Locked") {
                cryptWalletSymbol.src = "assets/use/walletlock.png";
            }
        });     
    };
    
    /****************************Sending coins****************************/ 
    //Transfer
    var crytTrans = document.getElementById("cryptTrans");
    
    
    //controllers directory must link to src
    
    //Send Actions
    var cryptSendDiv = doc.getElementById("BitcoinOmSendAddress");
    var cryptPayTo = doc.getElementById("cryptPayTo");
    var cryptFromAccount = doc.getElementById("cryptFromAccount");
    var cryptSendButton = doc.getElementById("cryptSendButton");
    var cryptSendError = doc.getElementById("cryptSendError");
    
    //Send submit sends data to sendcoins.php
    function cryptSend() {
        var fromAccount = cryptFromAccount.value;
    
        //Get sends
        var cryptSends = cryptSendDiv.getElementsByTagName("input");
        var t = cryptSends.length;
        var postData = {fromAccount: fromAccount};
        var sendData = [];
        var sendArr = {};
        var counter = 0;
        for(var i = 0; i < t; i++) {
            var thisChild = cryptSends[i];
            var inputName = thisChild.getAttribute("name");
            if(!inputName) {continue;}
            
            sendArr[inputName] = thisChild.value;
            
            if(inputName === "sendAddress") {
                var thisNext = thisChild.parentNode.getElementsByTagName("select")[0];
                //console.log(thisNext);
                if(thisNext) {
                    if(thisNext.value !== "") {
                        sendArr[inputName] = thisNext.value;
                    }
                } 
            }
            
            if(inputName === "sendAmount") {
                counter++;
                sendData.push(sendArr);
                sendArr = {};
            }       
        }
        postData["sends"] = sendData;
        
        var url = cryptControl + "sendcoins.php";
        ajaxPost(postData, url, function(responseText) {
            cryptSendError.innerHTML = responseText;
        });
        console.log(postData);
    }
    //cryptSendButton initializes send
    cryptSendButton.onclick = function() {cryptSend();};
    
    //Add address 
    cryptAddAddress = doc.getElementById("cryptAddAddress");
    cryptAddAddress.onclick = function() {
        var hr = document.createElement("hr");
        var newAddress = cryptPayTo.cloneNode(10);
        newAddress.appendChild(closeAddressButton());
        
        cryptSendDiv.insertBefore(hr, cryptAddAddress);
        cryptSendDiv.insertBefore(newAddress, cryptAddAddress);
    };
    
    function closeAddressButton() {
        var el = document.createElement("a");
        el.onclick = function() {
            var thisParent = this.parentNode;
            var gran = thisParent.parentNode;
            gran.removeChild(thisParent);
        };
        el.className = "cryptCloseRed";
        el.innerHTML = "Remove";
        return el;
    }
    
    
    /****************************I/O page exports and qrs****************************/  
    //Wallet Export Toggler
    var walletExportToggler = new PageToggler("cryptWalletIOKeys", "cryptExportWallet", {
        keyAttribute: "data",
        homeName: "export-wallet"
    });
    
    //Wallet import html
    var walletImportMessage = doc.getElementById("cryptWalletImportMessage");
    
    //Wallet import ajax
    ajaxForm("cryptImportKeyForm", function(responseText) {
        walletImportMessage.innerHTML = responseText;
    });
    
    //Wallet loading
    doc.getElementById("cryptImportKeyButton").onclick = function() {
        walletImportMessage.innerHTML = "This might take a while due to rescan " + cryptLoader;
    };
        
    //Qr Check Toggler
    var qrCheckToggler = new PageToggler("cryptQRCreateKeys", "cryptQRCreatorDiv", {
        keyAttribute: "data",
        homeName: "request-coin"
    });
}