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
namespace Instaphp;
use Instaphp\Exceptions\InstaphpException;
use Instaphp\Exceptions\InvalidEndpointException;

use Monolog\Logger;
/**
 * A PHP library for accessing Instagram's API
 *
 * This is version 2 of the Instaphp library and is a complete rewrite from the
 * previous version. It's not entirely compatible with the previous version.
 *
 * Requirements:
 *	- PHP >= 5.4.0 with cURL enabled
 *
 * @author Randy Sesser <randy@instaphp.com>
 * @license http://instaphp.mit-license.org MIT License
 * @package Instaphp
 * @version 2.0-dev
 *
 * @property-read Instagram\Media $Media Media API
 * @property-read Instagram\Users $Users Users API
 * @property-read Instagram\Tags $Tags Tags API
 * @property-read Instagram\Locations $Locations Locations API
 * @property-read Instagram\Subscriptions $Subscriptions Subscription API
 * @property-read Instagram\Direct $Direct Direct share API
 */
class Instaphp
{
	/** @var array Storage for the endpoints */
	private static $endpoints = [];

	/** @var array Available enoints */
	private static $availableEndpoints = ["media", "users", "tags", "locations", "subscriptions", "direct"];

	/** @var array Configuration for Instaphp */
	protected $config = [];

	/**
	 * Constucts a new Instaphp object
	 * @param array $config Configuration
	 */
	public function __construct(array $config = [])
	{
		$ua = sprintf('Instaphp/2.0; cURL/%s; (+http://instaphp.com)', curl_version()['version']);
        $logpath = dirname(__FILE__) . '/instaphp.log';
		$defaults = [
			'client_id'	=> '',
			'client_secret' => '',
			'access_token' => '',
			'redirect_uri' => '',
			'client_ip' => '',
			'scope' => 'comments+relationships+likes',
            'log_enabled' => false,
            'log_level' => Logger::DEBUG,
            'log_path' => $logpath,
			'http_useragent' => $ua,
			'http_timeout' => 6,
			'http_connect_timeout' => 2,
            'verify' => true,
			'debug' => false,
			'event.before' => [],
			'event.after' => [],
			'event.error' => []
		];
		$this->config = $config + $defaults;

		//-- Can't do anything without a client_id...
        if (empty($this->config['client_id']))
            throw new InstaphpException("Invalid client id");
	}

	/**
	 * Get an Instagram API endpoint
	 * @param string $endpoint The endpoint name
	 * @return mixed The instantiated endpoint
	 * @throws Exceptions\InvalidEndpointException
	 */
	public function __get($endpoint)
	{
		$endpoint = strtolower($endpoint);
		$class = ucfirst($endpoint);
		if (in_array($endpoint, static::$availableEndpoints)) {
			if (!$this->__isset($endpoint)) {
				$ref = new \ReflectionClass('Instaphp\\Instagram\\' . $class);
				$obj = $ref->newInstanceArgs([$this->config]);
				static::$endpoints[$endpoint] = $obj;
			}

			return static::$endpoints[$endpoint];
		}
		throw new InvalidEndpointException("{$endpoint} is not a valid endpoint");
	}

	/**
	 * Check if endpoint is set and already instantiated
	 * @param string $endpoint The endpoint name
	 * @return bool
	 */
	public function __isset($endpoint)
	{
		$endpoint = strtolower($endpoint);
		return in_array($endpoint, static::$availableEndpoints) &&
				isset(static::$endpoints[$endpoint]) &&
				static::$endpoints[$endpoint] instanceof Instagram\Instagram;
	}

	/**
	 * Unset an endpoint
	 * @param string $endpoint The endpoint name
	 */
	public function __unset($endpoint)
	{
		$endpoint = strtolower($endpoint);
		if (isset(static::$endpoints[$endpoint]))
			unset(static::$endpoints[$endpoint]);
	}

	/**
	 * Get the OAuth url for logging into Instagram
	 * @param bool $displayTouch When true, adds 'display=touch' to the url for mobile friendly UI
	 * @return string
	 */
	public function getOauthUrl($displayTouch = TRUE)
	{
		return sprintf('https://api.instagram.com/oauth/authorize/?client_id=%s&redirect_uri=%s&scope=%s&response_type=code%s',
				$this->config['client_id'],
				urlencode($this->config['redirect_uri']),
				$this->config['scope'],
				$displayTouch ? '&display=touch':'');
	}

	/**
	 * Set the access_token
	 * @param string $access_token The access_token
	 */
	public function setAccessToken($access_token)
	{
		//also needs to update all the endpoints

		foreach(static::$endpoints as $endpoint) {
			$endpoint->setAccessToken($access_token);
		}

		$this->config['access_token'] = $access_token;
	}

	/**
	 * Get the access_token
	 * @return string
	 */
	public function getAccessToken()
	{
		return $this->Users->getAccessToken();
	}

	/**
	 * @see Instagram\Instagram::isAuthorized()
	 * @return boolean
	 */
	public function isAuthorized()
	{
		return $this->Users->isAuthorized();
	}

    /**
     * @see Instagram\Instagram::getCurrentUser()
     * @return array
     */
    public function getCurrentUser()
    {
        return $this->Users->getCurrentUser();
    }
}
