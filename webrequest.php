<?php
/**
 * Instaphp
 * 
 * Copyright (c) 2011 randy sesser <randy@instaphp.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * 
 * @author randy sesser <randy@instaphp.com>
 * @copyright 2011, randy sesser
 * @license http://www.opensource.org/licenses/mit-license The MIT License
 * @package Instaphp
 * @filesource
 */
namespace Instaphp
{
	class WebRequest
	{
		/**
		 *
		 * @var resource A cURL multi handle resource
		 * @access private
		 */
		private $mh = null;
		
		/**
		 *
		 * @var array Array of active requests
		 * @access private
		 */
		private $_requests;
		
		/**
		 *
		 * @var array Array of stored responses
		 * @access private
		 */
		private $_responses;
		/**
		 *
		 * @var array Array of common cURL options
		 * @access private
		 */
		private $_options = array(
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_ENCODING => ''
		);
		
		/**
		 *
		 * @var WebRequest An instance of WebRequest (aka Mr. Singleton)
		 * @access private
		 */
		static $_instance = null;
		
		/**
		 * Constructor is private final. Can only be instantiated via WebRequest::Instance()
		 * @access private
		 * @final
		 */
		private final function __construct()
		{
			$this->mh = curl_multi_init();
			
			if (isset(Config::Instance()->Endpoint['timeout']))
				$this->_options[CURLOPT_TIMEOUT] = Config::Instance()->Endpoint['timeout'];
			
			$this->_options[CURLOPT_USERAGENT] = 'Instaphp/v' . INSTAPHP_VERSION;

			//-- this is an interesting hack to make curl+ssl+windows follow redirects
            //-- without skipping verification. For some reason, the version of libcurl/curl
            //-- included with ZendServer CE doesn't use the systems CA bundle, so, we specify
            //-- the path to the cert here (via config setting)
            if (isset(Config::Instance()->Instaphp->CACertBundlePath) && !empty(Config::Instance()->Instaphp->CACertBundlePath)) {
                $this->_options[CURLOPT_SSL_VERIFYPEER] = true;
                $this->_options[CURLOPT_SSL_VERIFYHOST] = 2;
                $this->_options[CURLOPT_SSLVERSION] = 3;
                $this->_options[CURLOPT_CAINFO] = Config::Instance()->Instaphp->CACertBundlePath;
            }
			
			$this->_options[CURLOPT_HTTPHEADER] = array(
				"Connection: keep-alive",
				"Keep-Alive: 300",
				"Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7",
				"Accept-Language: en-us,en;q=0.5"
			);
			
			$this->_requests = array();
			$this->_responses = array();
		}
		
		/**
		 * Instantiates a new WebRequest if one does not already exist
		 * 
		 * @return WebRequest A single instance of WebRequest 
		 * @access public
		 * @static
		 */
		public static function Instance()
		{
			if (null == static::$_instance)
				static::$_instance = new self();
			return static::$_instance;
		}
		
		/**
		 * Convenience method for http GET requests
		 * 
		 * @param string $url The url to Get
		 * @param array $parameters An array of key/value pairs to pass
		 * @return WebRequestManager A WebRequestManager to manage the current request 
		 * @access public
		 */
		public function Get($url, $parameters = array())
		{
			return $this->Create($url, "GET", $parameters);
		}
		
		/**
		 * Convenience method for http POST requests
		 * 
		 * @param string $url The url to POST
		 * @param array $parameters An array of key/value pairs to pass
		 * @return WebRequestManager A WebRequestManager to manage the current request 
		 * @access public
		 */
		public function Post($url, $parameters = array())
		{
			return $this->Create($url, "POST", $parameters);
		}
		
		/**
		 * Convenience method for http DELETE requests
		 * 
		 * @param string $url The url to DELETE
		 * @param array $parameters An array of key/value pairs to pass
		 * @return WebRequestManager A WebRequestManager to manage the current request 
		 * @access public
		 */
		public function Delete($url, $parameters = array())
		{
			return $this->Create($url, "DELETE", $parameters);
		}
		
		/**
		 * Creates a new cURL reuest and adds to the request queue for processing
		 * @param string $url The url in which to make the request
		 * @param string $method The method used to make the request [GET|POST|DELETE]
		 * @param array $parameters Array of key/value pairs to pass to the request
		 * @return mixed A WebRequestManager to manage the current request on success, Error on failure
		 * @access public
		 */
		public function Create($url, $method = 'GET', $parameters = array())
		{
			$ch = curl_init();
			$key= (string)$ch;
			$query = '';
			$res = null;
			
			$options = $this->_options;
			
			foreach ($parameters as $k => $v)
				$query .= ((strlen ($query) == 0) ? "":"&") . sprintf('%s=%s', $k, urlencode($v));

			switch (strtolower($method))
			{
				case 'post':
					$options[CURLOPT_POST] = true;
					$options[CURLOPT_POSTFIELDS] = $query;
					break;
				case 'delete':
					$options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
					$url .= '?' . $query;
					break;
				default:
					$url .= '?' . $query;
					break;
			}
			
			$options[CURLOPT_URL] = $url;
			curl_setopt_array($ch, $options);
			
			$this->_requests[$key] = $ch;
			
			$res = curl_multi_add_handle($this->mh, $this->_requests[$key]);
			if ($res == CURLM_OK) {
				curl_multi_exec($this->mh, $active);
				return new WebRequestManager($key);
			}
			return new Error('cURLError', curl_error($ch), curl_errno($ch), $options[CURLOPT_URL]);
		}
		
		/**
		 *
		 * @param string $key The key used to get the current request from the queue
		 * @return WebResponse  A WebResponse for the $key
		 * @access public
		 */
		public function GetResponse($key = null)
		{
			if (isset($this->_responses[$key]))
				return $this->_responses[$key];
			
			$running = null;
			
			do {
				$res = curl_multi_exec($this->mh, $current);
				if (null !== $running && $current != $running) {
					$this->store();	
					
					if (isset($this->_responses[$key]))
						return $this->_responses[$key];
					
				}
				$running = $current;
			} while ($current > 0);
			
			return false;
		}
		
		/**
		 * Runs through the request queue and processes completed requests
		 * @access private
		 */
		private function store()
		{
			while ($finished = curl_multi_info_read($this->mh, $messages)) {
				$key = (string)$finished["handle"];
				$this->_responses[$key] = new WebResponse(curl_multi_getcontent($finished["handle"]), curl_getinfo($finished["handle"]));
				curl_multi_remove_handle($this->mh, $finished["handle"]);
			}
		}
	}
	
	class WebRequestManager
	{
		private $key;
		private $request;
		
		public function __construct($key)
		{
			$this->key = $key;
			$this->request = WebRequest::Instance();
		}
		
		public function __get($name)
		{
			$response = $this->request->GetResponse($this->key);
			return $response->{$name};
		}
	}
	
	class WebResponse
	{
		public $Content;
		public $Info;
		
		public function __construct($content = null, $info = null)
		{
			$this->Content = $content;
			$this->Info = $info;
		}
	}
}