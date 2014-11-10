/****************************Utils****************************/
function isset(value) {
	if(typeof(value) == "undefined") {return false;}
	else if(value == null) {return false;}
	else {return true;}
}

function trim(text) {return text.replace(/^\s+|\s+$/g, '');}

function in_array(needle, haystack, argStrict) {
	var key = '',
	strict = !! argStrict;
	if (strict) {
		for(key in haystack) {if (haystack[key] === needle) {return true;}}
	} else {
		for(key in haystack) {if (haystack[key] == needle) {return true;}}
	}
	return false;
}

function encodeFormData(data) {
	if (!data) return "";
	var pairs = [];
	for(var name in data) {
		if(!data.hasOwnProperty(name))continue;
		if(typeof data[name] === "function")continue;
		if(typeof data[name] === "undefined")continue;
		var value = data[name].toString();
		value = encodeURIComponent(value).replace("%20", "+");
		pairs.push(name + "=" + value);
	}
	return pairs.join("&");
}

function htmlDecode(input){
  var e = document.createElement('div');
  e.innerHTML = input;
  return e.childNodes.length === 0 ? "" : e.childNodes[0].nodeValue;
}

function addEvent(evnt, elem, func, bubble) {
	if (elem.addEventListener) {
		elem.addEventListener(evnt,func,bubble);
	} else if (elem.attachEvent) { 
			elem.attachEvent("on"+evnt, func);
	} 
	else { elem[evnt] = func;}
}

//Get Elements By Class Name
function GEBCN(cn){
    if(document.getElementsByClassName) // Returns NodeList here
        return document.getElementsByClassName(cn);

    cn = cn.replace(/ *$/, '');

    if(document.querySelectorAll) // Returns NodeList here
        return document.querySelectorAll((' ' + cn).replace(/ +/g, '.'));

    cn = cn.replace(/^ */, '');

    var classes = cn.split(/ +/), clength = classes.length;
    var els = document.getElementsByTagName('*'), elength = els.length;
    var results = [];
    var i, j, match;

    for(i = 0; i < elength; i++){
        match = true;
        for(j = clength; j--;)
            if(!RegExp(' ' + classes[j] + ' ').test(' ' + els[i].className + ' '))
                match = false;
        if(match)
            results.push(els[i]);
    }

    // Returns Array here
    return results;
}

//HTML animator will show and hide elements based on given classes in customize
function animateHTML(animator, customize) {
	if(typeof(animator) === "string") {this.animator = document.getElementById(animator);}
	else if(typeof(animator) === "object") {
		this.animator = animator;
	} else {
		console.log("Not object or string for animator.");
		return false;	
	}
	
	customize = customize || {};
	
	//Animation Data
	this.classOn = customize.classOn || "";
	this.classOff = customize.classOff || "";
	this.aniTime = customize.animationTime || 750;
	this.showDefault = customize.showDefault || false;
	//Wrapper Data
	this.isOn = false;
		
	this.init();
}

animateHTML.prototype = {
	init: function() {
		if(!this.showDefault) {this.clear();}	
		else {
			this.isOn = true;
			this.animator.className = this.classOn;	
			this.animator.style.display = "block";
		}
	},
	
	show: function() {
		if(this.isOn) {return true;}
		
		this.animator.style.display = "block";
		this.animator.style.opacity = "0";
		
		var thisOm = this;
		setTimeout(function() {
			thisOm.animator.className = thisOm.classOn;
			thisOm.animator.style.opacity = "1";
		}, 100);
		this.isOn = true;
	},
	
	on: function() {
		this.animator.className = this.classOn;
		this.animator.style.display = "block";
		this.animator.style.opacity = "1";
		this.isOn = true;
	},
	
	hide: function() {
		if(!this.isOn) {return true;}
		
		this.animator.className = this.classOff;
		this.animator.style.opacity = "0";
		
		var thisOm = this;
		setTimeout(function() {
			thisOm.animator.style.display = "none";
		}, thisOm.aniTime);
		this.isOn = false;
	},
	
	clear: function() {
		this.animator.style.display = "none";
		this.animator.className = this.classOff;
		this.isOn = false;
	},
	
	toggle: function() {
		if(this.isOn) {
			this.hide();
		} else {
			this.show();
		}
	}
};


/****************************Ajax Functions****************************/

function ajaxGetPage(page, callback) {
	page = page || "";
	
	var request = new XMLHttpRequest();
	request.open("GET", page, true);	
	request.setRequestHeader("Content-Type", "application/html");
	//request.responseType = "text";
	request.onreadystatechange = function() {
		if(request.readyState === 4 && request.status===200) {
			var responseText = trim(this.response);
			callback(responseText);
		}
	};
	request.send(null);
}


function ajaxPost(postData, url, callback) {	
	var thisOm = this;
	var request = new XMLHttpRequest();
	request.open("POST", url);
	//request.responseType = "document";
	request.onreadystatechange = function() {
		if(request.readyState === 4 && request.status===200) {
			console.dir(this);
			var responseText = trim(this.response);
			if(typeof(callback) != "undefined") {
				callback(responseText);
			}
		}
	};
	request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	postData = "json_data=" + encodeURIComponent(JSON.stringify(postData));
	request.send(postData);
}

