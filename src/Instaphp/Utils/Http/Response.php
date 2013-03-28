<?php

/**
 * The MIT License (MIT)
 * Copyright © 2013 Randy Sesser <randy@instaphp.com>
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the “Software”), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * 
 * @author Randy Sesser <randy@instaphp.com>
 * @filesource
 */

namespace Instaphp\Utils\Http;

/**
 * A simple HTTP resposne object
 * 
 * Parses the headers from the raw response when included in cURL requests.
 *
 * @author Randy Sesser <randy@instaphp.com>
 * @license http://instaphp.mit-license.org MIT License 
 * @package Instaphp
 * @subpackage Utils
 * @version 2.0-dev
 */
class Response
{
	/** @var string The request URL that generated this response. */
	public $request_url = '';
	
	/** @var array Request parameters that were passed to the URL. */ 
	public $request_params = [];
	
	/** @var string The request method */
	public $request_method = '';
	
	/** @var int The HTTP status code returned from the request. */
	public $code = 0;
	
	/** @var string The HTTP status message returned from the request. */
	public $status = '';
	
	/** @var array Information about the request (from curl_getinfo()). */
	public $info = [];
	
	/** @var array The response headers. */
	public $headers = [];
	
	/** @var string The body of the response.  */
	public $body = '';
	
	/** @var string The full response including headers.  */
	public $raw = '';
	
	/**
	 * Constructs a new Response object based on the raw response
	 * @param string $response The raw response from the HTTP request
	 */
	public function __construct($response)
	{
		$this->raw = $response;
		$head = '';
		$body = '';
		
		list($head, $body) = explode("\r\n\r\n", $response, 2);
		
		if (false !== ($pos = stripos($head, '100 continue'))) {
			list($head, $body) = explode("\r\n\r\n", $body, 2);
		}
		
		$this->body = $body;
		
		$headers = explode("\r\n", $head);
		
		foreach ($headers as $header) {
			if (preg_match('/^(?<protocol>HTTPS?)\/(?<version>\d\.\d)\s(?<code>[\d]{3})\s(?<status>.+)/', $header, $m)) {
				$this->code = $m['code'];
				$this->status = $m['status'];
				continue;
			}
			$parts = explode(':', $header);
			$this->headers[$parts[0]] = trim($parts[1]);
		}
	}
}

