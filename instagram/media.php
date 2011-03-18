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
     * Media
     * The Media class handles all media requests to the API
     * @package Instaphp
     * @version 1.0
     * @author randy sesser <randy@instaphp.com>
     */
    class Media extends InstagramBase
    {
        public function __construct($token = null)
        {
            parent::__construct($token);
            $this->api_path = '/media';
        }

        /**
         * Gets information about a particular media
         * @access public
         * @param int $media_id A media ID
         * @param string $token An access token
         * @return Response 
         */
        public function Info($media_id)
        {
            return $this->Get($this->buildUrl($media_id));
        }

        /**
         * Searches the API
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

		public function OEmbed($url)
		{
			$uri = $this->config->Instagram->Endpoint . '/' . $this->config->Instagram->Version;
			return $this->Get($uri.'/oembed', array('url' => $url));
		}

        /**
         * Gets the recent popular media.
         * Note: This method does not appear to require authentication
         * @access public
         * @return Response 
         */
        public function Popular()
        {
            return $this->Get($this->BuildUrl('popular'));
        }

        /**
         * Gets comments associated with a particular media
         * @access public
         * @param int $media_id A media ID
         * @param string $token An access token
         * @return Response 
         */
        public function Comments($media_id)
        {
            return $this->Get($this->buildUrl($media_id . '/comments'));
        }

        /**
         * Adds a comment to a particular media
         * @access public
         * @param int $media_id A media ID
         * @param string $comment The text of the comment
         * @param string $token An access token
         * @return Response 
         */
        public function Comment($media_id, $comment)
        {
            $this->AddParam('text', $comment);
            return $this->Post($this->buildUrl($media_id . '/comments'));
        }

        /**
         * Deletes a comment previously left on a particular media
         * @access public
         * @param int $media_id A media ID
         * @param int $comment_id The comment ID to delete
         * @param string $token An access token
         * @return Response 
         */
        public function DeleteComment($media_id, $comment_id)
        {
            return $this->Delete($this->buildUrl($media_id . '/comments/' . $comment_id));
        }

        /**
         * Gets likes for a particular media
         * @access public
         * @param int $media_id A media ID
         * @param string $token An access token
         * @return Response 
         */
        public function Likes($media_id)
        {
            return $this->Get($this->buildUrl($media_id . '/likes'));
        }

        /**
         * Likes a particular media
         * @access public
         * @param int $media_id A media ID
         * @param string $token An access token
         * @return Response 
         */
        public function Like($media_id)
        {
            return $this->Post($this->buildUrl($media_id . '/likes'));
        }

        /**
         * Unlikes a particular media
         * @access public
         * @param int $media_id A media ID
         * @param string $token An access token
         * @return Response
         */
        public function Unlike($media_id)
        {
            return $this->Delete($this->buildUrl($media_id . '/likes'));
        }

    }

}