function ajaxForm(formId, callback) {
	var thisForm = document.getElementById(formId);
	if(!isset(thisForm)) {return false;}
		
	addEvent("submit", thisForm, function(e) {
		e.preventDefault();
		
		var url = thisForm.getAttribute("action");
		var inputs = thisForm.getElementsByTagName("input");
		var textareas = thisForm.getElementsByTagName("textarea");

		
		var inputsLength = inputs.length;
		
		var postData = {};
		for(var i = 0; i < inputsLength; i++) {
			var thisInput = inputs[i];
			
			var field = thisInput.getAttribute("name");
			//Check type
			if(thisInput.type === "checkbox") {
				var value = (thisInput.checked)? 1:0;
			} else {
				var value = thisInput.value;
			}
			
			postData[field] = value;
		}
		
		for(var i = 0; i < textareas.length; i++) {
			var thisInput = textareas[i];
			var field = thisInput.getAttribute("name");
			var value = thisInput.value;			
			postData[field] = value;
		}
		
		ajaxPost(postData, url, callback);
	});
}


/****************************Page Toggler****************************/
//Use for page tabs and loading
//Expects links to have data or href to find divs
//Divs finds all with matching key as data attribute
function PageToggler(keys, toggleDivs, customize) {
	
	//Check customizeable interface
	//classes are for display, attribute if for grabbing
	if(typeof(customize) === "undefined") {customize = false;}
	this.classOn  = customize.classOn  || "opacityOn";
	this.classOff = customize.classOff || "opacityOff";
	this.keyAttribute = customize.keyAttribute || "href";
	this.homeName = customize.homeName || "home";
	
	if(!isset(keys)) {return false;}
	if(!isset(toggleDivs)) {return false;}
	
	this.keysArray=[];
	
	//Check if keys is array or html object
	if(typeof(keys) === "object") {this.keys = keys.getElementsByTagName("a");} 
	else if(typeof(keys) === "array") {this.keys = keys;}
	else if(typeof(keys) === "string") {
		this.keys = document.getElementById(keys).getElementsByTagName("a");
	}
	else {console.log("keys not html or array");return false;}
	
	for(var i=0; i<this.keys.length; i++) {
		keyData = this.keys[i].getAttribute(this.keyAttribute);
		if(this.keyAttribute === "href") {
			keyData = keyData.split("#")[1];	
		}
		this.keysArray.push(keyData);
	}
	
	//Get toggle divs if it is brought as string
	if(typeof(toggleDivs) === "string") {
		toggleDivs = document.getElementById(toggleDivs);
	}
	//Check if dvis is array or html object
	if(typeof(toggleDivs) === "object") {
		toggleDivs = toggleDivs.getElementsByTagName("div");
	} 
	else if(typeof(toggleDivs) !== "array") {
		console.log("pages not html or array");
		return false;
	}
	
	pages = [];
	for(var i=0; i < toggleDivs.length; i++) {
		var page = toggleDivs[i];
		var pageData = page.getAttribute("data");
		if(in_array(pageData, this.keysArray)) {
			if(!pageData) {continue;}
			pages.push(page);
		}
	}
	
	this.toggleDivs = pages;
	this.init();
}

PageToggler.prototype = {
	init: function() {
		var t = this;
		this.clear();
		this.buttonEnable();
		if(this.keyAttribute !== "href") {
			this.setPage(this.homeName);
		} else {
			this.setPage(this.homeName);
			var hashChange = t.hrefLoad;
			hashChange = hashChange.bind(t);
		}
	},


	buttonEnable: function() {
		var thisToggler = this;
		
		for(var i=0; i<this.keys.length;i++) {
			this.keys[i].onclick = function(){
				thisToggler.show(this);
			};
		}
	},

	clear: function() {
		for(var i=0; i<this.toggleDivs.length;i++) {
			this.toggleDivs[i].removeAttribute("class", this.classOn);
			this.toggleDivs[i].setAttribute("class", this.classOff);
		}
		
		for(var i=0; i<this.keys.length;i++) {
			this.keys[i].className = "";
		}
	},
	
	show: function(keyA) {
		this.clear();
		var keyData = keyA.getAttribute(this.keyAttribute);
		if(this.keyAttribute == "href") {
			keyData = keyData.split("#")[1];
		}
		
		for(var i=0; i<this.toggleDivs.length;i++) {
			if(this.toggleDivs[i].getAttribute("data") === keyData) {
				this.toggleDivs[i].removeAttribute("class", this.classOff);
				this.toggleDivs[i].setAttribute("class", this.classOn);
			}
		}
		keyA.className = "selected";
	},
	
	setPage: function(keyData) {
		this.clear();
		for(var i=0; i<this.toggleDivs.length;i++) {
			if(this.toggleDivs[i].getAttribute("data") === keyData) {
				this.toggleDivs[i].removeAttribute("class", this.classOff);
				this.toggleDivs[i].setAttribute("class", this.classOn);
			}
		}
		for(var i = 0; i < this.keys.length; i++) {
			if(this.keyAttribute === "href") {
				keyRef = this.keys[i].href.split("#")[1];
			} else{
				keyRef = this.keys[i].getAttribute(this.keyAttribute);
			}
			
			if(keyRef === keyData) {this.keys[i].className = "selected";}
		}
	},
	
	addPage: function(toggleDiv) {
		this.toggleDivs.push(toggleDiv);
	},
	
	//Load a page by href
	hrefLoad: function(hash) {
		
		var hash = location.href.split("#")[1] || "";
		//Find key
		if(hash == "") {hash = this.homeName;}
		for(var i = 0; i < this.toggleDivs.length;i++) {
			var keyData = this.toggleDivs[i].getAttribute("data");
			if(keyData == hash) {
				this.setPage(keyData);
				return true;
			}
		}	
	}
};