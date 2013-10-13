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
 * The base Instagram API object.
 * 
 * All APIs inherit from this base class. It provides helper methods for making
 * {@link \Instaphp\Utils\Http\Http HTTP} requests and handling errors returned
 * from API requests.
 *
 * @author Randy Sesser <randy@instaphp.com>
 * @license http://instaphp.mit-license.org MIT License 
 * @package Instaphp
 * @version 2.0-dev
 */
class Instagram
{
	/** @var array The configuration for Instaphp */
	protected $config = [];
	
	/** @var string The client_id for requests */
	protected $client_id = '';
	
	/** @var string The client_secret for requesting access_tokens */
	protected $client_secret = '';
	
	/** @var string The access_token for authenticated requests */
	protected $access_token = '';
	
	/** @var array The currently authenticated user */
	protected $user = [];
	
	/** @var \Sesser\Scurl\Scurl The Http object for making requests to the API */
	protected $http = NULL;
	
	public function __construct(array $config)
	{
		$this->config = $config;
		$this->client_id = $config['client_id'];
		$this->client_secret = $config['client_secret'];
		$this->http = new \Sesser\Scurl\Scurl([
			'options' => [
				'user-agent' => $this->config['http_useragent'],
				'timeout' => $this->config['http_timeout'],
				'connect_timeout' => $this->config['http_connect_timeout']
			]
		]);
	}
	
	/**
	 * Set the access_token for all future requests made with the current instance
	 * @param string $access_token A valid access_token
	 * @return void
	 */
	public function setAccessToken($access_token)
	{
		$this->access_token = $access_token;
	}
	
	/**
	 * Get the access_token currently in use
	 * @return string
	 */
	public function getAccessToken()
	{
		return $this->access_token;
	}
	
	/**
	 * Checks the existance of an access_token and assumes the user is logged in
	 * and has authorized this site
	 * @return boolean
	 */
	public function isAuthorized()
	{
		return !empty($this->access_token);
	}
	
	/**
	 * Makes a GET request to the API
	 * @param string $path The path of the request
	 * @param array $params Parameters to pass to the API
	 * @param array $headers Additional headers to pass in the HTTP call
	 * @return \Instaphp\Instagram\Response
	 */
	protected function get($path, array $params = [], array $headers = [])
	{
		$response = $this->http->get($this->buildPath($path), $this->prepare($params), ['headers' => $headers]);
		return $this->parseResponse($response);
	}
	
	/**
	 * Makes a POST request to the API
	 * @param string $path The path of the request
	 * @param array $params Parameters to pass to the API
	 * @param array $headers Additional headers to pass in the HTTP call
	 * @return \Instaphp\Instagram\Response
	 */
	protected function post($path, array $params = [], array $headers = [])
	{
		$response = $this->http->post($this->buildPath($path), $this->prepare($params), ['headers' => $headers]);
		return $this->parseResponse($response);
	}
	
	/**
	 * Makes a DELETE request to the API
	 * @param string $path The path of the request
	 * @param array $params Parameters to pass to the API
	 * @param array $headers Additional headers to pass in the HTTP call
	 * @return \Instaphp\Instagram\Response
	 */
	protected function delete($path, array $params = [], array $headers = [])
	{
		$response = $this->http->delete($this->buildPath($path), $this->prepare($params), ['headers' => $headers]);
		return $this->parseResponse($response);
	}
	
	/**
	 * Simply prepares the parameters being passed. Automatically set the client_id
	 * unless there is an access_token, in which case it is added instead
	 * @param array $params The list of parameters to perpare for a request
	 * @return array The prepared parameters
	 */
	private function prepare(array $params)
	{
		$params['client_id'] = $this->client_id;
		if (!empty($this->access_token)) {
			unset($params['client_id']);
			$params['access_token'] = $this->access_token;
		}
		return $params;
	}
	
	/**
	 * Adds the api_version to the beginning of the path
	 * @param string $path
	 * @param bool $add_version
	 * @return string Returns the corrected path
	 */
	protected function buildPath($path, $add_version = true)
	{
		$base = sprintf('%s://%s', $this->config['api_protocol'], $this->config['api_host']);
		if (empty($path))
			return $base . '/';
		
		if (substr($path, 0, 1) !== '/')
			$path = '/' . $path;
		
		if ($add_version && !preg_match('/^\/'.$this->config['api_version'].'/', $path))
			$path = '/' . $this->config['api_version'] . $path;
		
		return $base.$path;
	}
	
	/**
	 * Parses both the {@link \Instaphp\Utils\Http\Response HTTP Response} and
	 * the {@link \Instaphp\Instagram\Response Instagram Response} and scans them
	 * for errors and throws the apropriate exception. If there's no errors,
	 * this method returns the Instagram Response object.
	 * 
	 * @param \Sesser\Scurl\Response $response
	 * @return \Instaphp\Instagram\Response
	 * @throws \Instaphp\Exceptions\OAuthParameterException
	 * @throws \Instaphp\Exceptions\OAuthRateLimitException
	 * @throws \Instaphp\Exceptions\APINotFoundError
	 * @throws \Instaphp\Exceptions\APINotAllowedError
	 * @throws \Instaphp\Exceptions\APIInvalidParametersError
	 * @throws \Instaphp\Exceptions\HttpException
	 */
	private function parseResponse(\Sesser\Scurl\Response $response)
	{
		$igresponse = new Response($response);
		
		//-- First check if there's an API error from the Instagram response
		if (isset($igresponse->meta['error_type'])) {
			switch ($igresponse->meta['error_type'])
			{
				case 'OAuthParameterException':					
					throw new \Instaphp\Exceptions\OAuthParameterException($igresponse->meta['error_message'], $igresponse->meta['code']);
					break;
				case 'OAuthRateLimitException':
					throw new \Instaphp\Exceptions\OAuthRateLimitException($igresponse->meta['error_message'], $igresponse->meta['code']);
					break;
				case 'APINotFoundError':
					throw new \Instaphp\Exceptions\APINotFoundError($igresponse->meta['error_message'], $igresponse->meta['code']);
					break;
				case 'APINotAllowedError':
					throw new \Instaphp\Exceptions\APINotAllowedError($igresponse->meta['error_message'], $igresponse->meta['code']);
					break;
				case 'APIInvalidParametersError':
					throw new \Instaphp\Exceptions\APIInvalidParametersError($igresponse->meta['error_message'], $igresponse->meta['code']);
					break;

				default:
					break;
			}
		}
		//-- Next, look at the HTTP status code for 500 errors when Instagram is
		//-- either down or just broken (like it seems to be a lot lately)
		switch ($response->code)
		{
			case 500:
			case 502:
			case 503:
			case 400: //-- 400 error slipped through?
				throw new \Instaphp\Exceptions\HttpException($response->status, $response->code);
				break;
			default: //-- no error then?
				break;
		}
		return $igresponse;
	}
}

