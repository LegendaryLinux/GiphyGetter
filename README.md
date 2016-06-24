# GiphyGetter

### What is it?
Simply, it's a `PHP 7` class you can use to send a search term to Giphy and get back a gif. You can choose to
force a download, just display it as a normal image, or simply return the image url.

### Where might I implement this?
Anywhere you want a user to be able to type a keyword and get a gif in return. An ajax implementation should be fairly
simple to throw together.

### How do I use it?
The constructor accepts two optional parameters. The first is `accessToken`. Giphy has an API which this class
utilizes, and they ask that you don't make tons of requests with their demo token, which this class includes by
default. You can get a Giphy API token here: http://api.giphy.com/submit. The second parameter is `tempDirectory`.
Here, GiphyGetter will store files locally while it transfers them to the user. If you are only using GiphyGetter
to find URLs, you do not need to provide a temp directory.

### Code examples:
```
// Require the file
require_once(__DIR__.'/GiphyGetter.php');

// A directory to temporarily store local files is required unless you are only requesting URLs.
// The access token field is optional, but you should really get one.
$giphy = new GiphyGetter(MY_GIPHY_ACCESS_TOKEN, __DIR__.'/temp');

// Display a random gif directly in your browser
$giphy->requestGif($_GET['keyword']);

// Force the download of a random gif through your browser using Content-Disposition: attachment
$giphy->requestGif($_GET['keyword'],true);

// Display a non-random gif in your browser. This tends to return the same gif across multiple requests
print $giphy->requestGif($_GET['keyword'],false,false);

// Get a random gif url
$url = $giphy->requestGifUrl($_GET['keyword']);

// Get a non-random gif ufl
$url = $giphy->requestGifUrl($_GET['keyword'],false);
```
