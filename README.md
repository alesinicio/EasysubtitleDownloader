# EasysubtitleDownloader
PHP implementation for the `easysubtitles.com` REST API. 

Downloads subtitles for video files given preferred language.

Can be used in an object-oriented fashion or in procedural format.

The file 'tests.php' runs unit-tests for most common situations. Feel free to contribute.

##Examples
###Object-oriented style
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

###Procedural styles
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