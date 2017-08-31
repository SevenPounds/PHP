<?php
class Result{
	var $msg;
	var $data;
	var $error;
	function __construct(){
		$this->msg='';
		$this->data='';
		$this->error='';
	}
	
	function getJsonData($data){
		$this->data=$data;
		
	}
}

class HttpClient{

	var $host;
	var $port;
	var $path;
	var $method;
	var $postdata = '';
	var $cookies = array ();
	var $files = array ();
	var $referer;
	var $accept = 'text/xml,application/xml,application/json,application/xhtml+xml,text/html,text/plain,image/png,image/jpeg,image/gif,*/*';
	var $accept_encoding = 'gzip';
	var $accept_language = 'en-us';
	var $user_agent = 'SW HttpClient v0.1';
	// Options
	var $timeout = 1000;
	var $use_gzip = true;
	var $persist_cookies = true; // If true, received cookies are placed in the $this->cookies array ready for the next request
	// Note: This currently ignores the cookie path (and time) completely. Time is not important,
	//       but path could possibly lead to security problems.
	var $persist_referers = true; // For each request, sends path of last request as referer
	var $debug = false;
	var $handle_redirects = true; // Automatically redirect if Location or URI header is found
	var $max_redirects = 5;
	var $headers_only = false; // If true, stops receiving once headers have been read.
	// Basic authorization variables
	var $username;
	var $password;
	// Response vars
	var $status;
	var $headers = array ();
	var $content = '';
	var $errormsg;
	// Tracker variables
	var $redirect_count = 0;
	var $cookie_host = '';
	

	function HttpClient($host, $port = 80) {
		$this->host = $host;
		$this->port = $port;
	}
	
	function get($path, $data = false) {
		$this->path = $path;
		$this->method = 'GET';
		if ($data) {
			$this->path .= '?' . $this->buildQueryString($data);
		}
		return $this->doRequest();
	}
	
	/**
	 *
	 * $files = array();
	 *
	 * $uploadedfile = array();
	 * $uploadedfile['name'] = $name;
	 * $uploadedfile['path'] = $newname;
	 * $uploadedfile['type'] = $file['type'];
	
	 * $files[] = $uploadedfile;
	 *
	 *
	 */
	function post($path, $data, $files = array ()) {
		$this->path = $path;
		$this->method = 'POST';
		$this->files = $files;
		$this->postdata = $this->buildQueryString($data);
		return $this->doRequest();
	}
	
	
	function postStream($path, $data, $files = array ()) {
		$this->path = $path;
		$this->method = 'POST';
		$this->files = $files;
		$this->postdata = $data;
		return $this->doStreamRequest();
	}
	
	function buildQueryString($data) {
		$querystring = '';
		if (is_array($data)) {
			// Change data in to postable data
			foreach ($data as $key => $val) {
				if (is_array($val)) {
					foreach ($val as $key2 => $val2) {
						$querystring .= urlencode($key) . "[" . urlencode($key2) . "]" . '=' . urlencode($val2) . '&';
					}
				} else {
					$querystring .= urlencode($key) . '=' . urlencode($val) . '&';
				}
			}
			$querystring = substr($querystring, 0, -1); // Eliminate unnecessary &
		} else {
			$querystring = $data;
		}
		return $querystring;
	}
	
