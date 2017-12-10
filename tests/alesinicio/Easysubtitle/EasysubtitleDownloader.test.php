<?php
namespace alesinicio\Easysubtitle;

use Exception;

/**
 * Test class for `subtitleDownloader`.
 *
 * @author alexandre.sinicio
 * @version 1.000.000
 */
class EasysubtitleDownloaderTest {
	private static $testResults = [];
	public static function runAllTests() {
		$tests = get_class_methods(__CLASS__);
		$class = __CLASS__;
		foreach($tests as $key=>$test) {
			$fullMethodName = __CLASS__.'::'.$test;
			if ($fullMethodName !== __METHOD__) {
				$class::$test();
			}
		}
		return self::$testResults;
	}
	private static function test_no_video_file_throws_exception() {
		$sub = new EasysubtitleDownloader();
		try {
			$sub->getAvailableSubtitles();
			self::$testResults[__METHOD__] = 'FAIL';
		} catch (Exception $e) {
			self::$testResults[__METHOD__] = 'pass';
		}
	}
	private static function test_invalid_video_file_throws_exception() {
		$sub = new EasysubtitleDownloader();
		$sub->setVideofile('UNAVAILABLE_VIDEO_FILE');
		try {
			$sub->getAvailableSubtitles();
			self::$testResults[__METHOD__] = 'FAIL';
		} catch (Exception $e) {
			self::$testResults[__METHOD__] = 'pass';
		}
	}
	private static function test_valid_video_file_and_no_preferred_language_throws_exception() {
		$sub = new EasysubtitleDownloader();
		$tmpFile = __DIR__.'/tmp.video';
		file_put_contents($tmpFile, '');
		
		$sub->setVideofile($tmpFile);
		try {
			$sub->getAvailableSubtitles();
			self::$testResults[__METHOD__] = 'FAIL';
		} catch (Exception $e) {
			self::$testResults[__METHOD__] = 'pass';
		} finally {
			@unlink($tmpFile);
		}
	}
	private static function test_no_user_agent_throws_exception() {
		$sub = new EasysubtitleDownloader();
		$tmpFile = __DIR__.'/tmp.video';
		file_put_contents($tmpFile, '');
		
		$sub->setVideofile($tmpFile);
		$sub->setPreferredLanguages('en');
		try {
			$sub->getAvailableSubtitles();
			self::$testResults[__METHOD__] = 'FAIL';
		} catch (Exception $e) {
			self::$testResults[__METHOD__] = 'pass';
		} finally {
			@unlink($tmpFile);
		}
	}
	private static function test_invalid_user_agent_throws_exception() {
		$sub = new EasysubtitleDownloader();
		$tmpFile = __DIR__.'/tmp.video';
		file_put_contents($tmpFile, '');
		
		$sub->setVideofile($tmpFile);
		$sub->setPreferredLanguages('en');
		$sub->setUserAgent('INVALID');
		try {
			$sub->getAvailableSubtitles();
			self::$testResults[__METHOD__] = 'FAIL';
		} catch (Exception $e) {
			self::$testResults[__METHOD__] = 'pass';
		} finally {
			@unlink($tmpFile);
		}
	}
	private static function test_no_subtitles_available_also_means_no_preferred_subtitle() {
		$sub = new EasysubtitleDownloader();
		
		$sub->setPreferredLanguages('en');
		
		$subtitles = null;
		if ($sub->searchForPreferredSubtitle($subtitles) === null) {
			self::$testResults[__METHOD__] = 'pass';
		} else {
			self::$testResults[__METHOD__] = 'FAIL';
		}
	}
	private static function test_no_subtitles_available_for_preferred_language_also_means_no_preferred_subtitle() {
		$sub = new EasysubtitleDownloader();
		
		$sub->setPreferredLanguages('en');
		
		$subtitles = 'pt';
		if ($sub->searchForPreferredSubtitle($subtitles) === null) {
			self::$testResults[__METHOD__] = 'pass';
		} else {
			self::$testResults[__METHOD__] = 'FAIL';
		}
	}
	private static function test_multi_subtitles_available_gets_only_preferred_subtitle() {
		$sub = new EasysubtitleDownloader();
		
		$sub->setPreferredLanguages('en');
		
		$subtitles = 'pt,en,es';
		if ($sub->searchForPreferredSubtitle($subtitles) === 'en') {
			self::$testResults[__METHOD__] = 'pass';
		} else {
			self::$testResults[__METHOD__] = 'FAIL';
		}
	}
	private static function test_multi_subtitles_available_gets_first_preferred_subtitle() {
		$sub = new EasysubtitleDownloader();
		
		$sub->setPreferredLanguages('es,en');
		
		$subtitles = 'pt,en,es';
		if ($sub->searchForPreferredSubtitle($subtitles) === 'es') {
			self::$testResults[__METHOD__] = 'pass';
		} else {
			self::$testResults[__METHOD__] = 'FAIL';
		}
	}
	private static function test_setting_preferred_language_to_invalid_value_gets_no_preferred_subtitle() {
		$sub = new EasysubtitleDownloader();
		
		$sub->setPreferredLanguages([[]]);
		
		$subtitles = 'pt,en,es';
		if ($sub->searchForPreferredSubtitle($subtitles) === null) {
			self::$testResults[__METHOD__] = 'pass';
		} else {
			self::$testResults[__METHOD__] = 'FAIL';
		}
	}
	private static function test_setting_custom_save_folder_generates_correct_str_filename() {
		$sub = new EasysubtitleDownloader();
		
		$file = 'c:/test/videofile.mp4';
		$sub->setVideofile($file);
		if ($sub->getSrtFilenameByFolder('c:/anotherfolder') === 'c:/anotherfolder/videofile.srt') {
			self::$testResults[__METHOD__] = 'pass';
		} else {
			self::$testResults[__METHOD__] = 'FAIL';
		}
	}
	private static function test_video_hashing_generates_correct_hash() {
		$sub = new EasysubtitleDownloader();
		$sub->setVideofile(__DIR__.'/justified.mp4');
		
		$expectedHash	= 'edc1981d6459c6111fe36205b4aff6c2';
		
		if ($sub->getVideofileMD5Hash() === $expectedHash) {
			self::$testResults[__METHOD__] = 'pass';
		} else {
			self::$testResults[__METHOD__] = 'FAIL';
		}
	}
	private static function test_sample_video_yelds_subtitles() {
		$sub = new EasysubtitleDownloader();
		$sub->setVideofile(__DIR__.'/justified.mp4');
		$sub->setUserAgent('SubDB/1.0 (alesinicio_test/0.1; https://bitbucket.org/alesinicio/)');
		
		if ($sub->getAvailableSubtitles() !== null) {
			self::$testResults[__METHOD__] = 'pass';
		} else {
			self::$testResults[__METHOD__] = 'FAIL (This test may fail if sample data from subdb.com has changed -- we are HOPING it will not change)';
		}
	}
	private static function test_sample_video_downloads_en_subtitles_correctly() {
		$sub = new EasysubtitleDownloader();
		$sub->setVideofile(__DIR__.'/justified.mp4');
		$sub->setUserAgent('SubDB/1.0 (alesinicio_test/0.1; https://bitbucket.org/alesinicio/)');
		
		$expectedHash = 'b60752821a4a94d7107ac789a663b625';
		
		if ($sub->downloadSubtitle('en') !== null) {
			$srtFile = __DIR__.'/justified.srt';
			if (!file_exists($srtFile)) {
				self::$testResults[__METHOD__] = 'FAIL';
				return;
			}
			if (md5_file($srtFile) !== $expectedHash) {
				self::$testResults[__METHOD__] = 'FAIL (This fail condition may occurr if sample data from subdb.com has changed -- we are HOPING it will not change)';
				@unlink($srtFile);
				return;
			}
			self::$testResults[__METHOD__] = 'pass';
		} else {
			self::$testResults[__METHOD__] = 'FAIL (This fail condition may occurr if sample data from subdb.com has changed -- we are HOPING it will not change)';
		}
		@unlink($srtFile);
	}
}

