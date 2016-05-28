# GiphyGetter

### What is it?
Simply, it's a `PHP 7` class you can use to send a search term to Giphy and get back a single image, which is returned to your user. You can choose whether to force a download or just display it as a normal image.

### Where might I implement this?
Anywhere you want a user to be able to type a search and get a gif in return. Any ajax implemention should be fairly simple to throw together.

### Any future development plans?
At the moment it just returns the first result. There are plans to allow a random gif matching the search to be returned. There are also plans to allow just the URL to be returned. That way you can have a random gif URL sent to your #irc channel.

### How do I use it?
The constructor requires a directory in which it can store local files while it transfers them to the user. It also accepts a second parameter, `accessToken`. Giphy has an API which this class utilizes, and they ask that you don't make tons of requests with their demo token, which this class includes as the default token. You can get a Giphy API token here: http://api.giphy.com/submit

### Code example:
```
// Require the file
require_once(__DIR__.'/GiphyGetter.php');

// A directory to temporarily store local files is required. 
// The access token field is optional, but you should really get one.
$giphy = new GiphyGetter(__DIR__.'/temp', MY_GIPHY_ACCESS_TOKEN);

// True forces a downlaod with Content-Disposition: attachment
$giphy->requestGif($_GET['gif'],false); 
```
