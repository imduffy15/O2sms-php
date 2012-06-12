<?php
class O2SMS {
	var $cookiedir	=	"cookies";
	var $cookie		=	"o2.txt";

	public static function __construct($username,$password) {
		$url = "https://www.o2online.ie/amserver/UI/Login?org=o2ext&goto=//www.o2online.ie/o2/my-o2/&IDToken1=$username&IDToken2=$password";
		$data = $this->curlURL($url,"post","http://o2online.ie");
		if(strstr($data,'in-correct')) {
			throw new Exception("Login details incorrect")
		}
		elseif(strstr($data,'isLoggedIn = false')) {
			throw new Exception("Login failed")
         }
		elseif(strstr($data, 'You are logged in as')) {
			return $this;
		} else {
			throw new Exception("Connection failed");
		}
	}

	public function send($number, $message) {
		// Run this silently to make them think we've been redirected.
		$this->curlURL("http://messaging.o2online.ie/ssomanager.osp?APIID=AUTH-WEBSSO","get");
		$data = $this->curlURL("http://messaging.o2online.ie/o2om_smscenter_new.osp?SID=_","get");
		if(strstr($data,"spn_WebtextFree")) {
			$balance = explode("<span id=\"spn_WebtextFree\">",$data);
			$balance=explode('</span>',$balance[1]);
			$balance = $balance[0];
			if($balance == 0 ) {
				throw new Exception("Out of webtexts")
			}
		} else {
			throw new Exception("Unspecified failure")
		}
		$messagelength = ceil(strlen($message)/160);
		$message = urlencode($message);
		$message = str_replace("%5C%27", "%27", $message);
		$data = 	$this->curlURL("http://messaging.o2online.ie/smscenter_send.osp?SID=_&FlagDLR=1&MsgContentID=-1&SMSToNormailzed=&SMSText=$message&country=&SMSTo=$number","post","http://messaging.o2online.ie/o2om_smscenter_new.osp?MsgContentID=-1&SID=_");
		if(strstr($data, 'isSuccess : true')) {
			return 0;
		} else {
			throw new Exception("Sending failed")
		}
	}

	function curlURL($tUrl, $method = "GET", $ref = "") {
		$str="";
		if($method=="post") {
			$sp=explode("?",$tUrl);
			$tUrl=$sp[0];
			$str=$sp[1];
		}
		$ch = curl_init($tUrl);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_URL,$tUrl);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIEFILE, "$this->cookiedir/$this->cookie");
		curl_setopt($ch, CURLOPT_COOKIEJAR, "$this->cookiedir/$this->cookie");
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 2.0.50727; .NET CLR 1.1.4322)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_REFERER, $ref); 
		if($method=="post") { 
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
		} else {
			curl_setopt($ch, CURLOPT_POST, 0);
		} 
		$tRes = curl_exec($ch);
		curl_close($ch);
		return $tRes;
	}
}

?>

