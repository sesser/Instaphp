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

namespace Instaphp\Instagram {

    use Instaphp\Config;
    use Instaphp\Request;
    use Instaphp\Response;

    /**
     * Locations class
     * Handles all Location based API requests
     * @package Instaphp
     * @version 1.0
     * @author randy sesser <randy@instaphp.com>
     */
    class Locations extends InstagramBase
    {

        public function __construct($token = null)
        {
            parent::__construct($token);
            $this->api_path = '/locations';
        }
        
        /**
         * Gets information about a particular location
         * @access public
         * @param int $location_id A location ID
         * @param string $token An access token
         * @return Response 
         */
        public function Info($location_id)
        {
            return $this->Get($this->buildUrl($location_id));
        }

        /**
         * Get recent media associated with a particular media
         * @access public
         * @param int $location_id A location ID
         * @param string $token An access token
         * @param Array $params An associative array of key/value pairs to pass to the API
         * @return Response 
         */
        public function Recent($location_id, Array $params = array())
        {
			if (!empty($params))
				$this->AddParams($params);
				
            return $this->Get($this->buildUrl($location_id . '/media/recent'));
        }

        /**
         * Search for media by latitude/longitude
         * @access public
         * @param string $token An access token
         * @param Array $params An associative array of key/value pairs to pass to the API
         * @return Response 
         */
        public function Search(Array $params = array())
        {
            if (isset($params['lat'])) {
                if (!isset($params['lng']) || empty($params['lng']))
                    trigger_error('Longitude and Latitude are mutually inclusive in ' . __METHOD__, E_USER_ERROR);
            }
            if (isset($params['lng'])) {
                if (!isset($params['lat']) || empty($params['lat']))
                    trigger_error('Longitude and Latitude are mutually inclusive in ' . __METHOD__, E_USER_ERROR);
            }

			if (!empty($params))
				$this->AddParams($params);

            return $this->Get($this->buildUrl('search'));
        }

    }

}