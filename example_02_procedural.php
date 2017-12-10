<?php

use alesinicio\Easysubtitle\EasysubtitleDownloader;

require './vendor/alesinicio/Easysubtitle/EasysubtitleDownloader.php';

//THE WHOLE THING THROWS EXCEPTIONS ON ERRORS, SO KEEP EVERYTHING IN A TRY/CATCH BLOCK.
try {
	//SETS THE PATH TO THE VIDEO FILE
	$strVideofile		= __DIR__.'/tests/alesinicio/Easysubtitle/justified.mp4';
	
	//SETS THE USER AGENT FOR THE API CALL -- USE YOUR OWN PROJECT DATA
	$strUserAgent		= 'SubDB/1.0 (YOUR_NAME/VERSION; PROJECT_URL)';
	
	//SETS WHAT ARE YOUR PREFERRED LANGUAGES FOR THE SUBTITLE -- EITHER AN ARRAY OR COMMA-SEPARATED VALUES
	$preferredLanguages	= ['pt','en'];

	//TRIES TO DOWNLOAD THE SUBTITLE
	$result = EasysubtitleDownloader::downloadSubtitleForVideoFile($strUserAgent, $strVideofile, $preferredLanguages);

	//PARSES THE RESULT OF THE DOWNLOAD ATTEMPT
	if ($result['code'] == 1) {
		echo "Subtitle downloaded!";
	} else {
		die('Error > '.$result['message']);
	}
} catch (Exception $e) {
	die($e->getMessage());
}