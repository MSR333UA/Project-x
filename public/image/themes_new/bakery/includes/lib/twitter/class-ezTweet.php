<?php
/*

Stan Scates
blr | further

stan@sc8s.com
blrfurther.com

Basic OAuth and caching layer for Seaofclouds' tweet.js, designed
to introduce compatibility with Twitter's v1.1 API.

Version: 1.4
Created: 2013.02.20

https://github.com/seaofclouds/tweet
https://github.com/themattharris/tmhOAuth

Modified by Milingona

*/

class ezTweet {
	/*************************************** config ***************************************/

   // Your Twitter App Consumer Key
	private $consumer_key = '';

	// Your Twitter App Consumer Secret
	private $consumer_secret = '';

	// Your Twitter App Access Token
	private $user_token = '';

	// Your Twitter App Access Token Secret
	private $user_secret = '';

	// Path to tmhOAuth libraries
	private $lib = '';

	// Enable caching
	private $cache_enabled = true;

	// Cache interval (minutes)
	private $cache_interval = 15;

	// Path to writable cache directory
	private $cache_dir = '';

	// Enable debugging
	private $debug = false;

	// Request
	private $request = '';

	/**************************************************************************************/

	public function __construct($consumer_key, $consumer_secret, $user_token, $user_secret, $request) {
		$this->consumer_key = $consumer_key;
		$this->consumer_secret = $consumer_secret;
		$this->user_token = $user_token;
		$this->user_secret = $user_secret;

		// The received json needs to be turned back into an array
		$this->request = json_decode(stripslashes($request), true);

		// Twitter lib path
		$this->lib = dirname(__FILE__) . '/';

		// Initialize paths and etc.
		$this->pathify($this->lib);
		$this->message = '';
	}

	public function fetch() {
		echo json_encode(
			array(
				'response' => json_decode($this->getJSON(), true),
				'message' => ($this->debug) ? $this->message : false
			)
		);
	}

	private function getJSON() {
		if($this->cache_enabled === true) {
			$cache = json_decode(get_option('vu_twitter_feeds', '{}'), true);

			if(!empty($cache) && (isset($cache['time']) && intval($cache['time']) > (time() - 60 * intval($this->cache_interval)))) {
				return $cache['data'];
			} else {

				$JSONraw = $this->getTwitterJSON();
				$JSON = $JSONraw['response'];

				// Don't write a bad cache file if there was a CURL error
				if($JSONraw['errno'] != 0) {
					$this->consoleDebug($JSONraw['error']);
					return $JSON;
				}

				if($this->debug === true) {
					// Check for twitter-side errors
					$pj = json_decode($JSON, true);
					if(isset($pj['errors'])) {
						foreach($pj['errors'] as $error) {
							$message = 'Twitter Error: "'.$error['message'].'", Error Code #'.$error['code'];
							$this->consoleDebug($message);
						}
						return false;
					}
				}

				// save twitter feeds
				if($JSONraw) {
					if( get_option('vu_twitter_feeds') !== false ){
						update_option('vu_twitter_feeds', json_encode(array('time' => time(), 'data' => $JSON)));
					} else {
						add_option('vu_twitter_feeds', json_encode(array('time' => time(), 'data' => $JSON)), null, 'no');
					}
				}

				return $JSON;
			}
		} else {
			$JSONraw = $this->getTwitterJSON();

			if($this->debug === true) {
				// Check for CURL errors
				if($JSONraw['errno'] != 0) {
					$this->consoleDebug($JSONraw['error']);
				}

				// Check for twitter-side errors
				$pj = json_decode($JSONraw['response'], true);
				if(isset($pj['errors'])) {
					foreach($pj['errors'] as $error) {
						$message = 'Twitter Error: "'.$error['message'].'", Error Code #'.$error['code'];
						$this->consoleDebug($message);
					}
					return false;
				}
			}
			return $JSONraw['response'];
		}
	}

	private function getTwitterJSON() {
		require_once($this->lib.'tmhOAuth.php');
		require_once($this->lib.'tmhUtilities.php');

		$tmhOAuth = new tmhOAuth(array(
			'host'                  => $this->request['host'],
			'consumer_key'          => $this->consumer_key,
			'consumer_secret'       => $this->consumer_secret,
			'user_token'            => $this->user_token,
			'user_secret'           => $this->user_secret,
			'curl_ssl_verifypeer'   => false
		));

		$url = $this->request['url'];
		$params = $this->request['parameters'];

		$tmhOAuth->request('GET', $tmhOAuth->url($url), $params);
		return $tmhOAuth->response;
	}

	private function pathify(&$path) {
		// Ensures our user-specified paths are up to snuff
		$path = realpath($path).'/';
	}

	private function consoleDebug($message) {
		if($this->debug === true) {
			$this->message .= 'tweet.js: '.$message."\n";
		}
	}
}

?>