	function doStreamRequest() {
		// Performs the actual HTTP request, returning true or false depending on outcome
		if (!$fp = fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout)) {
			// Set error message
			switch ($errno) {
				case -3 :
					$this->errormsg = 'Socket creation failed (-3)'; break;
				case -4 :
					$this->errormsg = 'DNS lookup failure (-4)'; break;
				case -5 :
					$this->errormsg = 'Connection refused or timed out (-5)'; break;
				default :
					$this->errormsg = 'Connection failed (' . $errno . ')';
					$this->errormsg .= ' ' . $errstr;
					$this->debug($this->errormsg);
			}
		}
		socket_set_timeout($fp, $this->timeout);
		$request = $this->buildStreamRequest();
		$this->debug('Request', $request);
		fwrite($fp, $request);
		// Reset all the variables that should not persist between requests
		$this->headers = array ();
		$this->content = '';
		$this->errormsg = '';
		// Set a couple of flags
		$inHeaders = true;
		$atStart = true;
		// Now start reading back the response
		while (!feof($fp)) {
			$line = fgets($fp, 4096);
			if ($atStart) {
				// Deal with first line of returned data
				$atStart = false;
				if (!preg_match('/HTTP\/(\\d\\.\\d)\\s*(\\d+)\\s*(.*)/', $line, $m)) {
					$this->errormsg = "Status code line invalid: " . htmlentities($line);
					$this->debug($this->errormsg);
					$this->debug("Line:", $line);
	
					// Hack: Request, der fehlgeschlagen ist, in Datei schreiben
					$filename = dirname(__FILE__).'/tmp/output'.rand(1,9999999).'.txt';
	
					if ($handle = fopen($filename, "w+")) {
						fwrite($handle, $request);
						fclose($handle);
					}
	
	
					return false;
				}
				$http_version = $m[1]; // not used
				$this->status = $m[2];
				$status_string = $m[3]; // not used
				$this->debug(trim($line));
				continue;
			}
			if ($inHeaders) {
				if (trim($line) == '') {
					$inHeaders = false;
					$this->debug('Received Headers', $this->headers);
					if ($this->headers_only) {
						break; // Skip the rest of the input
					}
					continue;
				}
				if (!preg_match('/([^:]+):\\s*(.*)/', $line, $m)) {
					// Skip to the next header
					continue;
				}
				$key = strtolower(trim($m[1]));
				$val = trim($m[2]);
				// Deal with the possibility of multiple headers of same name
				if (isset ($this->headers[$key])) {
					if (is_array($this->headers[$key])) {
						$this->headers[$key][] = $val;
					} else {
						$this->headers[$key] = array (
								$this->headers[$key],
								$val
						);
					}
				} else {
					$this->headers[$key] = $val;
				}
				continue;
			}
			// We're not in the headers, so append the line to the contents
			$this->content .= $line;
		}
		fclose($fp);
		// If data is compressed, uncompress it
		if (isset ($this->headers['content-encoding']) && $this->headers['content-encoding'] == 'gzip') {
			$this->debug('Content is gzip encoded, unzipping it');
			$this->content = substr($this->content, 10); // See http://www.php.net/manual/en/function.gzencode.php
			$this->content = gzinflate($this->content);
		}
		// If $persist_cookies, deal with any cookies
		if ($this->persist_cookies && isset ($this->headers['set-cookie'])) {
			$cookies = $this->headers['set-cookie'];
			if (!is_array($cookies)) {
				$cookies = array (
						$cookies
				);
			}
			foreach ($cookies as $cookie) {
				if (preg_match('/([^=]+)=([^;]+);/', $cookie, $m)) {
					$this->cookies[$m[1]] = $m[2];
				}
			}
			// Record domain of cookies for security reasons
			$this->cookie_host = $this->host;
		}
		// If $persist_referers, set the referer ready for the next request
		if ($this->persist_referers) {
			$this->debug('Persisting referer: ' . $this->getRequestURL());
			$this->referer = $this->getRequestURL();
		}
		// Finally, if handle_redirects and a redirect is sent, do that
		if ($this->handle_redirects) {
			if (++ $this->redirect_count >= $this->max_redirects) {
				$this->errormsg = 'Number of redirects exceeded maximum (' . $this->max_redirects . ')';
				$this->debug($this->errormsg);
				$this->redirect_count = 0;
				return false;
			}
			$location = isset ($this->headers['location']) ? $this->headers['location'] : '';
			$uri = isset ($this->headers['uri']) ? $this->headers['uri'] : '';
			if ($location || $uri) {
				$url = parse_url($location . $uri);
				// This will FAIL if redirect is to a different site
				return $this->get($url['path'], $url['query']);
			}
		}
		return true;
	}
	
	function doRequest() {
		// Performs the actual HTTP request, returning true or false depending on outcome
		if (!$fp = fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout)) {
			// Set error message
			switch ($errno) {
				case -3 :
					$this->errormsg = 'Socket creation failed (-3)'; break;
				case -4 :
					$this->errormsg = 'DNS lookup failure (-4)'; break;
				case -5 :
					$this->errormsg = 'Connection refused or timed out (-5)'; break;
				default :
					$this->errormsg = 'Connection failed (' . $errno . ')';
					$this->errormsg .= ' ' . $errstr;
					$this->debug($this->errormsg);
			}
		}
		socket_set_timeout($fp, $this->timeout);
		$request = $this->buildRequest();
		$this->debug('Request', $request);
		fwrite($fp, $request);
		// Reset all the variables that should not persist between requests
		$this->headers = array ();
		$this->content = '';
		$this->errormsg = '';
		// Set a couple of flags
		$inHeaders = true;
		$atStart = true;
		// Now start reading back the response
		while (!feof($fp)) {
			$line = fgets($fp, 4096);
			if ($atStart) {
				// Deal with first line of returned data
				$atStart = false;
				if (!preg_match('/HTTP\/(\\d\\.\\d)\\s*(\\d+)\\s*(.*)/', $line, $m)) {
					$this->errormsg = "Status code line invalid: " . htmlentities($line);
					$this->debug($this->errormsg);
					$this->debug("Line:", $line);
					// Hack: Request, der fehlgeschlagen ist, in Datei schreiben
					$filename = dirname(__FILE__).'/tmp/output'.rand(1,9999999).'.txt';
					if ($handle = fopen($filename, "w+")) {
						fwrite($handle, $request);
						fclose($handle);
					}
	
					return false;
				}
				$http_version = $m[1]; // not used
				$this->status = $m[2];
				$status_string = $m[3]; // not used
				$this->debug(trim($line));
				continue;
			}
			if ($inHeaders) {
				if (trim($line) == '') {
					$inHeaders = false;
					$this->debug('Received Headers', $this->headers);
					if ($this->headers_only) {
						break; // Skip the rest of the input
					}
					continue;
				}
				if (!preg_match('/([^:]+):\\s*(.*)/', $line, $m)) {
					// Skip to the next header
					continue;
				}
				$key = strtolower(trim($m[1]));
				$val = trim($m[2]);
				// Deal with the possibility of multiple headers of same name
				if (isset ($this->headers[$key])) {
					if (is_array($this->headers[$key])) {
						$this->headers[$key][] = $val;
					} else {
						$this->headers[$key] = array (
								$this->headers[$key],
								$val
						);
					}
				} else {
					$this->headers[$key] = $val;
				}
				continue;
			}
			// We're not in the headers, so append the line to the contents
			$this->content .= $line;
		}
		fclose($fp);
		// If data is compressed, uncompress it
		if (isset ($this->headers['content-encoding']) && $this->headers['content-encoding'] == 'gzip') {
			$this->debug('Content is gzip encoded, unzipping it');
			$this->content = substr($this->content, 10); // See http://www.php.net/manual/en/function.gzencode.php
			$this->content = gzinflate($this->content);
		}
		// If $persist_cookies, deal with any cookies
		if ($this->persist_cookies && isset ($this->headers['set-cookie'])) {
			$cookies = $this->headers['set-cookie'];
			if (!is_array($cookies)) {
				$cookies = array (
						$cookies
				);
			}
			foreach ($cookies as $cookie) {
				if (preg_match('/([^=]+)=([^;]+);/', $cookie, $m)) {
					$this->cookies[$m[1]] = $m[2];
				}
			}
			// Record domain of cookies for security reasons
			$this->cookie_host = $this->host;
		}
		// If $persist_referers, set the referer ready for the next request
		if ($this->persist_referers) {
			$this->debug('Persisting referer: ' . $this->getRequestURL());
			$this->referer = $this->getRequestURL();
		}
		// Finally, if handle_redirects and a redirect is sent, do that
		if ($this->handle_redirects) {
			if (++ $this->redirect_count >= $this->max_redirects) {
				$this->errormsg = 'Number of redirects exceeded maximum (' . $this->max_redirects . ')';
				$this->debug($this->errormsg);
				$this->redirect_count = 0;
				return false;
			}
			$location = isset ($this->headers['location']) ? $this->headers['location'] : '';
			$uri = isset ($this->headers['uri']) ? $this->headers['uri'] : '';
			if ($location || $uri) {
				$url = parse_url($location . $uri);
				// This will FAIL if redirect is to a different site
				return $this->get($url['path'], $url['query']);
			}
		}
		return true;
	}
	
	
	
	function buildStreamRequest() {
		$headers = array ();
		$headers[] = "{$this->method} {$this->path} HTTP/1.0"; // Using 1.1 leads to all manner of problems, such as "chunked" encoding
		$headers[] = "Host: {$this->host}";
		$headers[] = "User-Agent: {$this->user_agent}";
		$headers[] = "Accept: {$this->accept}";
		if ($this->use_gzip) {
			$headers[] = "Accept-encoding: {$this->accept_encoding}";
		}
		$headers[] = "Accept-language: {$this->accept_language}";
		if ($this->referer) {
			$headers[] = "Referer: {$this->referer}";
		}
		// Cookies
		if ($this->cookies) {
			$cookie = 'Cookie: ';
			foreach ($this->cookies as $key => $value) {
				$cookie .= "$key=$value; ";
			}
			$headers[] = $cookie;
		}
		// Basic authentication
		if ($this->username && $this->password) {
			$headers[] = 'Authorization: BASIC ' . base64_encode($this->username . ':' . $this->password);
		}
	
		if ($this->files) {
			// file upload
			$data = "";
	
			srand((double) microtime() * 1000000);
			$boundary = "---------------------------" . substr(md5(rand(0, 32000)), 0, 10);
	
			$headers[] = "Content-Type: multipart/form-data; boundary=" . $boundary . "";
	
			// attach post vars
			foreach ($_POST as $name => $value) {
				$data .= "--$boundary\r\n";
				$data .= "Content-Disposition: form-data; name=\"" . $name . "\"\r\n";
				$data .= "\r\n" . $value . "\r\n";
				$data .= "--$boundary\r\n";
			}
	
			//附加数据
			foreach ($this->postdata as $key => $value){
				$data .= "--$boundary\r\n";
				$data .= "Content-Disposition: form-data; name=\"" . $key . "\"\r\n";
				$data .= "\r\n" . $value . "\r\n";
				$data .= "--$boundary\r\n";
			}
	
			// and attach the files
			for ($i = 0; $i < count($this->files); $i++) {
				$file = $this->files[$i];
	
				$data .= "--".$boundary."\r\n";
				$data .= "Content-Disposition: form-data; name=\"" . $file['name'] . "\"; filename=\"" . $file['path'] . "\"\r\n";
				$data .= "Content-Type: " . $file['type'] . "\r\n\r\n";
				$fh = fopen($file['path'], "r");
				$data .= fread($fh, filesize($file['path'])) . "\r\n";
				fclose($fh);
				$data .= "--".$boundary."--\r\n";
			}

			$headers[] = "Content-length: " . strlen($data) . "\r\n";
			$request = implode("\r\n", $headers) . "\r\n\r\n" . $data;
		} else
			if ($this->postdata) {
		$headers[] = 'Content-Type: application/x-www-form-urlencoded';
		$headers[] = 'Content-Length: ' . strlen($this->postdata);
		$request = implode("\r\n", $headers) . "\r\n\r\n" . $this->postdata;
		} else {
			$request = implode("\r\n", $headers) . "\r\n\r\n" . $this->postdata;
		}
		return $request;
	}
	
	function buildRequest() {
		$headers = array ();
		$headers[] = "{$this->method} {$this->path} HTTP/1.0"; // Using 1.1 leads to all manner of problems, such as "chunked" encoding
		$headers[] = "Host: {$this->host}";
		$headers[] = "User-Agent: {$this->user_agent}";
		$headers[] = "Accept: {$this->accept}";
		if ($this->use_gzip) {
			$headers[] = "Accept-encoding: {$this->accept_encoding}";
		}
		$headers[] = "Accept-language: {$this->accept_language}";
		if ($this->referer) {
			$headers[] = "Referer: {$this->referer}";
		}
		// Cookies
		if ($this->cookies) {
			$cookie = 'Cookie: ';
			foreach ($this->cookies as $key => $value) {
				$cookie .= "$key=$value; ";
			}
			$headers[] = $cookie;
		}
		// Basic authentication
		if ($this->username && $this->password) {
			$headers[] = 'Authorization: BASIC ' . base64_encode($this->username . ':' . $this->password);
		}
	
		if ($this->files) {
			// file upload
			$data = "";
	
			srand((double) microtime() * 1000000);
			$boundary = "---------------------------" . substr(md5(rand(0, 32000)), 0, 10);
	
			$headers[] = "Content-Type: multipart/form-data; boundary=" . $boundary . "";
	
			// attach post vars
			foreach ($_POST as $name => $value) {
				$data .= "--$boundary\r\n";
				$data .= "Content-Disposition: form-data; name=\"" . $name . "\"\r\n";
				$data .= "\r\n" . $value . "\r\n";
				$data .= "--$boundary\r\n";
			}
			// and attach the files
			for ($i = 0; $i < count($this->files); $i++) {
				$file = $this->files[$i];
	
				$data .= "--".$boundary."\r\n";
				$data .= "Content-Disposition: form-data; name=\"" . $file['name'] . "\"; filename=\"" . $file['path'] . "\"\r\n";
				$data .= "Content-Type: " . $file['type'] . "\r\n\r\n";
				$fh = fopen($file['path'], "r");
				$data .= fread($fh, filesize($file['path'])) . "\r\n";
				fclose($fh);
				$data .= "--".$boundary."--\r\n";
			}

			$headers[] = "Content-length: " . strlen($data) . "\r\n";
			$request = implode("\r\n", $headers) . "\r\n\r\n" . $data;
			
		} else
			if ($this->postdata) {
		$headers[] = 'Content-Type: application/x-www-form-urlencoded';
		$headers[] = 'Content-Length: ' . strlen($this->postdata);
		$request = implode("\r\n", $headers) . "\r\n\r\n" . $this->postdata;
		} else {
			$request = implode("\r\n", $headers) . "\r\n\r\n" . $this->postdata;
		}
		return $request;
	}
	
	
	
	function getStatus() {
		return $this->status;
	}
	function getContent() {
		return $this->content;
	}
	function getHeaders() {
		return $this->headers;
	}
	function getHeader($header) {
		$header = strtolower($header);
		if (isset ($this->headers[$header])) {
			return $this->headers[$header];
		} else {
			return false;
		}
	}
	function getError() {
		return $this->errormsg;
	}
	function getCookies() {
		return $this->cookies;
	}
	function getRequestURL() {
		$url = 'http://' . $this->host;
		if ($this->port != 80) {
			$url .= ':' . $this->port;
		}
		$url .= $this->path;
		return $url;
	}
	// Setter methods
	function setUserAgent($string) {
		$this->user_agent = $string;
	}
	function setAuthorization($username, $password) {
		$this->username = $username;
		$this->password = $password;
	}
	function setCookies($array) {
		$this->cookies = $array;
	}
	// Option setting methods
	function useGzip($boolean) {
		$this->use_gzip = $boolean;
	}
	function setPersistCookies($boolean) {
		$this->persist_cookies = $boolean;
	}
	function setPersistReferers($boolean) {
		$this->persist_referers = $boolean;
	}
	function setHandleRedirects($boolean) {
		$this->handle_redirects = $boolean;
	}
	function setMaxRedirects($num) {
		$this->max_redirects = $num;
	}
	function setHeadersOnly($boolean) {
		$this->headers_only = $boolean;
	}
	function setDebug($boolean) {
		$this->debug = $boolean;
	}
	function setReferer($referer) {
		$this->referer = $referer;
	}
	function setAcceptLanguage($accept_language ) {
		$this->accept_language  = $accept_language;
	}
	// "Quick" static methods
	function quickGet($url) {
		$bits = parse_url($url);
		$host = $bits['host'];
		$port = isset ($bits['port']) ? $bits['port'] : 80;
		$path = isset ($bits['path']) ? $bits['path'] : '/';
		if (isset ($bits['query'])) {
			$path .= '?' . $bits['query'];
		}
		$client = new HttpClient($host, $port);
		
		if (!$client->get($path)) {
			return false;
		} else {
			return $client->getContent();
		}
	}
	// "Quick" static methods
	function solr_quickGet($url,$data) {
		$bits = parse_url($url);
		$host = $bits['host'];
		$port = isset ($bits['port']) ? $bits['port'] : 80;
		$path = isset ($bits['path']) ? $bits['path'] : '/';
		if (isset ($bits['query'])) {
			$path .= '?' . $bits['query'];
		}
		$client = new HttpClient($host, $port);
		if(!$client->solr_get($path,$data)) {
			return false;
		} else {
			return $client->getContent();
		}
	}
	function quickPost($url, $data) {
		$bits = parse_url($url);
		$host = $bits['host'];
		$port = isset ($bits['port']) ? $bits['port'] : 80;
		$path = isset ($bits['path']) ? $bits['path'] : '/';
		$client = new HttpClient($host, $port);
		if (!$client->post($path, $data)) {
			return false;
		} else {
			return $client->getContent();
		}
	}
	
	
	//-----------供2.0新接口调用------------------
	function getRequest($path, $data = false) {
		$this->path = $path;
		$this->method = 'GET';
		if ($data) {
			$this->path .= '?' . $this->buildQueryString($data);
		}
		return $this->doDataRequest($data);
	}
	
	function putRequest($path, $data,$file=array()) {
		$this->path = $path;
		$this->method = 'PUT';
		$this->files=$file;
		return $this->doDataRequest($data);
	}
	
	function postRequest($path, $data, $files = array ()) {
		$this->path = $path;
		$this->method = 'POST';
		$this->files = $files;
		$this->postdata = $this->buildQueryString($data);
		return $this->doDataRequest();
	}
	
	function putStream($path, $data, $files = array ()) {
		$this->path = $path;
		$this->method = 'PUT';
		$this->files = $files;
		return $this->doPutStreamRequest($data);
	}
	
	function deleteRequest($path,$data=array()) {
		$this->path = $path;
		$this->method = 'DELETE';
		return $this->doDataRequest($data);
	}
	
	function doDataRequest($data=array()) {
		
		// Performs the actual HTTP request, returning true or false depending on outcome
		if (!$fp = fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout)) {
			// Set error message
			switch ($errno) {
				case -3 :
					$this->errormsg = 'Socket creation failed (-3)'; break;
				case -4 :
					$this->errormsg = 'DNS lookup failure (-4)';break;
				case -5 :
					$this->errormsg = 'Connection refused or timed out (-5)';break;
				default :
					$this->errormsg = 'Connection failed (' . $errno . ')';
					$this->errormsg .= ' ' . $errstr;
					$this->debug($this->errormsg);
			}
		}
		socket_set_timeout($fp, $this->timeout);
		$request = $this->buildDataRequest($data);
		$this->debug('Request', $request);
		fwrite($fp, $request);
		// Reset all the variables that should not persist between requests
		$this->headers = array ();
		$this->content = '';
		$this->errormsg = '';
		// Set a couple of flags
		$inHeaders = true;
		$atStart = true;
		// Now start reading back the response
		while (!feof($fp)) {
			$line = fgets($fp, 4096);
			if ($atStart) {
				// Deal with first line of returned data
				$atStart = false;
				if (!preg_match('/HTTP\/(\\d\\.\\d)\\s*(\\d+)\\s*(.*)/', $line, $m)) {
					$this->errormsg = "Status code line invalid: " . htmlentities($line);
					$this->debug($this->errormsg);
					$this->debug("Line:", $line);
	
					// Hack: Request, der fehlgeschlagen ist, in Datei schreiben
					$filename = dirname(__FILE__).'/tmp/output'.rand(1,9999999).'.txt';
	
					if ($handle = fopen($filename, "w+")) {
						fwrite($handle, $request);
						fclose($handle);
					}
	
	
					return false;
				}
				$http_version = $m[1]; // not used
				$this->status = $m[2];
				$status_string = $m[3]; // not used
				$this->debug(trim($line));
				continue;
			}
			if ($inHeaders) {
				if (trim($line) == '') {
					$inHeaders = false;
					$this->debug('Received Headers', $this->headers);
					if ($this->headers_only) {
						break; // Skip the rest of the input
					}
					continue;
				}
				if (!preg_match('/([^:]+):\\s*(.*)/', $line, $m)) {
					// Skip to the next header
					continue;
				}
				$key = strtolower(trim($m[1]));
				$val = trim($m[2]);
				// Deal with the possibility of multiple headers of same name
				if (isset ($this->headers[$key])) {
					if (is_array($this->headers[$key])) {
						$this->headers[$key][] = $val;
					} else {
						$this->headers[$key] = array (
								$this->headers[$key],
								$val
						);
					}
				} else {
					$this->headers[$key] = $val;
				}
				continue;
			}
			// We're not in the headers, so append the line to the contents
			$this->content .= $line;
		}
		fclose($fp);
		// If data is compressed, uncompress it
		if (isset ($this->headers['content-encoding']) && $this->headers['content-encoding'] == 'gzip') {
			$this->debug('Content is gzip encoded, unzipping it');
			$this->content = substr($this->content, 10); // See http://www.php.net/manual/en/function.gzencode.php
			$this->content = gzinflate($this->content);
		}
		// If $persist_cookies, deal with any cookies
		if ($this->persist_cookies && isset ($this->headers['set-cookie'])) {
			$cookies = $this->headers['set-cookie'];
			if (!is_array($cookies)) {
				$cookies = array (
						$cookies
				);
			}
			foreach ($cookies as $cookie) {
				if (preg_match('/([^=]+)=([^;]+);/', $cookie, $m)) {
					$this->cookies[$m[1]] = $m[2];
				}
			}
			// Record domain of cookies for security reasons
			$this->cookie_host = $this->host;
		}
		// If $persist_referers, set the referer ready for the next request
		if ($this->persist_referers) {
			$this->debug('Persisting referer: ' . $this->getRequestURL());
			$this->referer = $this->getRequestURL();
		}
		// Finally, if handle_redirects and a redirect is sent, do that
		if ($this->handle_redirects) {
			if (++ $this->redirect_count >= $this->max_redirects) {
				$this->errormsg = 'Number of redirects exceeded maximum (' . $this->max_redirects . ')';
				$this->debug($this->errormsg);
				$this->redirect_count = 0;
				return false;
			}
			$location = isset ($this->headers['location']) ? $this->headers['location'] : '';
			$uri = isset ($this->headers['uri']) ? $this->headers['uri'] : '';
			if ($location || $uri) {
				$url = parse_url($location . $uri);
				// This will FAIL if redirect is to a different site
				return $this->get($url['path'], $url['query']);
			}
		}
		return true;
	}
	
	function doPutStreamRequest($data) {
		// Performs the actual HTTP request, returning true or false depending on outcome
		if (!$fp = fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout)) {
			// Set error message
			switch ($errno) {
				case -3 :
					$this->errormsg = 'Socket creation failed (-3)'; break;
				case -4 :
					$this->errormsg = 'DNS lookup failure (-4)'; break;
				case -5 :
					$this->errormsg = 'Connection refused or timed out (-5)'; break;
				default :
					$this->errormsg = 'Connection failed (' . $errno . ')';
					$this->errormsg .= ' ' . $errstr;
					$this->debug($this->errormsg);
			}
		}
		socket_set_timeout($fp, $this->timeout);
		$request = $this->buildPutStreamRequest($data);
		$this->debug('Request', $request);
		fwrite($fp, $request);
		// Reset all the variables that should not persist between requests
		$this->headers = array ();
		$this->content = '';
		$this->errormsg = '';
		// Set a couple of flags
		$inHeaders = true;
		$atStart = true;
		// Now start reading back the response
		while (!feof($fp)) {
			$line = fgets($fp, 4096);
			if ($atStart) {
				// Deal with first line of returned data
				$atStart = false;
				if (!preg_match('/HTTP\/(\\d\\.\\d)\\s*(\\d+)\\s*(.*)/', $line, $m)) {
					$this->errormsg = "Status code line invalid: " . htmlentities($line);
					$this->debug($this->errormsg);
					$this->debug("Line:", $line);
	
					// Hack: Request, der fehlgeschlagen ist, in Datei schreiben
					$filename = dirname(__FILE__).'/tmp/output'.rand(1,9999999).'.txt';
	
					if ($handle = fopen($filename, "w+")) {
						fwrite($handle, $request);
						fclose($handle);
					}
	
	
					return false;
				}
				$http_version = $m[1]; // not used
				$this->status = $m[2];
				$status_string = $m[3]; // not used
				$this->debug(trim($line));
				continue;
			}
			if ($inHeaders) {
				if (trim($line) == '') {
					$inHeaders = false;
					$this->debug('Received Headers', $this->headers);
					if ($this->headers_only) {
						break; // Skip the rest of the input
					}
					continue;
				}
				if (!preg_match('/([^:]+):\\s*(.*)/', $line, $m)) {
					// Skip to the next header
					continue;
				}
				$key = strtolower(trim($m[1]));
				$val = trim($m[2]);
				// Deal with the possibility of multiple headers of same name
				if (isset ($this->headers[$key])) {
					if (is_array($this->headers[$key])) {
						$this->headers[$key][] = $val;
					} else {
						$this->headers[$key] = array (
								$this->headers[$key],
								$val
						);
					}
				} else {
					$this->headers[$key] = $val;
				}
				continue;
			}
			// We're not in the headers, so append the line to the contents
			$this->content .= $line;
		}
		fclose($fp);
		// If data is compressed, uncompress it
		if (isset ($this->headers['content-encoding']) && $this->headers['content-encoding'] == 'gzip') {
			$this->debug('Content is gzip encoded, unzipping it');
			$this->content = substr($this->content, 10); // See http://www.php.net/manual/en/function.gzencode.php
			$this->content = gzinflate($this->content);
		}
		// If $persist_cookies, deal with any cookies
		if ($this->persist_cookies && isset ($this->headers['set-cookie'])) {
			$cookies = $this->headers['set-cookie'];
			if (!is_array($cookies)) {
				$cookies = array (
						$cookies
				);
			}
			foreach ($cookies as $cookie) {
				if (preg_match('/([^=]+)=([^;]+);/', $cookie, $m)) {
					$this->cookies[$m[1]] = $m[2];
				}
			}
			// Record domain of cookies for security reasons
			$this->cookie_host = $this->host;
		}
		// If $persist_referers, set the referer ready for the next request
		if ($this->persist_referers) {
			$this->debug('Persisting referer: ' . $this->getRequestURL());
			$this->referer = $this->getRequestURL();
		}
		// Finally, if handle_redirects and a redirect is sent, do that
		if ($this->handle_redirects) {
			if (++ $this->redirect_count >= $this->max_redirects) {
				$this->errormsg = 'Number of redirects exceeded maximum (' . $this->max_redirects . ')';
				$this->debug($this->errormsg);
				$this->redirect_count = 0;
				return false;
			}
			$location = isset ($this->headers['location']) ? $this->headers['location'] : '';
			$uri = isset ($this->headers['uri']) ? $this->headers['uri'] : '';
			if ($location || $uri) {
				$url = parse_url($location . $uri);
				// This will FAIL if redirect is to a different site
				return $this->get($url['path'], $url['query']);
			}
		}
		return true;
	}
	//by frsun  2012.10.18
	function buildDataRequest($data=array()) {
		$headers = array ();
		$headers[] = "{$this->method} {$this->path} HTTP/1.0"; // Using 1.1 leads to all manner of problems, such as "chunked" encoding
		$headers[] = "Host: {$this->host}";
		$headers[] = "User-Agent: {$this->user_agent}";
		foreach ($data as $key=>$value){
			$v= str_replace('+','%20',urlencode($value));
			$headers[] = "$key:$v";
		}
		if ($this->use_gzip) {
			$headers[] = "Accept-encoding: {$this->accept_encoding}";
		}
		$headers[] = "Accept-language: {$this->accept_language}";
		if ($this->referer) {
			$headers[] = "Referer: {$this->referer}";
		}
		// Cookies
		if ($this->cookies) {
			$cookie = 'Cookie: ';
			foreach ($this->cookies as $key => $value) {
				$cookie .= "$key=$value; ";
			}
			$headers[] = $cookie;
		}
		// Basic authentication
		if ($this->username && $this->password) {
			$headers[] = 'Authorization: BASIC ' . base64_encode($this->username . ':' . $this->password);
		}
	
		if ($this->files) {
			// file upload
			$data = "";
	
			srand((double) microtime() * 1000000);
			$boundary = "---------------------------" . substr(md5(rand(0, 32000)), 0, 10);
	
			$headers[] = "Content-Type: multipart/form-data; boundary=" . $boundary . "";
	
			// attach post vars
			foreach ($_POST as $name => $value) {
				$data .= "--$boundary\r\n";
				$data .= "Content-Disposition: form-data; name=\"" . $name . "\"\r\n";
				$data .= "\r\n" . $value . "\r\n";
				$data .= "--$boundary\r\n";
			}
			// and attach the files
			for ($i = 0; $i < count($this->files); $i++) {
				$file = $this->files[$i];
	
				$data .= "--".$boundary."\r\n";
				$data .= "Content-Disposition: form-data; name=\"" . $file['name'] . "\"; filename=\"" . $file['path'] . "\"\r\n";
				$data .= "Content-Type: " . $file['type'] . "\r\n\r\n";
				$fh = fopen($file['path'], "r");
				$data .= fread($fh, filesize($file['path'])) . "\r\n";
				fclose($fh);
				$data .= "--".$boundary."--\r\n";
			}

			$headers[] = "Content-length: " . strlen($data) . "\r\n";
			$request = implode("\r\n", $headers) . "\r\n\r\n" . $data;
	
		} else
			if ($this->postdata) {
		$headers[] = 'Content-Type: application/x-www-form-urlencoded';
		$headers[] = 'Content-Length: ' . strlen($this->postdata);
		$request = implode("\r\n", $headers) . "\r\n\r\n" . $this->postdata;
		} else {
			$headers[] = 'Content-Length: ' . strlen($this->postdata);
			$request = implode("\r\n", $headers) . "\r\n\r\n" . $this->postdata;
		}
		return $request;
	}
	
	function buildPutStreamRequest($data=array()) {
		$headers = array ();
		$headers[] = "{$this->method} {$this->path} HTTP/1.0"; // Using 1.1 leads to all manner of problems, such as "chunked" encoding
		$headers[] = "Host: {$this->host}";
		$headers[] = "User-Agent: {$this->user_agent}";
		foreach ($data as $key=>$value){
			$v= str_replace('+','%20',urlencode($value));
			$headers[] = "$key:$v";
		}
		if ($this->use_gzip) {
			$headers[] = "Accept-encoding: {$this->accept_encoding}";
		}
		$headers[] = "Accept-language: {$this->accept_language}";
		if ($this->referer) {
			$headers[] = "Referer: {$this->referer}";
		}
		// Cookies
		if ($this->cookies) {
			$cookie = 'Cookie: ';
			foreach ($this->cookies as $key => $value) {
				$cookie .= "$key=$value; ";
			}
			$headers[] = $cookie;
		}
		// Basic authentication
		if ($this->username && $this->password) {
			$headers[] = 'Authorization: BASIC ' . base64_encode($this->username . ':' . $this->password);
		}
	
		if ($this->files) {
			// file upload
			$data = "";
	
			srand((double) microtime() * 1000000);
			$boundary = "---------------------------" . substr(md5(rand(0, 32000)), 0, 10);
	
			$headers[] = "Content-Type: multipart/form-data; boundary=" . $boundary . "";
	
			// attach post vars
			foreach ($_POST as $name => $value) {
				$data .= "--$boundary\r\n";
				$data .= "Content-Disposition: form-data; name=\"" . $name . "\"\r\n";
				$data .= "\r\n" . $value . "\r\n";
				$data .= "--$boundary\r\n";
			}
	
			//附加数据
			foreach ($this->postdata as $key => $value){
				$data .= "--$boundary\r\n";
				$data .= "Content-Disposition: form-data; name=\"" . $key . "\"\r\n";
				$data .= "\r\n" . $value . "\r\n";
				$data .= "--$boundary\r\n";
			}
	
			// and attach the files
			for ($i = 0; $i < count($this->files); $i++) {
				$file = $this->files[$i];
	
				$data .= "--".$boundary."\r\n";
				$data .= "Content-Disposition: form-data; name=\"" . $file['name'] . "\"; filename=\"" . $file['path'] . "\"\r\n";
				$data .= "Content-Type: " . $file['type'] . "\r\n\r\n";
				$fh = fopen($file['path'], "r");
				$data .= fread($fh, filesize($file['path'])) . "\r\n";
				fclose($fh);
				$data .= "--".$boundary."--\r\n";
			}
	
			$headers[] = "Content-length: " . strlen($data) . "\r\n";
			$request = implode("\r\n", $headers) . "\r\n\r\n" . $data;
		} else
			if ($this->postdata) {
		$headers[] = 'Content-Type: application/x-www-form-urlencoded';
		$headers[] = 'Content-Length: ' . strlen($this->postdata);
		$request = implode("\r\n", $headers) . "\r\n\r\n" . $this->postdata;
		} else {
			$request = implode("\r\n", $headers) . "\r\n\r\n" . $this->postdata;
		}
		return $request;
	}
	
	function quickGetRequest($url,$data=array()) {
		$bits = parse_url($url);
		$host = $bits['host'];
		$port = isset ($bits['port']) ? $bits['port'] : 80;
		$path = isset ($bits['path']) ? $bits['path'] : '/';
		if (isset ($bits['query'])) {
			$path .= '?' . $bits['query'];
		}
		$client = new HttpClient($host, $port);
		if (!$client->getRequest($path,$data)) {
			return false;
		} else {
			$c=$client->getContent();
			$s=$client->getStatus();
			$r=new Result();
			$r->data=$c;
			$r->error=$s;
			$result=json_encode($r);
			return '{"result":'.$result.'}';
		}
	}
	
	function quickPutRequest($url, $data,$file=array()) {
		$bits = parse_url($url);
		$host = $bits['host'];
		$port = isset ($bits['port']) ? $bits['port'] : 80;
		$path = isset ($bits['path']) ? $bits['path'] : '/';
		$client = new HttpClient($host, $port);
		if (!$client->putRequest($path, $data,$file)) {
			return false;
		} else {
			$c=$client->getContent();
			$s=$client->getStatus();
			$r=new Result();
			$r->data=$c;
			$r->error=$s;
			$result=json_encode($r);
			return '{"result":'.$result.'}';
		}
	}
	
	function quickPostRequest($url,$data=array()) {
		$bits = parse_url($url);
		$host = $bits['host'];
		$port = isset ($bits['port']) ? $bits['port'] : 80;
		$path = isset ($bits['path']) ? $bits['path'] : '/';
		$client = new HttpClient($host, $port);
		if (!$client->postRequest($path,$data)) {
			return false;
		} else {
			$c=$client->getContent();
			$s=$client->getStatus();
			$r=new Result();
			$r->data=$c;
			$r->error=$s;
			$result=json_encode($r);
			return '{"result":'.$result.'}';
		}
	}
	
	function quickDeleteRequest($url,$data=array()) {
		$bits = parse_url($url);
		$host = $bits['host'];
		$port = isset ($bits['port']) ? $bits['port'] : 80;
		$path = isset ($bits['path']) ? $bits['path'] : '/';
		$client = new HttpClient($host, $port);
		if (!$client->deleteRequest($path,$data)) {
			return false;
		} else {
			$c=$client->getContent();
			$s=$client->getStatus();
			$r=new Result();
			$r->data=$c;
			$r->error=$s;
			$result=json_encode($r);
			return '{"result":'.$result.'}';
		}
	}
	
	//-----------------------------
	
	function debug($msg, $object = false) {
		if ($this->debug) {
			print '<div style="border: 1px solid red; padding: 0.5em; margin: 0.5em;"><strong>HttpClient Debug:</strong> ' . $msg;
			if ($object) {
				ob_start();
				print_r($object);
				$content = htmlentities(ob_get_contents());
				ob_end_clean();
				print '<pre>' . $content . '</pre>';
			}
			print '</div>';
		}
	}
}
?>