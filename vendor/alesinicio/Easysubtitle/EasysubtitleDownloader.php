<?php
namespace alesinicio\Easysubtitle;

use Exception;

/**
 * Downloads subtitles from easysubtitle.com (thesubdb.com).
 *
 * @author alexandre.sinicio
 * @version 1.000.000
 */
class EasysubtitleDownloader {
	private $preferredLanguagues = [];
	private $userAgent;
	private $apiURL;
	private $videofile;
	
	/**
	 * Creates object and sets the API URL.
	 *
	 * @param string $strAPIURL Full URL for the API ('http://api.thesubdb.com/...')
	 */
	public function __construct($strAPIURL='http://sandbox.thesubdb.com/') {
		$this->apiURL = rtrim($strAPIURL, '/').'/';
	}
	/**
	 * Sets the user agent for the API calls.
	 *
	 * @param string $strUserAgent User agent
	 */
	public function setUserAgent($strUserAgent='SubDB/1.0 (YOUR_NAME/VERSION; PROJECT_URL)') {
		$this->userAgent = $strUserAgent;
	}
	/**
	 * Sets the preferred languages for download subtitles.
	 *
	 * @param string|array $preferredLanguages Comma-separated languages or array with languages
	 */
	public function setPreferredLanguages($preferredLanguages) {
		if (!is_array($preferredLanguages)) {
			$preferredLanguages = explode(',', $preferredLanguages);
		}
		$this->preferredLanguagues = $preferredLanguages;
	}
	/**
	 * Sets the video file path.
	 *
	 * @param string $strVideofile Path to the video file
	 */
	public function setVideofile($strVideofile) {
		$this->videofile = str_replace('\\', '/', $strVideofile);
	}
	/**
	 * Selects, among the subtitles available, the preferred one. Returns null if nothing good is available.
	 *
	 * @param string $strSubtitlesAvailable List of available subtitles from thesubdb.com
	 * @return string|NULL The preferred subtitle or null if none available
	 */
	public function searchForPreferredSubtitle($strSubtitlesAvailable) {
		$arrSubtitles = explode(',', $strSubtitlesAvailable);
		foreach($this->preferredLanguagues as $language) {
			if (in_array($language, $arrSubtitles)) return $language;
		}
		return null;
	}
	/**
	 * Gets MD5 hash of video file.
	 *
	 * @throws Exception
	 * @return string MD5 hash
	 */
	public function getVideofileMD5Hash() {
		if (!file_exists($this->videofile)) throw new Exception('File not found - '.$this->videofile);
		$handle		= fopen($this->videofile, 'rb');
		$dataStart	= fread($handle, 64*1024);
		fseek($handle, -64*1024, SEEK_END);
		$dataEnd	= fread($handle, 64*1024);
		fclose($handle);
		
		$dataMD5 = $dataStart . $dataEnd;
		return md5($dataMD5);
	}
	/**
	 * Downloads subtitle of the set video file and saves it to disk.
	 *
	 * @param string $strLanguage Language identifier of the subtitle to be downloaded
	 * @param string $strSrtSavePath Fully qualified path name or null to save in the same folder as the video file
	 * @return string Subtitle in plain text
	 */
	public function downloadSubtitle($strLanguage, $strSrtSavePath=null) {
		$apiResponse = $this->getDataFromAPIServer('download', $this->getVideofileMD5Hash(), ['language'=>$strLanguage]);
		if ($apiResponse === null) throw new Exception('Error downloading subtitle.');
		
		$srtPath = ($strSrtSavePath !== null ? $strSrtSavePath : substr($this->videofile, 0, -3).'srt');

		file_put_contents($srtPath, $apiResponse);
		return $apiResponse;
	}
	/**
	 * Gets available subtitles for the set video file.
	 *
	 * @return string Available languages separated by comma
	 */
	public function getAvailableSubtitles() {
		if ($this->videofile === null) throw new Exception('No video file set.');
		return $this->getDataFromAPIServer('search', $this->getVideofileMD5Hash());
	}
	/**
	 * One-call wrapper to download subtitle of file.
	 *
	 * @param string $strUserAgent Sets the user agent for the API call
	 * @param string $strVideofile Path to the video file
	 * @param string|array $preferredLanguages Comma-separated languages or array with languages
	 * @param string $saveFolder Folder where subtitles should be saved, or null to save on the same folder as the video file
	 * @param string $strAPIURL URL of the API, if not the default
	 * @return array Array with format ['code'=>0(ERROR)|1(SUCCESS), 'message'=>ERROR_MESSAGE|PLAIN_TEXT_SUBTITLE]
	 */
	public static function downloadSubtitleForVideoFile($strUserAgent, $strVideofile, $preferredLanguages, $saveFolder=null, $strAPIURL='http://sandbox.thesubdb.com/') {
		if ($saveFolder !== null && !file_exists($saveFolder)) mkdir($saveFolder);
		
		$sub = new EasysubtitleDownloader($strAPIURL);
		$sub->setVideofile($strVideofile);
		$sub->setPreferredLanguages($preferredLanguages);
		$sub->setUserAgent($strUserAgent);
		
		$available = $sub->getAvailableSubtitles();
		if ($available === null) return ['code'=>0, 'message'=>'No subtitle available for that video file'];
		
		$bestSubtitle = $sub->searchForPreferredSubtitle($available);
		if ($bestSubtitle === null) return ['code'=>0, 'message'=>'No subtitle available for the preferred languages'];
		
		if ($saveFolder !== null) $saveFolder = $sub->getSrtFilenameByFolder($saveFolder);
		$subtitle = $sub->downloadSubtitle($bestSubtitle, $saveFolder);
		
		return ['code'=>1, 'message'=>$subtitle];
	}
	/**
	 * Returns the fully qualified path to a SRT file for a custom save folder.
	 *
	 * @param string $saveFolder Folder where the SRT file should be saved
	 * @return string
	 */
	public function getSrtFilenameByFolder($saveFolder) {
		$saveFolder 	= str_replace('\\', '/', $saveFolder);
		$saveFolder 	= rtrim($saveFolder, '/');
		$saveFolder		= $saveFolder.'/';
		
		$videoFilename	= explode('/', $this->videofile);
		$videoFilename	= end($videoFilename);
		$srtFilename	= substr($videoFilename, 0, -3).'srt';
		return $saveFolder.$srtFilename;
	}
	/**
	 * Assembles the URL for the API call.
	 *
	 * @param string $action Action on the API
	 * @param string $hash MD5 hash of the file you are talking about
	 * @param array $arrExtraParameters Extra parameters [$key=>$value] that should be passed to the API
	 * @return string Full URL
	 */
	private function getAPIFullURL($action, $hash, $arrExtraParameters) {
		$url = $this->apiURL.'?action='.$action.'&hash='.$hash;
		foreach($arrExtraParameters as $parameter=>$val) {
			$url .= '&'.$parameter.'='.$val;
		}
		return $url;
	}
	/**
	 * Communicates with the API server and returns plain text response.
	 *
	 * @param string $action Action on the API
	 * @param string $hash MD5 hash of the file you are talking about
	 * @param array $arrExtraParameters Extra parameters [$key=>$value] that should be passed to the API
	 * @throws Exception
	 * @return string Plain text API response or `null` on http code 404 (special code for dbsub.com)
	 */
	private function getDataFromAPIServer($action, $hash, $arrExtraParameters=[]) {
		if ($this->apiURL === null)		throw new Exception('API URL not set');
		if ($this->userAgent === null)	throw new Exception('User agent not set');
		
		$url = $this->getAPIFullURL($action, $hash, $arrExtraParameters);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
		
		$response	= curl_exec($ch);
		$header_size= curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		
		$http_code 	= curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$body		= substr($response, $header_size);
		
		curl_close($ch);
		
		switch ($http_code) {
			case 200:
				return $body;
				break;
			case 404:
				return null;
				break;
			default:
				throw new Exception('API error > http code '.$http_code);
				break;
		}
	}
}