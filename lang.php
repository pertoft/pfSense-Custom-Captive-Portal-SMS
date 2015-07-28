<?PHP
session_start();

//Get browser languange
$browser_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

//Check if lang was passed as a variable

if($_POST['lang'] == "en")
{
	
	$language = "en";
	//Set language for the rest of the session
	$_SESSION['lang'] = $language;
}
else
{
	if(!isSet($_SESSION['lang']))
	{
		if( isSet($browser_lang) )
		{
			//If nothing specified, use browser language
			$language = $browser_lang;
			$_SESSION['lang'] = $language;
		}
		else
		{
			//Default to danish
			$language = "da";
			$_SESSION['lang'] = $language;
		}
	}
	else
	{
		 $language = $_SESSION['lang'];
	}
}
	
//Load language	
switch ($language) {
  case 'en':
   
  $language_file = 'captiveportal-lang.en.inc.php';
  break;
 
  default:
  //Default lang
  $language_file = 'captiveportal-lang.da.inc.php';
}
if(file_exists($language_file))
{
include_once $language_file;
}
else
{
	include_once 'captiveportal-lang.da.inc.php';
}

?>