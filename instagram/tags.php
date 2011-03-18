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
     * Tags
     * The Tags class handles all tag based API calls
     * @package Instaphp
     * @version 1.0
     * @author randy sesser <randy@instaphp.com>
     */
    class Tags extends InstagramBase
    {

        public function __construct($token = null)
        {
            parent::__construct($token);
            $this->api_path = '/tags';
        }

        /**
         * Gets infor about a particular tag
         * @access public
         * @param string $tag A tag name
         * @param string $token An access token
         * @return Response 
         */
        public function Info($tag = mull)
        {
            if (empty($tag))
                trigger_error("You didn't supply a tag, not sure what whill happen here...", E_USER_WARNING);

            return $this->Get($this->buildUrl($tag));
        }

        /**
         * Gets recent media tagged with $tag
         * @access public
         * @param string $tag A tag name
         * @param string $token An access token
         * @param Array $params An associative array of key/value pairs to pass to the API
         * @return Response 
         */
        public function Recent($tag, Array $params = array())
        {
			if (!empty($params))
				$this->AddParams($params);

            return $this->Get($this->buildUrl($tag . '/media/recent'));
        }

        /**
         * Searches for media by tag
         * @access public
         * @param string $query
         * @param string $token
         * @return Response 
         */
        public function Search($query = '')
        {
            $this->AddParam('q', $query);
            return $this->Get($this->buildUrl('search'));
        }

    }

}