<?PHP

//Include PFsense functions
require_once("auth.inc");
require_once("functions.inc");
require_once("captiveportal.inc");
session_start();

//Do not cache pages
header("Expires: 0");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Connection: close");

//MySQL Configuration
$dbhost = '127.0.0.1';
$dbuser = 'radius';
$dbpass = 'radpass';
$dbname = 'radius';

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Telenor WIFI</title>
    <link href="captiveportal-bootstrap.min.css" rel="stylesheet">

  </head>
  <body>
  <div class="container">
  <img src="captiveportal-logo.jpg" class="img-responsive" alt="Responsive image">

<script src="captiveportal-jquery.min.js"></script>
<script src="captiveportal-bootstrap.min.js"></script>


<?php
		//PFSense functions to resolve mac
		$clientip = $_SERVER['REMOTE_ADDR'];
		if (!$clientip) {
        /* not good - bail out */
        log_error("Zone: {$cpzone} - Captive portal could not determine client's IP address.");
        $error_message = "An error occurred.  Please check the system logs for more information.";
        portal_reply_page($redirurl, "error", $errormsg);
        ob_flush();
        return;
		}
		$macfilter = !isset($cpcfg['nomacfilter']);
		$passthrumac = isset($cpcfg['passthrumacadd']);

			/* find MAC address for client */
			if ($macfilter || $passthrumac) {
				
				$tmpres = pfSense_ip_to_mac($clientip);
				if (!is_array($tmpres)) {
					/* unable to find MAC address - shouldn't happen! - bail out */
					captiveportal_logportalauth("unauthenticated","noclientmac",$clientip,"ERROR");
					echo "An error occurred.  Please check the system logs for more information.";
					log_error("Zone: {$cpzone} - Captive portal could not determine client's MAC address.  Disable MAC address filtering in captive portal if you do not need this functionality.");
					ob_flush();
					return;
				}
				$mac = $tmpres['macaddr'];
				unset($tmpres);
			}
		if(!$mac) die("Error! Could not identify your device");
	
		print "<pre>Logging out (mac: $mac)</pre>";
		
		$con = mysql_connect($dbhost, $dbuser, $dbpass,$dbname) or die("Database error:  connect failed ".mysql_error());

		//Clean old MAC addresses
		$clean = "DELETE FROM `radius`.`radcheck` where `username`= '$mac'";
		mysql_query($clean);
			
		mysql_close($con);

		
		print "<h2>You have been logged out</h2>";
		print "<p>Thansk for using Telenor WIFI</p><br/><br/>";		
		print "<p>Note! It can take up to 5 min before your connection are closed</p>";
		
		unset($_POST);
		session_unset(); 
		session_destroy(); 
?>
</div> 
</body>
</html>


