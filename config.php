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

namespace Instaphp {

    use \SimpleXMLElement;

    /**
     * The Instaphp version. We pass this to Instagram as part of the User-Agent
     */
    define('INSTAPHP_VERSION', '1.0');

    /**
     * Our Config class which extends the SimpleXMLElement class
     * See {inline @link http://php.net/simplexmlelement SimplXMLElement}
     * @package Instaphp
     * @version 1.0
     * @author randy sesser <randy@instaphp.com>
     */
    class Config extends SimpleXMLElement
    {

        /**
         * A static instance property for creating an instance of the Config object
         * @var Instaphp\Config
         * @access private
         */
        private static $_instance = null;
        /**
         * The path to the config.xml file
         * @var string
         * @access private
         */
        private static $file = null;

        /**
         * Singleton method since the SimpleXMLElement class is essentially "sealed"
         * @return Config An instance of the Config object
         */
        public static function Instance()
        {
            if (static::$file == null)
                static::$file = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config.xml';

            if (!file_exists(static::$file))
                trigger_error("No configuration found for Instaphp", E_USER_ERROR);

            if (null == static::$_instance)
                static::$_instance = new self(static::$file, null, true);

            return static::$_instance;
        }

        /**
         * A convenience method to build the OAuth URL to authenticate a user.
         * the value in the config.xml file should contain some "tokens" that
         * are replaced with other values in the config.
         * @access public
         * @return string The OAuth URL used to authenticate a user
         */
        public function GetOAuthUri()
        {
            if (!isset($this->Instagram))
                return null;

            $path = $this->Instagram->OAuthPath;
            $path = str_replace("{ClientId}", $this->Instagram->ClientId, $path);
            $path = str_replace("{RedirectUri}", $this->Instaphp->RedirectUri, $path);

            if (!empty($this->Instagram->Scope))
                $path .= '&scope=' . $this->Instagram->Scope;

            return $this->Instagram->Endpoint . $path;
        }

        /**
         * A convenience method to build the OAuth URL used to retreive an access token
         * @return string The URL used to retrieve the access token
         */
        public function GetOAuthTokenUri()
        {
            if (!isset($this->Instagram))
                return null;

            return $this->Instagram->Endpoint . $this->Instagram->OAuthTokenPath;
        }

    }

}

