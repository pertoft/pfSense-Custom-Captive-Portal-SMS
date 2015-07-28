<?PHP
$debug=false;

session_start();

//Include PFSense functions
require_once("auth.inc");
require_once("functions.inc");
require_once("captiveportal.inc");


//Do not cache pages
header("Expires: 0");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Connection: close");
header('Cache-control: private'); // IE 6 FIX

require_once("captiveportal-lang.php");

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
  <a href="captiveportal-eapsim.php">
    <img src="captiveportal-logo.jpg" class="img-responsive" alt="Responsive image">
  </a>
  <form action="#" method="post" name="Language">
	<input type="hidden" name="lang" value="en">
	<p class="text-right">  <button class="btn btn-link"><img src="captiveportal-Flag_UK.png"> Click for English version</button></p>
  </form>
	
<script src="captiveportal-jquery.min.js"></script>
<script src="captiveportal-bootstrap.min.js"></script>
<script>
$( document ).ready(function() {
    $("#alert").hide();

	$("#formMobile").submit(function(e) {
	//e.preventDefault();
    if(!$('input[type=checkbox]:checked').length) {
	    $("#alert").show();
	 //stop the form from submitting
        return false;
    }
    return true;
	});

});
</script>

	<form id="formMobile" name="formMobile" action="captiveportal-portal-smscode.php" method="POST" role="form" data-toggle="validator">
		<input name="redirurl" type="hidden" value="$PORTAL_REDIRURL$">
		<div class="panel panel-default">
			<div class="panel-heading"><?PHP echo $lang['HEADER_TITLE']; ?></div>
			  <div class="panel-body">
				
  			    <p><?PHP echo $lang['PAGE1_TEXT1']; ?> </p>
				<div class="input-group">
					<span class="input-group-addon" id="icon1"><?PHP echo $lang['MOBILE_NUMBER']; ?> </span>
					<input type="text" class="form-control" name="mobile"  placeholder="+45 12345678 " aria-describedby="icon1" required>
				</div>
				<div class="input-group">
					<span class="input-group-addon" id="icon2"><?PHP echo $lang['EMAIL_OPT']; ?></span>
					<input type="text" class="form-control" name="email"  placeholder="mail@telenor.dk" aria-describedby="icon2">
				</div>
				<div class="checkbox" required>
					<label>
						<input type="checkbox" id="checkbox"> <?PHP echo $lang['PAGE1_TEXT2']; ?> 
					</label>
				</div>
				<div class="alert alert-warning" role="alert" id="alert"><?PHP echo $lang['PAGE1_TEXT3']; ?></div>
			   <button type="submit" class="btn btn-default"><?PHP echo $lang['BTN_NEXT']; ?></button>
			   <br>
			   <br>			   
			   <p><?PHP echo $lang['PAGE1_TEXT4']; ?> </p>
		    </div>
		</div>
	</form>

</div> 
</body>
</html>

<?PHP



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
				
		print "<pre>MAC:". $mac."</pre>";