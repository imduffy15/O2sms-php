<?php

/**
 * O2SMS sending library
 * 
 * This library is a tad hackish but it enables you to send text messages from
 * O2 Ireland's web interface using just PHP. 
 * 
 * @category Services
 * @package O2SMS
 * @author Ian Duffy <imduffy15@gmail.com>
 * @author Ben Chapman <hello@benchapman.ie>
 * @copyright 2012, Ian Duffy
 * @license GNU Lesser Public License v3.0
 * @version 0.1
 */
class O2SMS {
   /**
    * Define POST and GET for O2SMS::curl()
    *
    * @var int
    */
    const O2SMS_POST = 0;
    const O2SMS_GET = 1;
    
    /**
     * Sets the path that the cookies are stored in
     *
     * @var string
     */
    var $cookiePath	    = "cookies/o2.txt";

    /**
     * Sets the username and password for authentication then attempts to login
     *
     * @param string
     * @param string
     * @return O2SMS
     */
    public function __construct($username,$password) {
        $actionUrl = "https://www.o2online.ie/amserver/UI/Login";
        $actionUrl .= "?org=o2ext&goto=//www.o2online.ie/o2/my-o2/";
        $actionUrl .= "&IDToken1=$username&IDToken2=$password";
        
        $apiActionData = $this->apiAction(
            $actionUrl,
            self::O2SMS_POST,
            "http://o2online.ie"
        );

        if(strstr($apiActionData,'in-correct')) {
            throw new Exception("Login details incorrect");
        } elseif(strstr($apiActionData, 'isLoggedIn = false')) {
            throw new Exception("Login failed");
        } elseif(strstr($apiActionData, 'You are logged in as')) {
            return;
        } else {
            throw new Exception("Connection failed");
        }
    }

    public function send($number, $message) { //send a message
        if($this->balance() == 0) {
            throw new Exception("Out of webtexts");
        }
        
        $message = urlencode($message);
        $message = str_replace("%5C%27", "%27", $message);
        
        $apiActionData = $this->apiAction(
             "http://messaging.o2online.ie/smscenter_send.osp"
            ."?SID=_&FlagDLR=1&MsgContentID=-1"
            ."&SMSToNormailzed=&SMSText=$message&country=&SMSTo=$number",
             self::O2SMS_POST,
             "http://messaging.o2online.ie/o2om_smscenter_new.osp"
            ."?MsgContentID=-1&SID=_"
        );
        
        if(strstr($apiActionData, 'isSuccess : true')) {
            return 0;
        } else {
            throw new Exception("Sending failed");
        }
    }

    function balance() { //check balance
        $data = $this->apiAction(
            "http://messaging.o2online.ie/o2om_smscenter_new.osp?SID=_",
            self::O2SMS_GET
        );
        var_dump($data);die;
        if(strstr($data,"spn_WebtextFree")) {
            $balance = explode("<span id=\"spn_WebtextFree\">",$data);
            $balance=explode('</span>',$balance[1]);
            $balance = $balance[0];
            if(is_numeric($balance)) {
                return $balance;
            }
        } else {
            throw new Exception("Unspecified failure");
        }
    }

    function apiAction($url, $method = self::O2SMS_GET, $referer = "") {
        if($method === self::O2SMS_POST) {
            $extractData = explode("?", $url);
            $url = $extractData[0];
            $postData = $extractData[1];
        }
        
        $ch = curl_init($url);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, "$this->cookiePath");
        curl_setopt($ch, CURLOPT_COOKIEJAR, "$this->cookiePath");
        curl_setopt($ch, CURLOPT_USERAGENT, 
            'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1;'
           .' .NET CLR 2.0.50727; .NET CLR 1.1.4322)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        if($method === self::O2SMS_POST) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        } else {
            curl_setopt($ch, CURLOPT_POST, 0);
        }
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}

?>

