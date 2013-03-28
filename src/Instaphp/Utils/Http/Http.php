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
 * Http - A simple cURL wrapper for making HTTP requests
 * 
 * This class handles GET, POST, PUT, DELETE and HEAD requests. Its current
 * limitation is that it does not handle authentication of any kind. 
 *
 * @author Randy Sesser <randy@instaphp.com>
 * @license http://instaphp.mit-license.org MIT License 
 * @package Instaphp
 * @subpackage Utils
 * @version 2.0-dev
 */
class Http
{
	const METHOD_GET	= 'GET';
	const METHOD_POST	= 'POST';
	const METHOD_PUT	= 'PUT';
	const METHOD_DELETE	= 'DELETE';
	const METHOD_HEAD	= 'HEAD';
	
	/**
	 * The base url of the request (https://www.google.com)
	 * @var string
	 */
	public $host	= '';
	/**
	 * The absolute path of the request (/search)
	 * @var string
	 */
	public $path	= '';
	/**
	 * The HTTP method
	 * @var string
	 */
	public $method	= Http::METHOD_GET;
	/**
	 * Parameters passed in request (or, POSTed fields)
	 * @var array
	 */
	public $params	= [];
	/**
	 * Array of curl options (CURLOPT_*)
	 * @var array
	 */
	public $options = [];
	/**
	 * Headers passed in request
	 * @var array
	 */
	public $headers = [];
	/**
	 * Array of callable "events" called throughout the request/response lifecycle
	 * @var array
	 */
	public $events	= [];
	/**
	 * The cURL resource from calling curl_init()
	 * @var resource
	 */
	protected $curl		= NULL;
	/**
	 * Configuration for the request
	 * @var array
	 */
	protected $config	= [];
	
	/**
	 * Defaults for the request (options, headers & events)
	 * @var array
	 * @access private
	 * @static
	 */
	static $defaults = [
		'curl_opts' => [
			CURLOPT_FOLLOWLOCATION	=> TRUE,
			CURLOPT_MAXREDIRS		=> 3,
			CURLOPT_HEADER			=> TRUE,
			CURLINFO_HEADER_OUT		=> TRUE,
			CURLOPT_RETURNTRANSFER	=> TRUE,
			CURLOPT_CONNECTTIMEOUT	=> 2,
			CURLOPT_TIMEOUT			=> 10,
			CURLOPT_ENCODING		=> '',
			CURLOPT_USERAGENT		=> 'Instaphp/2.0 (+http://instaphp.com)'
		],
		'headers' => [
			"Connection"		=>  "keep-alive",
			"Keep-Alive"		=> 300,
			"Accept-Charset"	=> "ISO-8859-1,utf-8;q=0.7,*;q=0.7",
			"Accept-Language"	=> "en-us,en;q=0.5"
		],
		'events' => [
			'request.create'		=> NULL,
			'request.before_send'	=> NULL,
			'request.after_send'	=> NULL,
			'error.handler'			=> NULL
		]
	];
	
	/**
	 * Constructor constructs
	 * @param string $url The base url for this request
	 * @param array $config Array of configuration for the request (@see static::$defaults)
	 * @throws \Exception Throws and exception if curl is not available on the server
	 */
	public function __construct($host, array $config = [])
	{
		if (!function_exists('curl_init'))
			throw new \Exception("cURL not found in this installation of PHP");
		
		$this->curl = curl_init();
		
		$this->config = $config;

		$this->Reset($host, TRUE);
		
		if (is_callable($this->events['request.create']))
			call_user_func_array($this->events['request.create'], [$this]);
		
	}
	
	/**
	 * Convenience method for making GET requests
	 * @param string $path The absolute path of the request
	 * @param array $data Array of parameters to pass in GET request
	 * @return \Instaphp\Utils\Http\Response
	 */
	public function Get($path = '', array $data = [])
	{
		return $this->Send(self::METHOD_GET, $path, $data);
	}
	
