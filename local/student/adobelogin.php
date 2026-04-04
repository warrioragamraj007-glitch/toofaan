<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<style>
html {
  height: 100%;
}
body {
  min-height: 100%;
}
</style>
<html xmlns="http://www.w3.org/1999/xhtml">
<body oncontextmenu="return false;">
<?php
/**
 * Created by PhpStorm.
 * User: Mahesh
 * Date: 2/4/16
 * Time: 3:04 PM
 */

// require_once(dirname(__FILE__) . '/../config.php');
require_once('../../config.php');

//require_once('custom_adobe.php');

require_once('custom_tele.php');

require_login();

global $USER;

echo "<center><h1>connecting please wait . . .</h1></center>";

if (isset($_POST)) {


/* adobe autologin code start */


$adobecookies=array();

function curlResponseHeaderCallback($ch, $headerLine) {
    global $adobecookies;
    if (preg_match('/^Set-Cookie:\s*([^;]*)/mi', $headerLine, $cookie) == 1)
        $adobecookies[] = $cookie;
    return strlen($headerLine); // Needed by curl
}

function httpPost($url, $params)
{
    global $adobecookies;

    $postData = '';
    //create name value pairs seperated by &
    foreach ($params as $k => $v)
    {
        $postData .= $k . '=' . $v . '&';
    }
    $postData = rtrim($postData, '&');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADERFUNCTION, "curlResponseHeaderCallback");

    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_POST, count($postData));

    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

    $output = curl_exec($ch);

    if ($output === false)
    {
        throw new \RuntimeException("curl error " . curl_errno($ch) . ": " . curl_error($ch));
        // https://bugs.php.net/bug.php?id=76268
    }
    //var_dump($output);
    curl_close($ch);

	$session=explode('=',$adobecookies[0][1])[1];
	header("Location: {$url}?session={$session}");
	exit();


}

/* adobe autologin code end */

    $reflink = $_POST['mlink'];
    $connect_name = $_POST['connect-name'];

    if ($reflink) {

        $userobj = get_complete_user_data(id, $USER->id);


        if (strcasecmp($connect_name, "adobeconnect") == 0) {

	    $adobelogin=$userobj->profile['adobelogin'];
            $adobepassword=$userobj->profile['adobepassword'];

		$params = array(
		    "action" => "login",
		    "login" => $adobelogin,
		    "password" => $adobepassword
		);

	    echo httpPost($reflink, $params);


        }
        if (strcasecmp($connect_name, "teleconnect") == 0) {


	    $adobelogin=$userobj->profile['adobelogin'];
            $adobepassword=$userobj->profile['adobepassword'];

		$params = array(
		    "action" => "login",
		    "login" => $adobelogin,
		    "password" => $adobepassword
		);
        // var_dump($params);
        // exit(0);
	    echo httpPost($reflink, $params);


		}
	       if (strcasecmp($connect_name, "adobevideos") == 0) {

		   if(!empty($adobelogin)&&!empty($adobepassword)){
		    $adobelogin=$userobj->profile['adobelogin'];
		    $adobepassword=$userobj->profile['adobepassword'];
		    }else{
			$adobelogin='fs@teleuniv.com';
		        $adobepassword='Mahesh321$';
		    }

			$params = array(
			    "action" => "login",
			    "login" => $adobelogin,
			    "password" => $adobepassword
			);

		    echo httpPost($reflink, $params);



		}
    }//if (strcasecmp($connect_name, "teleconnect") == 0)
}
else{
    echo '<h1>please close the current tab,refresh your page and try again </h1>';
}
?>
</body>
</html>
<script>
        //document.adobelogin.submit();

</script>

