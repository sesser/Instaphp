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

namespace Instaphp {

    use Instaphp\Config;

    /**
     * Request
     * The Request class performs simple curl requests to a URL optionally passing
     * parameters on the querystring. Currently, it supports GET,POST and DELETE requests.
     * @package Instaphp
     * @version 1.0
     * @author randy sesser <randy@instaphp.com>
     */
    class Request
    {

        /**
         * Associative array of key/value pairs to pass to the Instagram API
         * @var Array
         * @access public
         */
        public $parameters = array();
        /**
         * The URL in which to make the request
         * @var String
         * @access public
         */
        public $url = null;
        /**
         * A var to store whether or not to use curl
         * @var boolean
         * @access private
         */
        private $useCurl = false;
        /**
         * A curl handle
         * @var Handle
         * @access private
         */
        private $ch = null;
        /**
         * Default options to pass to cURL
         * @var Array
         * @access private
         */
        private $curl_opts = array(
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_ENCODING => ''
        );

		/**
		 * Headers to pass in the request
		 *
		 * @var array
		 * @access private
		 **/
		private $headers = null;
		
        /**
         * Max number of redirects to follow a request before giving up
         * @var int
         * @access private
         * @static
         */
        private static $max_redirects = 3;
        
        /**
         * The constructor contructs
         * @param string $url A URL in which to create a new request (optional)
         * @param Array $params An associated array of key/value pairs to pass to said URL (optional)
         */
        public function __construct($url = null, $params = array())
        {
            if (isset(Config::Instance()->Endpoint['timeout']))
                $this->curl_opts[CURLOPT_TIMEOUT] = (int)Config::Instance()->Endpoint['timeout'];
                
            $this->curl_opts[CURLOPT_USERAGENT] = 'Instaphp/v' . INSTAPHP_VERSION;

            //-- this is an interesting hack to make curl+ssl+windows follow redirects
            //-- without skipping verification. For some reason, the version of libcurl/curl
            //-- included with ZendServer CE doesn't use the systems CA bundle, so, we specify
            //-- the path to the cert here (via config setting)
            if (isset(Config::Instance()->Instaphp->CACertBundlePath) && !empty(Config::Instance()->Instaphp->CACertBundlePath)) {
                $this->curl_opts[CURLOPT_SSL_VERIFYPEER] = true;
                $this->curl_opts[CURLOPT_SSL_VERIFYHOST] = 2;
                $this->curl_opts[CURLOPT_SSLVERSION] = 3;
                $this->curl_opts[CURLOPT_CAINFO] = Config::Instance()->Instaphp->CACertBundlePath;
            }

            $this->useCurl = self::HasCurl();

            $this->parameters = $params;
            $this->url = $url;

			$this->curl_opts[CURLOPT_HTTPHEADER] = array(
				"Connection: keep-alive",
				"Keep-Alive: 300",
				"Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7",
				"Accept-Language: en-us,en;q=0.5"
			);
        }

        /**
         * Used to close the current curl handle
         */
        public function __destruct()
        {
            //-- close the curl handle. we're done with it
            if (null != $this->ch)
                curl_close($this->ch);
        }

        /**
         * Makes a GET request
         * @param string $url A URL in which to make a GET request
         * @param Array $params An associative array of key/value pairs to pass to said URL
         * @return Request
         */
        public function Get($url = null, $params = array())
        {
            if (null !== $url)
                $this->url = $url;
			
			if (!empty($params))
				$this->parameters = $params;

            $this->response = $this->GetResponse();
            return $this;
        }

        /**
         * Makes a POST request
         * @param string $url A URL in which to make a POST request
         * @param Array $params An associative array of key/value pairs to pass to said URL
         * @return Request
         */
        public function Post($url = null, $params = array())
        {
            if (null !== $url)
                $this->url = $url;

			if (!empty($params))
				$this->parameters = $params;

            $this->response = $this->GetResponse('POST');
            return $this;
        }

        /**
         * Makes a PUT request (currently unused)
         * @param string $url A URL in which to make a PUT request
         * @param Array $params An associative array of key/value pairs to pass to said URL
         * @return void
         */
        public function Put($url = null, $params = array())
        {
            
        }

        /**
         * Makes a DELETE request
         * @param string $url A URL in which to make a DELETE request
         * @param Array $params An associative array of key/value pairs to pass to said URL
         * @return Request
         */
        public function Delete($url = null, $params = array())
        {
            if (null !== $url)
                $this->url = $url;

			if (!empty($params))
				$this->parameters = $params;

            $this->response = $this->GetResponse('DELETE');
            return $this;
        }

        /**
         * Makes a request
         * @param string $url A URL in which to make a GET request
         * @param Array $params An associative array of key/value pairs to pass to said URL
         * @access private
         * @return Response
         */
        private function GetResponse($method = 'GET')
        {
            //-- if there's no url, can't make a request
            if (null == $this->url)
                trigger_error('No URL to make a request', E_USER_ERROR);

            //-- since there's no option to use anything other curl, this check is kinda useless
            //-- I had high hopes with this one using sockets and whatnot, but alas, time is of 
            //-- the essence... in internet time
            if ($this->useCurl) {
                //-- no curl handle? create one
                if ($this->ch === null)
                    $this->ch = curl_init();


                $opts = $this->curl_opts;
                $query = '';
                switch (strtolower($method)) {
                    case 'post':
                        $opts[CURLOPT_POST] = true;
                        $opts[CURLOPT_POSTFIELDS] = $this->parameters;
                        break;
                    case 'delete':
                        $opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                        foreach ($this->parameters as $key => $val)
                            $query .= ((strlen($query) == 0) ? '?' : '&') . $key . '=' . urlencode($val);
                        break;
                    default:
                        foreach ($this->parameters as $key => $val)
                            $query .= ((strlen($query) == 0) ? '?' : '&') . $key . '=' . urlencode($val);
                        break;
                }
				$this->url .= $query;
                $opts[CURLOPT_URL] = $this->url;

				$response = new Response;
				
                if (curl_setopt_array($this->ch, $opts)) {
                    if (false !== ($res = curl_exec($this->ch))) {
						$response->info = curl_getinfo($this->ch);
						$response->json = $res;
						$response = Response::Create($this, &$response);
                    } else {                        
                        $response->error = new Error('cURLError', curl_errno($this->ch), curl_error($this->ch), $opts[CURLOPT_URL]);
                    }
                }

                return $response;
            }
        }

        /**
         * Checks to see if cURL extension is available
         * @access private
         * @return boolean
         */
        private static function HasCurl()
        {
            return function_exists('curl_init');
        }
        
        /**
         * Determines whether or not curl will follow redirects over SSL
         * See the constructor for details, but there are cases in which
         * if curl can't verify the certificate of an SSL request, AND
         * PHP is in safe_mode OR there are open_basedir restrictions, it will
         * not follow a redirect. There's a fix for this that involves
         * parsing all the response headers from a request and detecting
         * a Location header, but that's kind of a hack as it bypasses the
         * whole point of SSL. This method left for posterity. Or something...
         *
         * @return boolean
         * @access private
         **/
        private function WillFollowRedirects()
        {
            $open_basedir = ini_get('open_basedir');
            $safe_mode = strtolower(ini_get('safe_mode'));
            if (empty($open_basedir) && $safe_mode == 'off') {
                return true;
            }
            return false;
        }

    }
}