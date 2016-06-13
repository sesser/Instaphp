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
 * Media API
 * 
 * Handles all media related API calls including liking and commenting calls.
 * See {@link http://instagram.com/developer/endpoints/media/ Media Endpoints}, 
 * {@link http://instagram.com/developer/endpoints/comments/ Comment Endpoints}, and
 * {@link http://instagram.com/developer/endpoints/likes/ Like Endpoints} for 
 * Instagram's documentation on the methods contained in this class.
 *
 * @author Randy Sesser <randy@instaphp.com>
 * @license http://instaphp.mit-license.org MIT License 
 * @package Instaphp
 * @version 2.0-dev
 */
class Media extends Instagram
{
	/**
	 * Gets current popular media
	 * @param array $params Parameters to pass to API ('count' is the only known 
	 *                      parameters supported)
	 * @return Response
	 */
	public function Popular(array $params = [])
	{
		return $this->Get('/media/popular', $params);
	}
	
	/**
	 * Gets info about a particular media item
	 * @param string $media_id The media_id to fetch
	 * @param array $params Parameters to pass to API
	 * @return Response
	 */
	public function Info($media_id, array $params = [])
	{
		return $this->Get($this->formatPath('/media/%s', $media_id), $params);
	}

	/**
	 * Gets info about a particular media item by its shortcode
	 * @param string $shortcode The shortcode to fetch
	 * @param array $params Parameters to pass to API
	 * @return Response
	 */
	public function Shortcode($shortcode, array $params = [])
	{
		return $this->Get($this->formatPath('/media/shortcode/%s', $shortcode), $params);
	}
	
	/**
	 * Searches Instagram for media by location/distance. Currently supported
	 * parameters are:
	 *	- 'lat' and 'lng' (if you use one, you must use the other)
	 *	- 'min_timestamp' and 'max_timestamp' (these are unix timestamps)
	 *	- 'distance' The distance form the lat/lng to search
	 *	- 'count' the number of items to return (max ~50)
	 * @param array $params Parameters to pass to API. 
	 * @return Response
	 * @throws \Instaphp\Exceptions\InvalidArgumentException
	 */
	public function Search(array $params = [])
	{
		if ((isset($params['lat']) && !isset($params['lng'])) || (isset($params['lng']) && !isset($params['lat'])))
			throw new \Instaphp\Exceptions\InvalidArgumentException("Invalid Arguments: lat and lng are mutually inclusive");
		
		return $this->Get('/media/search', $params);
	}
	
	/**
	 * Set a like on this media by the currently authenticated user.
	 * @param string $media_id A valid media_id
	 * @return Response
	 * @throws \Instaphp\Exceptions\OAuthParameterException
	 */
	public function Like($media_id)
	{
		if (empty($this->access_token))
			throw new \Instaphp\Exceptions\OAuthParameterException("A valid access_token is required for this endpoint");
		return $this->Post($this->formatPath('/media/%s/likes', $media_id));
	}
	
	/**
	 * Get a list of users who liked this media.
	 * @param string $media_id A valid media_id
	 * @return Response
	 */
	public function Likes($media_id)
	{
		return $this->Get($this->formatPath('/media/%s/likes', $media_id));
	}
	
	/**
	 * Remove a like on this media by the currently authenticated user.
	 * @param string $media_id A valid media_id
	 * @return Response
	 * @throws \Instaphp\Exceptions\OAuthParameterException
	 */
	public function Unlike($media_id)
	{
		if (empty($this->access_token))
			throw new \Instaphp\Exceptions\OAuthParameterException("A valid access_token is required for this endpoint");
		
		return $this->Delete($this->formatPath('/media/%s/likes', $media_id));
	}
	
	/**
	 * Post a comment on this media by the currently authenticated user.
	 * @param string $media_id A valid media_id
	 * @param string $comment The comment to post 
	 * @return Response
	 * @throws \Instaphp\Exceptions\OAuthParameterException
	 */
	public function Comment($media_id, $comment)
	{
		if (empty($this->access_token))
			throw new \Instaphp\Exceptions\OAuthParameterException("A valid access_token is required for this endpoint");
		
		return $this->Post($this->formatPath('/media/%s/comments', $media_id), ['text' => $comment]);
	}
	
	/**
	 * Get a list of comments on this media
	 * @param string $media_id A valid media_id
	 * @return Response
	 */
	public function Comments($media_id)
	{
		return $this->Get($this->formatPath('/media/%s/comments', $media_id));
	}
	
	/**
	 * Remove a comment on this media
	 * @param string $media_id A valid media_id
	 * @param string $comment_id A valid comment_id
	 * @return Response
	 * @throws \Instaphp\Exceptions\OAuthParameterException
	 */
	public function Uncomment($media_id, $comment_id)
	{
		if (empty($this->access_token))
			throw new \Instaphp\Exceptions\OAuthParameterException("A valid access_token is required for this endpoint");
		
		return $this->Delete($this->formatPath('/media/%s/comments/%s', $media_id, $comment_id));
	}
}

