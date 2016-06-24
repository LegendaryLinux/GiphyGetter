<?php
include_once(__DIR__.'/GiphyGetter.php');

if(!isset($_GET['gif'])){
	http_response_code(200);
	print "You didn't search for anything! Use 'gif' in a GET variable!";
	exit(0);
}

$random = isset($_GET['random']);
$forceDownload = isset($_GET['forceDownload']);

$giphy = new GiphyGetter(null, __DIR__.'/temp');

if(isset($_GET['urlOnly'])){
	print $giphy->requestGifUrl($_GET['gif'],$random);
}else{
	$giphy->requestGif($_GET['gif'],$forceDownload,$random);
}
