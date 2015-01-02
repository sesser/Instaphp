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
 * Tags API
 * 
 * Handles all tags related API calls.
 *
 * @author Randy Sesser <randy@instaphp.com>
 * @license http://instaphp.mit-license.org MIT License 
 * @package Instaphp
 * @version 2.0-dev
 */
class Tags extends Instagram
{
	/**
	 * Get information about a tag object.
	 * @param string $tag The tag
	 * @return Response
	 */
	public function Info($tag)
	{
		return $this->Get($this->formatPath('/tags/%s', $tag));
	}
	
	/**
	 * Get a list of recently tagged media. Note that this media is ordered by 
	 * when the media was tagged with this tag, rather than the order it was 
	 * posted. Use the max_tag_id and min_tag_id parameters in the pagination 
	 * response to paginate through these objects.
	 * 
	 * Parameters
	 *	- min_id: Return media before this min_id.
	 *	- max_id: Return media after this max_id.
	 * 
	 * @param string $tag The tag
	 * @param array $params Parameters to pass
	 * @return Response
	 */
	public function Recent($tag, array $params = [])
	{
		return $this->Get($this->formatPath('/tags/%s/media/recent', $tag), $params);
	}
	
	/**
	 * Search for tags by name. Results are ordered first as an exact match, 
	 * then by popularity. Short tags will be treated as exact matches.
	 * 
	 * Parameters
	 *	- count: The number of tags to return
	 * @param string $query A tag name to search
	 * @param array $params
	 * @return Response
	 */
	public function Search($query, array $params = [])
	{
		$params['q'] = strtolower($query);
		return $this->Get('/tags/search', $params);
	}
	
	/**
	 * Performs multiple searches for a tag by name. 
	 * 
	 * @see Tags::Search()
	 * @param mixed $terms Array of terms or space separated list of terms
	 * @param array $params Parameters to pass
	 * @return array Returns an array of {@link InstaResponse}
	 */
	public function MultiSearch($terms, array $params = [])
	{
		$responses = [];
		if (!is_array($terms))
			$terms = explode(' ', $terms);
		
		foreach ($terms as $term) 
			$responses[] = $this->Search($term, $params);
		
		return $responses;
	}
}

