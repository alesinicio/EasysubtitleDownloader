<?php

use alesinicio\Easysubtitle\EasysubtitleDownloader;

require './vendor/alesinicio/Easysubtitle/EasysubtitleDownloader.php';

$sub = new EasysubtitleDownloader();

//THE WHOLE THING THROWS EXCEPTIONS ON ERRORS, SO KEEP EVERYTHING IN A TRY/CATCH BLOCK.
try {
	//SETS THE PATH TO THE VIDEO FILE
	$sub->setVideofile(__DIR__.'/tests/alesinicio/Easysubtitle/justified.mp4');
	
	//SETS THE USER AGENT FOR THE API CALL -- USE YOUR OWN PROJECT DATA
	$sub->setUserAgent('SubDB/1.0 (YOUR_NAME/VERSION; PROJECT_URL)');
	
	//SETS WHAT ARE YOUR PREFERRED LANGUAGES FOR THE SUBTITLE -- EITHER AN ARRAY OR COMMA-SEPARATED VALUES
	$sub->setPreferredLanguages('pt,en');
	
	//CALLS THE API AND GET WHAT SUBTITLES ARE AVAILABLE FOR THAT VIDEO FILE
	$strSubtitlesAvailable = $sub->getAvailableSubtitles();
	
	//GIVEN THE AVAILABLE SUBTITLES, GETS THE BEST CHOICE BASED ON YOUR PREFERENCES
	$bestAvailableSubtitle = $sub->searchForPreferredSubtitle($strSubtitlesAvailable);
	
	//DOWNLOADS THE SUBTITLE TO THE SAME FOLDER OF THE VIDEO FILE
	$sub->downloadSubtitle($bestAvailableSubtitle);
	
	echo "Subtitle in language `{$bestAvailableSubtitle}` downloaded!";
} catch (Exception $e) {
	die($e->getMessage());
}