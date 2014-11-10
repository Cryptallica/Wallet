<?php

class CheckCreator {
	
	
/****************************Check output METHODS****************************/
	
	//Load up
	function CheckCreator() {
	    require_once(CRYPTALLICA_DIR."lib".DS."phpqrcode".DS."qrlib.php");
	}
	
	//Create Qrcode using QRcode lib
	public function createQR($url) {
		$tmp_file = tempnam("/tmp", "CHECK");
		
		QRcode::png($url, $tmp_file); 
		if(is_file($tmp_file)) {
			return $tmp_file;
		} else {
			return false;
		}
	}
	
    //Create check for public address used for requesting
	public function createRequestCheck($url='', $coin='', $address='', $amount='', $label='', $message='') {
		$qr = $this->createQR($url);
		if(!$qr) {
			echo "Failed to create QR";
			return false;
		}
		//Create qr image resource
		$qrim = imagecreatefrompng($qr);
		
		//Make check base for putting text and other stuff on
		$check = $this->createCheckBase($qrim, $coin);
		
		//Send coin
		$text = $this->createImageText("Send ".ucfirst($coin),
			18/* 20, 250, 50,  */
		);
		$text = imagerotate($text, 90, 0);

		//Create address text
		$address_im = $this->createImageText($address, 11, 320, 30, [255,255,255]);

		//Check and amount label
		$amount_im = $this->createImageText("Amount ".$amount, 12, 250, 25, [200,200,200]);
		$amount_im = imagerotate($amount_im, 90, 0);
		
		if(!empty($label)) {
			$label_im = $this->createImageText($label, 12, 150, 25, [200,200,200]);
			$label_im = imagerotate($label_im, 90, 0);
		}

		//Create check front	
		$check = $this->imagePlace($check, $text, 0, 25);
		$check = $this->imagePlace($check, $amount_im, 40, 25);
		$check = $this->imagePlace($check, $address_im, 7, 25);
		
		//Back
		$check = $this->imagePlace($check, $qrim, 455, 490);
		
		//Optionals
		if(isset($label_im)) {$check = $this->imagePlace($check, $label_im, 60, 125);}
		
		return $check;
	}
	
	//Create check for importing keys and coins
	public function createSendCheck($coin, $amount, $pub, $priv) {
		
		//Create public key qr
		$qrpub = $this->createQR($pub);
		if(!$qrpub) {
			echo "Failed to create QR";
			return false;
		}
		
		//Create private key qr
		$qrpriv = $this->createQR($priv);
		if(!$qrpriv) {
			echo "Failed to create QR";
			return false;
		}
		
		//Create qr image resource
		$qrimpub = imagecreatefrompng($qrpub);
		$qrimpriv = imagecreatefrompng($qrpriv);
		
		//Make check base and use public key qr
		$check = $this->createCheckBase($qrimpub, $coin);
		
		//Send coin
		$text = $this->createImageText($coin."s"
			/* 20, 250, 50,  */
		);
		$text = imagerotate($text, 90, 0);

		//Create address text
		$address_im = $this->createImageText($pub, 11, 320, 30, [255,255,255]);

		//Check and amount label
		$amount_im = $this->createImageText("Amount ".$amount, 12, 250, 25, [200,200,200]);
		$amount_im = imagerotate($amount_im, 90, 0);
		
		//Create public key label
		$label_im = $this->createImageText("Public Key", 12, 150, 25, [200,200,200]);
		$label_im = imagerotate($label_im, 90, 0);
        
        //Back Address box
        $privatekeybox = $this->createRectangle(340, 260);
        
        
        //Back Address
        $address_im = $this->createImageText($priv, 11, 320, 30, [255,255,255]);

		//Create check front	
		$check = $this->imagePlace($check, $text, 0, 25);
		$check = $this->imagePlace($check, $amount_im, 40, 25);
		$check = $this->imagePlace($check, $address_im, 7, 25);
		
		//Back use private key
		$check = $this->imagePlace($check, $qrimpriv, 455, 490);
				
		return $check;
	}
	
