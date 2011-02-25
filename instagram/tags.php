<?php

/**
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
     * @subpackage Instagram
     * @version 1.0
     * @author randy sesser <randy@instaphp.com>
     */
    class Tags extends InstagramBase
    {

        public function __construct()
        {
            parent::__construct();
            $this->api_path = '/tags';
        }

        /**
         * Gets infor about a particular tag
         * @access public
         * @param string $tag A tag name
         * @param string $token An access token
         * @return Response 
         */
        public function Info($tag = mull, $token = null)
        {
            if (empty($token))
                trigger_error("An access token is required in " . __METHOD__, E_USER_ERROR);

            if (empty($tag))
                trigger_error("You didn't supply a tag, not sure what whill happen here...", E_USER_WARNING);

            $this->access_token = $token;

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
        public function Recent($tag = null, $token = null, $params = array())
        {
            if (empty($token))
                trigger_error("An access token is required in " . __METHOD__, E_USER_ERROR);
            if (empty($tag))
                trigger_error("A tag is required for this to work in " . __METHOD__, E_USER_ERROR);

            $this->access_token = $token;
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
        public function Search($query = '', $token = null)
        {
            if (empty($token))
                trigger_error("An access token is required in " . __METHOD__, E_USER_ERROR);
            $this->access_token = $token;
            $this->AddParam('q', $query);
            return $this->Get($this->buildUrl('search'));
        }

    }

}