	/**
	 * Convenience method for making POST requests
	 * @param string $path The absolut path of the request
	 * @param array $data Array of parameters to POST
	 * @return \Instaphp\Utils\Http\Response
	 */
	public function Post($path = '', array $data = [])
	{
		return $this->Send(self::METHOD_POST, $path, $data);
	}
	
	/**
	 * Convenience method for making Put requests
	 * @param string $path The absolut path of the request
	 * @param array $data Array of parameters to pass in PUT request. Note: You 
	 *					  must pass a key of file with the full path to the 
	 *					  file you want to PUT
	 * @return \Instaphp\Utils\Http\Response
	 */
	public function Put($path = '', array $data = [])
	{
		return $this->Send(self::METHOD_PUT, $path, $data);
	}
	
	/**
	 * Convenience method for making DELETE requests
	 * @param string $path The absolut path of the request
	 * @param array $data Array of parameters to pass in DELETE request
	 * @return \Instaphp\Utils\Http\Response
	 */
	public function Delete($path = '', array $data = [])
	{
		return $this->Send(self::METHOD_DELETE, $path, $data);
	}
	
	/**
	 * Convenience method for making HEAD requests
	 * @param string $path The absolut path of the request
	 * @param array $data Array of parameters to pass in HEAD request
	 * @return \Instaphp\Utils\Http\Response
	 */
	public function Head($path = '', array $data = [])
	{
		return $this->Send(self::METHOD_HEAD, $path, $data);
	}
	
	/**
	 * 
	 * @param string $method The HTTP method for the request
	 * @param string $path The absolute path of the request
	 * @param array $data Parameters to pass in the request
	 * @param array $headers Optional headers to pass in the request
	 * @return \Instaphp\Utils\Http\Response
	 */
	public function Send($method, $path, array $data = [], array $headers = [])
	{
		$this->method = $method;
		$this->path = $path;
		$this->headers = $headers + $this->headers;
		$this->params = $data;
		
		if (!is_resource($this->curl))
			$this->curl = curl_init();
		
		$this->setOption($this->options);
		
		$curl_headers = [];
		foreach ($this->headers as $header => $value)
			$curl_headers[] = sprintf('%s: %s', $header, $value);
		
		$this->setOption(CURLOPT_HTTPHEADER, $curl_headers);
		
		$url = $this->host . $this->path;
		$query = '';
		
		foreach ($this->params as $param => $value) {
			$query .= (empty($query) ? '?' : '&') . sprintf('%s=%s', $param, urlencode($value));
		}
		$fp = null;
		switch ($this->method)
		{
			case self::METHOD_GET:
				$url .= $query;
				break;
			case self::METHOD_POST:
				$this->setOption(CURLOPT_POST, TRUE);
				$this->setOption(CURLOPT_POSTFIELDS, $this->params);
				break;
			case self::METHOD_PUT:
				if (isset($this->params['file'])) {
					$file = $this->params['file'];
					if (file_exists($file)) {
						if (FALSE !== ($fp = fopen($file, 'r'))) {
							$this->setOption(CURLOPT_PUT, TRUE);
							$this->setOption(CURLOPT_BINARYTRANSFER, TRUE);
							$this->setOption(CURLOPT_INFILE, $fp);
							$this->setOption(CURLOPT_INFILESIZE, filesize($file));
						}
					}
					unset($this->params['file']);
				}
				$this->setOption(CURLOPT_POSTFIELDS, $this->params);
				break;
			case self::METHOD_HEAD:
				$url .= $query;
				$this->setOption(CURLOPT_NOBODY, TRUE);
				break;			
			default:
			case self::METHOD_DELETE:
				$url .= $query;
				$this->setOption(CURLOPT_CUSTOMREQUEST, $this->method);
				break;
		}
		$this->setOption(CURLOPT_URL, $url);
		
		if (is_callable($this->events['request.before_send']))
			call_user_func_array($this->events['request.before_send'], [$this]);
		
		$res = curl_exec($this->curl);
		
		if (is_resource($fp))
			fclose($fp);
		
		$errNo = curl_errno($this->curl);
		if ($errNo !== CURLM_OK && is_callable($this->events['error.handler']))
			call_user_func_array($this->events['error.handler'], [$this, $errNo, curl_error($this->curl)]);
		
		$response = new Response($res);
		$response->info = curl_getinfo($this->curl);
		$response->request_url = curl_getinfo($this->curl, CURLINFO_EFFECTIVE_URL);
		$response->request_params = $this->params;
		$response->request_method = $method;
		
		if (is_callable($this->events['request.after_send']))
			call_user_func_array($this->events['request.after_send'], [$this, $response]);
		
		if ($this->curl != NULL && is_resource($this->curl))
			curl_close($this->curl);
		
		return $response;
		
	}
	