	//Create check base using 
	private function createCheckBase($qrim, $coin) {
		//Create check image resources
		$background = $this->cropCheck();
		$black_box = $this->createRectangle(340, 260);
		$border = $this->createRectangle(2, 650);		
		
		//Create currency
		$currency = imagecreatefrompng(CRYPT_ICON.$coin.".png");
		imagealphablending($currency, false);
		imagesavealpha($currency, true);
		$currency = $this->imageResize($currency);
		
		//Create check front
		$check = $this->imagePlace($background, $black_box);
		$check = $this->imagePlace($check, $border, 339, 0);
		$check = $this->imagePlace($check, $qrim, 120, 80);
		$check = $this->imagePlace($check, $currency, 125, 490);
		
		return $check;
	}
	
	private function cropCheck() {
				
		// Get new dimensions
		list($width, $height) = getimagesize(CRYPTALLICA_CHECK);
		
		$check = imagecreatefromjpeg(CRYPTALLICA_CHECK);
		$to_crop_array = array('x' =>0 , 'y' => 0, 'width' => 680, 'height'=> 650);
		$new_image = imagecrop($check, $to_crop_array);			
		return $new_image;
	}
	
/****************************IMAGE METHODS****************************/	

	//Flip image
	public function imageFlip( $imgsrc, $mode ) {
	    $width                        =    imagesx ( $imgsrc );
	    $height                       =    imagesy ( $imgsrc );
	
	    $src_x                        =    0;
	    $src_y                        =    0;
	    $src_width                    =    $width;
	    $src_height                   =    $height;
	
	    switch ( $mode )
	    {
	
	        case '1': //vertical
	            $src_y                =    $height -1;
	            $src_height           =    -$height;
	        break;
	
	        case '2': //horizontal
	            $src_x                =    $width -1;
	            $src_width            =    -$width;
	        break;
	
	        case '3': //both
	            $src_x                =    $width -1;
	            $src_y                =    $height -1;
	            $src_width            =    -$width;
	            $src_height           =    -$height;
	        break;
	
	        default:
	            return $imgsrc;
	
	    }
	
	    $imgdest                    =    imagecreatetruecolor ( $width, $height );
	
	    if ( imagecopyresampled ( $imgdest, $imgsrc, 0, 0, $src_x, $src_y , $width, $height, $src_width, $src_height ) )
	    {
	        return $imgdest;
	    }
	
	    return $imgsrc;
	}
	
	public function imagePlace($dest, $place_image, $x=0, $y=20) {
		
		$width = imagesx ( $place_image );
		$height = imagesy ( $place_image );
		
		imagecopy($dest, $place_image, $x, $y, 0, 0, $width, $height);
		return $dest;
	}
	
	public function createImageText($text="", $size=20, $width=250, $height=50, $color=[255,255,255], $back=false) {
		$im = imagecreatetruecolor($width, $height);
		imagealphablending($im, false);
		imagesavealpha($im, true);

		// Create some colors
		$fontcolor = imagecolorallocate($im, $color[0],$color[1],$color[2]);
		
		if(is_array($back)) {
			$background = imagecolorallocate($im, $back[0],$back[1],$back[2]);
			imagefilledrectangle($im, 0, 0, ($width-1), ($height-1), $background);
		}
		
		// The text to draw
		$text = strval($text);
		
		// Add the text
		imagealphablending($im, false);
		imagesavealpha($im, true);
		imagettftext($im, $size, 0, 5, ($height/1.5), $fontcolor, C_OPENSANS, $text);
		return $im;
	}
	
	public function createRectangle($width=350, $height=200, $rgb=[0,0,0]) {
		
		$im = imagecreatetruecolor($width, $height);

		// Create some colors
		$background = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
		
		//Do 
		imagefilledrectangle($im, 0, 0, ($width-1), ($height-1), $background);
		return $im;
	}
	
	public function imageResize($im, $width=100, $height=100) {
		
		$im_width = imagesx($im);
		$im_height = imagesy($im);
		
		// Resample
		$image_p = imagecreatetruecolor($width, $height);
		imagealphablending($image_p, false);
		imagesavealpha($image_p, true);
		
		imagecopyresampled($image_p, $im, 0, 0, 0, 0, $width, $height, $im_width, $im_height);
		imagealphablending($image_p, false);
		imagesavealpha($image_p, true);
		return $image_p;
	}
}