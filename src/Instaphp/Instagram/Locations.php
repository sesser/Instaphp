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
 * Locations API
 * 
 * Handles all location based API calls. See 
 * {@link http://instagram.com/developer/endpoints/locations/ Location Endpoints}
 * for more information on the methods contained in this class.
 *
 * @author Randy Sesser <randy@instaphp.com>
 * @license http://instaphp.mit-license.org MIT License 
 * @package Instaphp
 * @version 2.0-dev
 */
class Locations extends Instagram
{
	/**
	 * Get information about a particular location
	 * @param string $location_id A valid location id
	 * @return Response
	 */
	public function Info($location_id)
	{
		return $this->Get($this->formatPath('/locations/%s', $location_id));
	}
	
	/**
	 * Get recent media from a location. 
	 * Optional parameters:
	 *	- min_timestamp: Return media after this UNIX timestamp
	 *	- max_timestamp: Return media before this UNIX timestamp
	 *	- min_id: Return media before this min_id.
	 *	- max_id: Return media after this max_id.
	 * @param string $location_id A valid location id
	 * @param array $params Parameters to pass
	 * @return Response
	 */
	public function Recent($location_id, array $params = [])
	{
		return $this->Get($this->formatPath('/locations/%s/media/recent', $location_id), $params);
	}
	
	/**
	 * Search for a location by geographic coordinate or Foursquare location ID (V1 or V2)
	 * paramters
	 *	- lat: Latitude of the center search coordinate. If used, lng is required.
	 *	- lng: Longitude of the center search coordinate. If used, lat is required.
	 *	- distance: Default is 1000m (distance=1000), max distance is 5000.
	 *	- foursquare_v2_id: Returns a location mapped off of a foursquare v2 api location id. If used, you are not required to use lat and lng.
	 *	- foursquare_id: Returns a location mapped off of a foursquare v1 api location id. If used, you are not required to use lat and lng. Note that this method is deprecated; you should use the new foursquare IDs with V2 of their API.
	 * @param array $params Parameters to pass
	 * @return Response
	 * @throws \Instaphp\Exceptions\InvalidArgumentException
	 */
	public function Search(array $params)
	{
		extract($params);
		if ((isset($lat) && !isset($lng)) || (isset($lng) && !isset($lng)))
			throw new \Instaphp\Exceptions\InvalidArgumentException("'lat' and 'lng' are mutually inclusive");
		
		return $this->Get('/locations/search', $params);
	}
}

