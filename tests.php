<?php

use alesinicio\Easysubtitle\EasysubtitleDownloaderTest;

require 'vendor/alesinicio/Easysubtitle/EasysubtitleDownloader.php';
require 'tests/alesinicio/Easysubtitle/EasysubtitleDownloader.test.php';

try {
	$results = EasysubtitleDownloaderTest::runAllTests();
	print_r($results);
} catch (Exception $e) {
	die($e->getMessage());
}