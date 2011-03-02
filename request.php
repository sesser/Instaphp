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
         * A Response object returned from the server
         * @var Instaphp\Response
         * @access public
         */
        public $response = null;
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
         * Our configuration object
         * @var Instaphp\Config
         * @access protected
         */
        protected $config = null;
        
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
            $this->config = Config::Instance();
            if (isset($this->config->Endpoint['timeout']))
                $this->curl_opts[CURLOPT_TIMEOUT] = (int)$this->config->Endpoint['timeout'];
                
            $this->curl_opts[CURLOPT_USERAGENT] = 'Instaphp/v' . INSTAPHP_VERSION;
            if (isset($this->config->Instaphp->CACertBundlePath) && !empty($this->config->Instaphp->CACertBundlePath)) {
                // echo $this->config->Instaphp->CACertBundlePath;
                $this->curl_opts[CURLOPT_SSL_VERIFYPEER] = true;
                $this->curl_opts[CURLOPT_SSL_VERIFYHOST] = 2;
                $this->curl_opts[CURLOPT_SSLVERSION] = 3;
                $this->curl_opts[CURLOPT_CAINFO] = $this->config->Instaphp->CACertBundlePath;
            }

            $this->useCurl = self::HasCurl();
            //-- We always pass the client_id
            $this->parameters['client_id'] = $this->config->Instagram->ClientId;

            if (!empty($params))
                $this->parameters = array_merge($this->parameters, $params);

            $this->url = $url;
        }

        /**
         * Used to close the current curl handle
         */
        public function __destruct()
        {
            if (null != $this->ch)
                curl_close($this->ch);
        }

        /**
         * Makes a GET request
         * @param string $url A URL in which to make a GET request
         * @param Array $params An associative array of key/value pairs to pass to said URL
         * @return Instaphp\Request
         */
        public function Get($url = null, $params = array())
        {
            if (null !== $url)
                $this->url = $url;

            if (!empty($params))
                $this->parameters = array_merge($this->parameters, $params);

            $this->response = $this->GetResponse();
            return $this;
        }

        /**
         * Makes a POST request
         * @param string $url A URL in which to make a POST request
         * @param Array $params An associative array of key/value pairs to pass to said URL
         * @return Instaphp\Request
         */
        public function Post($url = null, $params = array())
        {
            if (null !== $url)
                $this->url = $url;
            if (!empty($params))
                $this->parameters = array_merge($this->parameters, $params);

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
         * @return Instaphp\Request
         */
        public function Delete($url = null, $params = array())
        {
            if (null !== $url)
                $this->url = $url;
            if (!empty($params))
                $this->parameters = array_merge($this->parameters, $params);

            $this->response = $this->GetResponse('DELETE');
            return $this;
        }

        /**
         * Makes a request
         * @param string $url A URL in which to make a GET request
         * @param Array $params An associative array of key/value pairs to pass to said URL
         * @access private
         * @return Instaphp\Response
         */
        private function GetResponse($method = 'GET')
        {
            $this->response = new Response();
            if (null == $this->url)
                trigger_error('No URL to make a request', E_USER_ERROR);

            if ($this->useCurl) {

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
                            $query .= ( (strlen($query) == 0) ? '?' : '&') . $key . '=' . urlencode($val);
                        break;
                    default:
                        foreach ($this->parameters as $key => $val)
                            $query .= ( (strlen($query) == 0) ? '?' : '&') . $key . '=' . urlencode($val);
                        break;
                }
                $opts[CURLOPT_URL] = $this->url . $query;
                if (curl_setopt_array($this->ch, $opts)) {
                    if (false !== ($res = curl_exec($this->ch))) {
                        $response = Response::FromResponseText($res);
                        $response->requestUrl = $this->url.$query;
                    } else {
                        trigger_error("cURL error #" . curl_errno($this->ch) . ' - ' . curl_error($this->ch), E_USER_ERROR);
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