/****************************Cryptallica Qr Main object****************************/
//Cryptallica QR reader setups canvas and file reader
var cryptallicaQR = {
	//Canvas
	 gCanvas: null,
	 gCtx: null,

	 c:0,
	 stype:0,
	 gUM:false,
	 webkit:false,
	 moz:false,
	 v:null
};

//HTML
cryptallicaQR.cameraWrap = document.getElementById("cryptCameraWrap");
cryptallicaQR.readermessage = document.getElementById("cryptScannerMessage");
cryptallicaQR.scannermessage = document.getElementById("cryptScanner");
cryptallicaQR.window = document.getElementById("cryptCameraWindow");

//Main loader will setup 
cryptallicaQR.load = function() {
	//Setup page toggler
	this.toggler = new PageToggler("cryptQRKeyData", "cryptQrContent", {
		homeName: "camera",
		keyAttribute: "data"
	});

	//Check all requirements
	if(!isCanvasSupported() && !window.File && !window.FileReader) {
		this.readermessage.innerHTML = "Browser does not support canvas, file, or filereader";
		return false;
	}
	qrcode.callback = this.read.bind(this);
	this.initCanvas(500, 350);
	this.initVideo();
	this.initUploader();
};

//QR will load on load
addEvent("load", window, function() {
	cryptallicaQR.load();
});

/****************************Video streamer and reader****************************/
//Loads appends and initials canvas
cryptallicaQR.initCanvas = function(w,h) {
    this.gCanvas = document.createElement("canvas");
    this.gCanvas.style.width = w + "px";
    this.gCanvas.style.height = h + "px";
    this.gCanvas.width = w;
    this.gCanvas.height = h;
    this.gCtx = this.gCanvas.getContext("2d");
    this.gCtx.clearRect(0, 0, w, h);
    this.window.appendChild(this.gCanvas);
};

//Create Video element for qr scan output
cryptallicaQR.initVideo = function() {
	this.v = document.createElement("video");
	this.v.autoplay = true;
	this.window.appendChild(this.v);
};

//Set webcam capture functions
cryptallicaQR.setwebcam = function() {
	var crypt = this;
	crypt.scannermessage.innerHTML="Scanning";
    if(crypt.stype==1) {
        setTimeout(function() {
        	crypt.captureToCanvas();
        }, 250);    
        return;
    }

    //Setup Video streaming
    var n=navigator;
    var vOpts = {video: true, audio:false};
    
    var success = crypt.success.bind(this);
    var error = crypt.error.bind(this);
    
    //Check each browser
    if(n.getUserMedia) {
        n.getUserMedia(vOpts, success, error);
    }
    //Safari chrome
    else if(n.webkitGetUserMedia) {
        this.webkit=true;
        n.webkitGetUserMedia(vOpts, success, error);
    }
    //Firefox, gecko
    else if(n.mozGetUserMedia) {
        this.moz=true;
        n.mozGetUserMedia(vOpts, success, error);
    }

    this.stype=1;
    setTimeout(function() {
    	crypt.captureToCanvas();
    }, 250);
};

cryptallicaQR.captureToCanvas = function() {
	var captureToCanvas = this.captureToCanvas.bind(this);
    if(this.stype!==1) {return;}
    if(this.gUM) {
        try{
            this.gCtx.drawImage(this.v,0,0);
            try{
                qrcode.decode(this.gCanvas);
                this.addRescan();
            }
            catch(e){       
                this.readermessage.innerHTML = e;
                setTimeout(captureToCanvas, 250);
            };
        }
        catch(e){       
            this.readermessage.innerHTML = e;
            setTimeout(captureToCanvas, 250);
        };
    }
};

//Read outputed qrcode from > qrcode
cryptallicaQR.read = function(a) {
	var html='';
	//Check link
    if(/^http[s]?:\/\//.exec(a)) {
        html+="<a target='_blank' href='"+a+"'>"+a+"</a>";
    }
    
    html+= htmlEntities(a);
    cryptallicaQR.readermessage.innerHTML=html;
};	

//Load video element with stream data
cryptallicaQR.success = function(stream) {
	var crypt = this;
    if(crypt.webkit) {
        crypt.v.src = window.webkitURL.createObjectURL(stream);
    }
    else if(crypt.moz){
        crypt.v.mozSrcObject = stream;
        crypt.v.play();
    }
    else {
        crypt.v.src = stream;
    }
    
    this.stream = stream;
    
    crypt.gUM=true;
    setTimeout(function() {
    	crypt.captureToCanvas();
    }, 250);
};
	
//Stop proccesses on error		
cryptallicaQR.error = function(error) {
    this.gUM=false;
    return;
};

cryptallicaQR.addRescan = function() {
	var crypt = this;
	crypt.scannermessage.innerHTML = "Scan Complete";

	var rescan = document.createElement("a");
	rescan.innerHTML = "<div id='cryptQRRescan'><img src='assets/use/reload.png'></div>";
	rescan.onclick = function() {
		crypt.setwebcam();
		crypt.scannermessage.innerHTML = "Scanning";
		removeSelf(this);
	};
	this.cameraWrap.appendChild(rescan);
	this.stopScanner();
};

cryptallicaQR.stopScanner = function() {
	if(this.stream) {
		this.stream.stop();
		this.v.src = null;
		this.stype = 0;
	}
};

/****************************Image Uploader****************************/
cryptallicaQR.initUploader = function() {
    var qrfile = this.qrfile = document.getElementById("cryptQRFile");
    addEvent("dragenter", qrfile, dragstop);
    addEvent("dragover", qrfile, dragstop);
    
    var crypt = this;
    addEvent("drop", qrfile, function(e) {
	    crypt.loadDroppedFiles(e);
    });
};


/****************************File Reader****************************/
cryptallicaQR.loadDroppedFiles = function(e) {
	dragstop(e);
	
	var dt = e.dataTransfer;
	var files = dt.files;
	//Multipe Files
	
	if(files.length>0) {
		this.handleFiles(files);
	}
	//Data url
	else if(dt.getData('URL')) {
		qrcode.decode(dt.getData('URL'));
	}
};

cryptallicaQR.handleFiles = function(f) {
	var o=[];
	var crypt = this;
	for(var i =0;i<f.length;i++) {
        var reader = new FileReader();
        reader.onload = (function(theFile) {
	        return function(e) {
	            crypt.gCtx.clearRect(0, 0, crypt.gCanvas.width, crypt.gCanvas.height);
	            console.log(e.target.result);
				qrcode.decode(e.target.result);
	        };
        })(f[i]);
        reader.readAsDataURL(f[i]);	
    }
};


/****************************Utils****************************/
function isCanvasSupported(){
  var elem = document.createElement('canvas');
  return !!(elem.getContext && elem.getContext('2d'));
}

function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function dragstop(e) {
	e.stopPropagation();
	e.preventDefault();
}

function removeSelf(e) {
	e.parentNode.removeChild(e);
}