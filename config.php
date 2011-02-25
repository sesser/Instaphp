<?php

/**
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
         * @return Instaphp\Config An instance of the Config class
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
