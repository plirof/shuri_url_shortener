<?php
// eg http://shuri.dimotika.tk/index.php?BelNDA
// v210405b -- http://shuri.xxxx.tk/index.php?list=3   : shows a list with ALL created links
// v210405a -- added password

$mypassword="1234"; //added by jon 210405 - enter password to store a URL
require __DIR__.'/vendor/autoload.php';

use Endroid\QrCode\QrCode;

function smallHash($text)
{
    $t = rtrim(base64_encode(hash('crc32', $text, true)), '=');

    return strtr($t, '+/', '-_');
}


	//++++++++++++++ADDED BY JON 2120405		+++++++++++++++++++++
	if (!empty($_REQUEST['list'])) { //index.php?list 
	        $hashfolderpath = './db/';
             $hashfilepath = $hashfolderpath;

            $findfiles = glob($hashfilepath."*/*");
            print_r($findfiles);
            if (!empty($findfiles)) {
                foreach($findfiles as $fullfilepath){
               // $fullfilepath = current($findfiles);
                echo "<BR> link: fullfilepath =$fullfilepath , URL=".file_get_contents($fullfilepath);
                //header('Location:'.file_get_contents($fullfilepath));
                }
                return 0;
            }
	} //END of if (!empty($_REQUEST['url'])) 
    //---------------ADDED BY JON 2120405	---------------------
    
if (!empty($_REQUEST['url'])) { //if we have param URL then we should try and register a new ShortUrl
    $url = $_REQUEST['url'];
    $mypass = $_REQUEST['mypass'];
    
    if($mypass!=$mypassword) {echo "<h2>WRONG PASS</h2>"; exit ("wrong password");    } //added by jon 210405 - enter password to store a URL
    
    if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
        $url = 'http://'.$url;
    }

    $urlhash = smallHash($url);

    $hashfolder = substr($urlhash, 0, 2);
    $hashfile = substr($urlhash, 2);

    $hashfolderpath = './db/'.$hashfolder;
    $hashfilepath = $hashfolderpath.'/'.$hashfile;

    mkdir($hashfolderpath, 0700, true);

    file_put_contents($hashfilepath, $url);

    $shortUrl = $_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'?'.$urlhash;

    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
        $shortUrl = 'https://'.$shortUrl;
    } else {
        $shortUrl = 'http://'.$shortUrl;
    }

    $qrcode = (new QrCode())->setText($shortUrl);

// JON ---write alias++++++++++++++++++++
    if (!empty($_REQUEST['alias'])) {
        file_put_contents('./db/alias/'.$_REQUEST['alias'], $url); //
    }
// JON ---write alias----------------------------


    $content = '<a href="'.$shortUrl.'">'.$shortUrl.'</a><br>'
	    .'<img src="data:'.$qrcode->getContentType().';base64,'.base64_encode($qrcode->get()).'">';
} elseif (!empty($_REQUEST)) {
    $urlhash = key($_REQUEST);

    //JOn try to check for alias ++++++++++++++++++++++++++
    
    //search alias folder for the urlhash
    $findaliasfile = glob('./db/alias/'.$urlhash);
    echo "<hr>".file_get_contents($findaliasfile);
    //JOn try to check for alias --------------------------

    $hashfolder = substr($urlhash, 0, 2);
    $hashfile = substr($urlhash, 2);

    $hashfolderpath = './db/'.$hashfolder;
    $hashfilepath = $hashfolderpath.'/'.$hashfile;

    $findfiles = glob($hashfilepath);

    if (!empty($findfiles)) {
        $fullfilepath = current($findfiles);

        header('Location:'.file_get_contents($fullfilepath));
        return 0;
    }

    $content = 'No link match this identifier.';
} else {
    $content = '<form method="get">
				Enter your URL: <input type="text" name="url">
				<BR>Enter custom alias (not working yet): <input type="text" name="alias">
				<BR>Enter your pass: <input type="password" name="mypass">
				<input type="submit" value="Submit">
			</form>';
			
			
}


//actual page below
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Shuri</title>
	</head>
	<body>
		<div id="content">
			<?= $content ?>
		</div>
	</body>
</html>
