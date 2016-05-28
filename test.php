<?php
include_once(__DIR__.'/GiphyGetter.php');

if(!isset($_GET['gif'])){
	http_response_code(200);
	print "You didn't search for anything! Use 'gif' in a GET variable!";
	exit(0);
}

$giphy = new GiphyGetter(__DIR__.'/temp'); // SET ME TO A DIRECTORY WITH R/W PERMISSIONS
$giphy->requestGif($_GET['gif'],false); // TRUE TO FORCE A DOWNLOAD