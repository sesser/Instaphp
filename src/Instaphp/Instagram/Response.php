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

namespace Instaphp\Instagram;

/**
 * A generic objec representing a response from the Instagram API
 *
 * @author Randy Sesser <randy@instaphp.com>
 * @license http://instaphp.mit-license.org MIT License 
 * @package Instaphp
 * @version 2.0-dev
 */
class Response
{
	/**
	 * The HTTP header in the response that holds the rate limit for this request
	 */
	const RATE_LIMIT_HEADER = 'X-Ratelimit-Limit';
	
	/**
	 * The HTTP header in the response that holds the rate limit remaingin
	 */
	const RATE_LIMIT_REMAINING_HEADER = 'X-Ratelimit-Remaining';
	
	/** @var string The request url */
	public $url = '';
	
	/** @var array The request parameters */
	public $params = [];
	
	/** @var string The request method */
	public $method = '';
	
	/** @var array The data collection  */
	public $data = [];
	
	/** @var array The meta collection */
	public $meta = [];
	
	/** @var array The pagination collection */
	public $pagination = [];
	
	/** @var string The access_token from OAuth requests */
	public $access_token = NULL;
	
	/** @var array The user collection from OAuth requests */
	public $user = [];
	
	/** @var array The HTTP headers returned from API. */
	public $headers = [];
	
	/** @var string The raw JSON response from the API */
	public $json = NULL;
	
	/** @var int The number of requests you're allowed to make to the API */
	public $limit = 0;
	
	/** @var int The number of requests you have remaining for this client/access_token */
	public $remaining = 0;
	
	public function __construct(\Sesser\Scurl\Response $response)
	{
		$this->headers = $response->headers;
		$this->url = $response->request_url;
		$this->params = $response->request_parameters;
//		$this->method = $response->request_method;
		
		$this->json = $response->body;
		$json = json_decode($this->json, TRUE);
		$this->data = isset($json['data']) ? $json['data'] : NULL;
		$this->meta = isset($json['meta']) ? $json['meta'] : NULL;
		$this->pagination = isset($json['pagination']) ? $json['pagination'] : NULL;
		if (isset($json['access_token'])) {
			$this->access_token = $json['access_token'];
			$this->user = $json['user'];
		}
		$this->limit = (isset($this->headers[self::RATE_LIMIT_HEADER])) ? $this->headers[self::RATE_LIMIT_HEADER] : 0;
		$this->remaining = (isset($this->headers[self::RATE_LIMIT_REMAINING_HEADER])) ? $this->headers[self::RATE_LIMIT_REMAINING_HEADER] : 0;
	}

}