	/**
	 * Resets the Http object to initial state. Closes and re-opens the curl handle
	 * @param string $url Reset the base url of the request
	 * @param bool $everything To reset everything back to defaults
	 */
	public function Reset($host = NULL, $everything = FALSE)
	{
		static $state = [];
		if (empty($state)) {
			$vars = get_class_vars(__CLASS__);
			$ref = new \ReflectionClass(__CLASS__);
			/* @var $property \ReflectionProperty */
			foreach ($ref->getProperties() as $property) {
				if (!$property->isStatic() && $property->isPublic() && $property->getName() !== 'config')
					$state[$property->getName()] = $vars[$property->getName()];
			}
		}
		
		foreach ($state as $prop => $val)
			$this->{$prop} = $val;
		
		$this->host = (!empty($host)) ? $host : $this->host;
		
		if ($everything) {

			$options = isset($this->config['options']) ? $this->config['options'] : [];
			$this->options =  $options + static::$defaults['curl_opts'];
			$this->options[CURLOPT_SSL_VERIFYPEER] = TRUE;
			$this->options[CURLOPT_SSL_VERIFYHOST] = 2;
			$this->options[CURLOPT_CAINFO] = dirname(__FILE__) . '/cacert.pem';

			$headers = isset($this->config['headers']) ? $this->config['headers'] : [];
			$this->headers = $headers + static::$defaults['headers'];

			$events = isset($this->config['events']) ? $this->config['events'] : [];
			$this->events = $events + static::$defaults['events'];
			
			if (is_resource($this->curl))
				curl_close($this->curl);
			
			$this->curl = curl_init();
			
			$this->setHeader($this->headers);
			$this->setOption($this->options);
		}
	}
	
	/**
	 * Sets options for the curl handle
	 * @param mixed $option An array of CURLOPT_* => $value or CURLOPT_*, $value
	 * @param mixed $value The value to set
	 * @returns bool True on success, false otherwise
	 */
	public function setOption($option, $value = NULL)
	{
		if ($this->curl == NULL || !is_resource($this->curl))
			return false;
		
		if (is_array($option)) {
			if (function_exists('curl_setopt_array')) {
				curl_setopt_array($this->curl, $option);
			} else {
				foreach ($option as $opt => $val)
					$this->setOption($opt, $val);
			}
		} else {
			curl_setopt($this->curl, $option, $value);
		}
		
		return true;
	}
	
	/**
	 * Sets headers for a request
	 * @param mixed $header array of key/value pairs or a single key
	 * @param string $value
	 */
	public function setHeader($header, $value = NULL)
	{
		if (is_array($header)) {
			foreach ($header as $key => $val) {
				$this->setHeader($key, $val);
			}
		} else {
			$this->headers[(string)$header] = $value;
		}
	}
	
	/**
	 * Cleans up the Http object and closes the curl handle if it's still open
	 */
	public function __destruct()
	{
		if ($this->curl != NULL && is_resource($this->curl))
			curl_close($this->curl);
	}
}

