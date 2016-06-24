<?php

# Giphy has multiple sizes of images it can return. Here, we simplify their naming
# structure to make it easier to decide what size we want
const GIPHY_MEDIUM_HEIGHT = 'fixed_height';
const GIPHY_MEDIUM_WIDTH = 'fixed_width';
const GIPHY_SMALL_HEIGHT = 'fixed_height_small';
const GIPHY_SMALL_WIDTH = 'fixed_width_small';
const GIPHY_ORIGINAL = 'original';

/**
 * Class GiphyGetter
 * Used to search for a gif from Giphy and return it as a file to be downloaded or saved to the local drive
 */
class GiphyGetter{
	/**
	 * API Key used for making Giphy calls. Defaults to public test key.
	 * @var string
	 */
	protected $apiKey = 'dc6zaTOxFJmzC';

	/**
	 * We default to the original size of the Giphy file found
	 * @var string imageSize
	 */
	protected $imageSize = GIPHY_ORIGINAL;

	/**
	 * cURL handler for use in making API calls to Giphy
	 * @var resource
	 */
	protected $curl;

	/**
	 * The specified temporary directory into which the gif files will be saved while transferring them
	 * to the user
	 * @var string $tempDirectory
	 */
	protected $tempDirectory;

	/**
	 * GiphyGetter constructor.
	 * @param string|null $apiKey
	 * @param string|null $tempDirectory
	 */
	public function __construct(string $apiKey = null, string $tempDirectory = null){
		# Allow the user to define their own API_KEY
		if($apiKey) $this->API_KEY = $apiKey;

		# Set the temp directory, if provided
		if($tempDirectory) $this->tempDirectory = $tempDirectory;

		# Create the cURL handler to be used when making API requests
		$this->curl = curl_init();
	}

	/**
	 * Let the user optionally choose from a predefined list of giphy image size formats
	 * @param string $size
	 * @return $this
	 */
	public function setImageSize(string $size){
		# Make sure the size given is valid
		if(!in_array($size,[GIPHY_ORIGINAL,GIPHY_SMALL_WIDTH,GIPHY_SMALL_HEIGHT, GIPHY_MEDIUM_WIDTH,GIPHY_MEDIUM_HEIGHT]))
			$this->error('Invalid giphy size parameter provided. You must use one of the defined constants.');
		else $this->imageSize = $size;
		return $this;
	}

	/**
	 * Send a cURL call to the Giphy API and get the direct download URL for the requested gif
	 * @param string $search
	 * @param bool $random
	 * @return string || bool
	 */
	protected function findGifUrl(string $search, bool $random = true){
		try{
			curl_setopt_array($this->curl,[
				CURLOPT_URL => 'http://api.giphy.com/v1/gifs/search?q='.urlencode($search) .
					'&api_key='.$this->apiKey,
				CURLOPT_CUSTOMREQUEST => 'GET',
				CURLOPT_RETURNTRANSFER => true,
			]);
			$response = json_decode(curl_exec($this->curl),true);
			$img = $random ? mt_rand(0,count($response['data'])-1) : 0;
			return $response['data'][$img]['images'][$this->imageSize]['url'] ?? false;
		}catch(Throwable $E){
			$this->fail($E);
			return false;
		}
	}

	/**
	 * Download the image file from the remote URL to the local temporary file
	 * @param string $url
	 * @param string $search
	 * @return bool|string
	 */
	protected function downloadImage(string $url, string $search){
		try{
			# If the directory separator is not already on the end of the directory string, we add it
			if(substr($this->tempDirectory,-1) !== DIRECTORY_SEPARATOR)
				$this->tempDirectory = $this->tempDirectory.DIRECTORY_SEPARATOR;

			# Add a random number to the end of the filename to prevent collisions
			$tempName = $search.'-'.mt_rand(1000000,9999999).'gif';

			# Download the gif to the local temp directory
			if(!file_put_contents($this->tempDirectory.$tempName,fopen($url,'r')))
				return false;
			return $this->tempDirectory.$tempName;
		}catch(Throwable $E){
			$this->fail($E);
			return false;
		}
	}

	/**
	 * Search for and deliver a gif to the user 
	 * @param string $search They keyword used to search for a gif
	 * @param bool $forceDownload If true, forces a download with Content-Disposition: attachment
	 * @param bool $random If false, tends to return the same gif for a given keyword
	 * @return bool
	 */
	public function requestGif(string $search, bool $forceDownload = false, bool $random = true){
		try{
			if(!$this->tempDirectory){
				header('Content-Type: application/json');
				print json_encode(['error' => 'Unable to store local file. No temp directory was provided.']);
				http_response_code(400);
				return false;
			}

			# If no image can be found, send a 404
			if(!$gifUrl = $this->findGifUrl($search, $random)){
				http_response_code(404);
				return false;
			}

			# If we can't download the image, send a 500
			if(!$localFile = $this->downloadImage($gifUrl,$search)){
				http_response_code(500);
				return false;
			}

			# If we have the image, send it to the user and delete the local file
			header('Content-Type: image/gif');
			header('Content-Length: '.filesize($localFile));

			# If we're forcing a download, let's set that header now
			if($forceDownload) header('Content-Disposition: attachment; filename="'.$search.'.gif'.'"');
			else header('Content-Disposition: filename="'.$search.'.gif'.'"');

			# Output the file
			readfile($localFile);
			http_response_code(200);

			# Delete the temp file
			unlink($localFile);
			return true;
		}catch(Throwable $E){
			$this->fail($E);
			http_response_code(500);
			return false;
		}
	}

	/**
	 * Returns a URL of a gif from Giphy
	 * @param string $search They keyword to use when searching for a gif
	 * @param bool $random If false, tends to return the same image for multiple requests
	 * @return string || bool
	 */
	public function requestGifUrl(string $search, bool $random = true){
		return $this->findGifUrl($search, $random);
	}

	/**
	 * Error function which logs the error message and name of function causing the error
	 * @param Throwable $E
	 */
	protected function fail(Throwable $E){
		$functionStack = debug_backtrace();
		error_log("An error occurred in function {$functionStack[1]['function']} with error message:\n"
			.$E->getMessage());
	}

	/**
	 * Write an error log containing a custom message and the at-fault function
	 * @param string $message
	 */
	protected function error(string $message){
		$functionStack = debug_backtrace();
		error_log("An error occurred in function {$functionStack[1]['function']} with error message:\n".$message);
	}
}
