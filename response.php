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
    /**
     * The Response object.
     * This is the object passed back to the caller of this framework. It mimcs
     * Instagram's JSON objects returned from most (if not all) of its current
     * endpoints. Not all properties will be populated, so isset() should be used
     * when dealing with a response object's properties
     * @package Instaphp
     * @version 1.0
     * @author randy sesser <randy@instaphp.com>
     */
    class Response
    {
    	/**
    	 * @const RATELIMIT The HTTP header key for the Instagram rate limit
    	 */
    	const RATELIMIT = 'X-Ratelimit-Limit';
    	
    	/**
    	 * @const RATELIMIT_REMAINGING The HTTP header key for the remaining calls left
    	 */
    	const RATELIMIT_REMAINGING = 'X-Ratelimit-Remaining';
    	
		/**
		 * Technical information about the http response
		 *
		 * @var array
		 * @access public
		 */
		public $info;
        /**
         * HTTP headers returned from Instagram
         *
         * @var array
         * @access public
         */
        public $headers = array();
        /**
         * The meta "object" (contains a status code. 200 when successful)
         * @var object
         * @access public
         */
        public $meta = null;
        /**
         * The data "object" contains everything. Too much to list here.
         * See {@link https://api.instagram.com/developer/ Instagram Developer API}
         * @var object
         * @access public
         */
        public $data = null;
        /**
         * The pagination "object" is not your typical pagination
         * @var object
         * @access public
         */
        public $pagination = null;
        /**
         * If an error occurred, this will be populated. Check here first.
         * <code>
         * if (!empty(\$response->error)) {
         *  echo \$response->error->message;
         * }
         * </code>
         * @var Error
         * @access public
         */
        public $error = null;
        /**
         * When authenticating, this is populated with the access token and basic
         * user info returned from the API
         * @var object
         * @access public
         */
        public $auth = null;
		/**
		 * For embeded calls
		 *
		 * @var object
		 * @access public
		 **/
		public $embed = null;

        /**
         * This is the raw JSON response returned from the API. Usefull if 
         * you just want a "passthrough" situation or perhaps you want to embed
         * the JSON string in the page and parse it with JavaScript.
         * <code>
         * var response = JSON.parse('<?php echo \$response->json ?>');
         * </code>
         * @var string
         * @access public 
         */
        public $json = '';

        public function __construct()
        {
            
        }

        /**
         * A convenience method to parse the response text and build a Response object
         * @access public 
         * @static
         * @param string $responseText The response from the API call
         * @param string $url The url used to generate the Response object
         * @return Response
         */
        public static function Create(Request $request, Response &$response)
        {
            $obj = json_decode($response->json);

			//-- for embeded calls, just return the embeded object
			if (isset($obj->{'provider_url'}) && !empty($obj->{'provider_url'})) {
				$response->embed = $obj;
				return $response;
			}
				
            if (empty($obj)) {
				$error = new Error;
				$error->type = 'cURLResponseError';
				$error->code = $response->info['http_code'];
				$error->url = $response->info['url'];
                $error->headers = $response->headers;
				switch ($error->code)
				{
					case 505:
						$error->message = 'HTTP version not supported? Weird.';
						break;
					case 504:
						$error->message = 'Gateway timeout. Sorry.';
						break;
					case 503:
						$error->message = 'The API is currently unavailable';
						break;
					case 502:
						$error->message = 'Baaaaaaaaad gateway!';
						break;
					case 501:
						$error->message = 'Sorry, not implemented... YET.';
						break;
					case 500:
						$error->message = 'Whoops! API just barfed on your new shoes.';
						break;
					case 405:
						$error->message = 'Method not allowed.';
						break;
					case 404:
						$error->message = 'Received a 404 from the API';
						break;
					case 403:
						$error->message = 'The API says you are forbidden';
						break;
					case 402:
						$error->message = 'The API claims you own them money.';
						break;
					case 401:
						$error->message = 'The API says you are unauthorized.';
						break;
					case 400:
						$error->message = 'POBR... Plain Old Bad Request.';
						break;
					default:
						$error->message = 'Unknown error ocurred making this request';
						break;
				}
				$response->error = $error;
            }

            if (isset($obj->{'error_message'})) {
                $response->error = new Error($obj->{'error_type'}, $obj->{'code'}, $obj->{'error_message'}, $response->info['url']);
                $response->error->headers = $response->headers;
            }

            if (isset($obj->{'access_token'})) {
                $response->auth = new \stdClass();
                $response->auth->access_token = $obj->{'access_token'};
                $response->auth->user = $obj->{'user'};
            }

            if (isset($obj->{'meta'}))
                $response->meta = $obj->{'meta'};

            if (isset($obj->{'meta'}) && $obj->{'meta'}->code !== 200) {
                $response->error = new Error($response->meta->error_type, $response->meta->code, $response->meta->error_message, $response->info['url']);
                $query = '';
                
            }

            if (isset($obj->{'data'}))
                $response->data = $obj->{'data'};

            if (isset($obj->{'pagination'}))
                $response->pagination = $obj->{'pagination'};

            return $response;
        }
        
        /**
         * A convenience method to get the current Ratelimit for the request
         * @return integer The number of allowed API calls per hour
         */
        public function getRatelimit()
        {
        	return isset($this->headers[self::RATELIMIT]) ? $this->headers[self::RATELIMIT] : 0;
        }
        
        /**
         * A convenience method to get the remaining API calls left this hour
         * for this client_id or access_code
         * 
         * Note: the rate limit is technically by hour, but is calculated
         * in 5-minute bucket increments.
         * @link http://j.mp/JGMauX
         * @return integer The number of requests left for this client_id or access_token
         */
        public function getRatelimitRemaining()
        {
        	return isset($this->headers[self::RATELIMIT_REMAINGING]) ? $this->headers[self::RATELIMIT_REMAINGING] : 0;
        }

		private static function fixNonUtf8Chars($data)
		{ 
		    $aux = str_split($data); 
		    foreach($aux as $a) { 
		        $a1 = urlencode($a); 
		        $aa = explode("%", $a1); 

		        foreach($aa as $v)
		            if($v!="")
		                if(hexdec($v)>127)
		                	$data = str_replace($a,"&#".hexdec($v).";",$data); 

		    } 

		    return $data; 
		}
    }

    /**
     * Error Object
     * 
     *
     * @package Instaphp
     * @version 1.0
     * @author randy sesser <randy@instaphp.com>
     */
    class Error
    {
        /**
         * Error Type
         * @var string
         * @access public
         */
        public $type = null;
        /**
         * Error Code
         * @var int
         * @access public
         */
        public $code = null;
        /**
         * Error Message
         * @var string
         * @access public
         */
        public $message = null;
		/**
		 * The url associated with this error
		 *
		 * @var string
		 * @access public
		 **/
		public $url = null;

        /**
         * HTTP Headers returned from Instagram
         *
         * @var array
         * @access public
         */
        public $headers = array();
		
        /**
         * The constructor constructs
         * @param string $type The error type
         * @param int $code The error code
         * @param string $message The error message
         * @return Error
         * @access public
         */
        public function __construct($type = null, $code = null, $message = null, $url = null)
        {
            $this->type = $type;
            $this->code = $code;
            $this->message = $message;
			$this->url = $url;
        }
    }

}