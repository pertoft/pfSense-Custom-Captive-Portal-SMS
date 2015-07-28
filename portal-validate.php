<?PHP
//Include PFsense functions
require_once("auth.inc");
require_once("functions.inc");
require_once("captiveportal.inc");


//We use service session cookies to pass the password hash
//We use service session cookies to pass the password hash
session_start();

//Do not cache pages
header("Expires: 0");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Connection: close");
header('Cache-control: private'); // IE 6 FIX

require_once("captiveportal-lang.php");

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
    <title><?PHP echo $lang['PAGE_TITLE']; ?></title>
    <link href="captiveportal-bootstrap.min.css" rel="stylesheet">
  </head>
  <body>
  <div class="container">
  <img src="captiveportal-logo.jpg" class="img-responsive" alt="Responsive image">

<script src="captiveportal-jquery.min.js"></script>
<script src="captiveportal-bootstrap.min.js"></script>


<?php
// Read password salt form file
function getSalt()
{
$file = fopen("captiveportal-salt.inc","r");
if(!$file) die("No SALT");
$salt = fgets($file);
return $salt;
}

//Check if passcode is correct
$salt = getSalt();
 	if(crypt($_POST['passcode'],$salt) == $_SESSION['portal-hash'])
	{
	
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
			
		if(!$mac) die($lang['PAGE3_ERROR1']);
		
		$mobile = $_SESSION['portal-mobile'];
		$email = $_SESSION['portal-email'];
		
			
		$con = mysql_connect($dbhost, $dbuser, $dbpass,$dbname) or die("mysql connect failed: ".mysql_error());

		//Clean old MAC addresses
		$clean = "DELETE FROM `radius`.`radcheck` where `username`= '$mac'";
		mysql_query($clean);
	
		//Log historical data
		$sql = 'INSERT INTO `radius`.`radcheck_log` (`id`,`username`,`attribute`,`op`,`value`,`mobile`,`ip`,`email`) VALUES (NULL , \''.$mac.'\', \'Cleartext-Password\', \':=\', \'password\',\''.$mobile.'\',\''.$clientip.'\',\''.$email.'\');';
		$sql_result = mysql_query($sql);
		
		$sql = 'INSERT INTO `radius`.`radcheck` (`id`,`username`,`attribute`,`op`,`value`,`mobile`,`ip`,`email`) VALUES (NULL , \''.$mac.'\', \'Cleartext-Password\', \':=\', \'password\',\''.$mobile.'\',\''.$clientip.'\',\''.$email.'\');';
		$sql_result = mysql_query($sql);
			
		if(! $sql_result){
          		die('ERROR! Could not create user: ' . mysql_error());
     		}
		mysql_close($con);

		print "<h2>".$lang['PAGE3_WELCOME']."</h2>";
		print "<p>".$lang['PAGE3_REDIR']."</p>";		
		print "<div id=\"account\"></div>";
		
?>		
		<script type="text/javascript">
			window.onload = function()
			{
				countDown('account', 'Du viderestilles til telenor.dk...', 10);
			}
 
			function countDown(elID, output, seconds)
			{
				document.getElementById(elID).innerHTML = (seconds==0) ? output : '<h2>' + seconds +'</h2>'; 
					if(seconds==0) { 
					  window.location.replace("http://www.telenor.dk");
						return; 
					}
				setTimeout("countDown('"+elID+"', '"+output+"', "+(seconds-1)+")", 1000);
			}
		</script>
		
<?PHP
	
		unset($_POST);
		session_unset(); 
		session_destroy(); 
		
	}
	else
	{	
		print "<div class=\"alert alert-danger\" role=\"alert\">\n";
		print "<span class=\"glyphicon glyphicon-exclamation-sign\" aria-hidden=\"true\"></span>";
		print "<span class=\"sr-only\">".$lang['ERROR']."</span>\n";
		print $lang['PAGE3_ERROR2']." ".$to."\n";
		print "<a href=\"captiveportal-portal.php\">".$lang['RETRY']."</a>";
		print "</div>";
		die;

	} 
?>
</div> 
</body>
</html>
