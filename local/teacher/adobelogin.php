<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<body>
<?php
/**
 * Created by PhpStorm.
 * User: Mahesh
 * Date: 2/4/16
 * Time: 3:04 PM
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/my/lib.php');

//require_once('custom_adobe.php');

require_once($CFG->dirroot.'/local/student/custom_tele.php');

require_login();

global $USER;

if(user_has_role_assignment($USER->id,3)) {

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


	
echo "<center><h1>connecting please wait . . .</h1></center>";

if (isset($_GET)) {

    $reflink = $_GET['mlink'];
    $connect_name = $_GET['connect-name'];

//var_dump($_GET);
//exit(0);

    if ($reflink) {


        if (strcasecmp($connect_name, "adobeconnect") == 0) {


		$params = array(
		    "action" => "login",
		    "login" => $CFG->adobehost,
		    "password" => $CFG->hostpwd
		);

	echo httpPost($reflink, $params);


            /*$adobelogin=$CFG->adobehost;
	    $adobepassword=$CFG->hostpwd;
            echo '<form  method="post" action=' . $reflink . ' name="adobelogin" >';
            echo '<input type="hidden" name="action" value="login" />';
            echo '<input type="hidden" name="login" value=' . $adobelogin . ' />';
            echo '<input type="hidden" name="password" value='.$adobepassword.' />';
            echo '</form>';*/
        }

        if (strcasecmp($connect_name, "teleconnect") == 0) {

		$params = array(
		    "action" => "login",
		    "login" => $CFG->adobehost,
		    "password" => $CFG->hostpwd
		);

	echo httpPost($reflink, $params);

	    
            /*$adobelogin=$CFG->adobehost;
	    $adobepassword=$CFG->hostpwd;
		    echo '<form  method="post" action=' . $reflink . ' name="adobelogin" >';
		    echo '<input type="hidden" name="action" value="login" />';
		    echo '<input type="hidden" name="login" value=' . $adobelogin . ' />';
		    echo '<input type="hidden" name="password" value='.$adobepassword.' />';
		    echo '</form>';*/
	}

	
       
    }
}
else{
    echo '<h1>please close the current tab,refresh your page and try again </h1>';
}



}//end of teacher login check
?>
</body>
</html>
<script>
//alert('this is a test');
        //document.adobelogin.submit();
</script>








