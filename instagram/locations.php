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
     * Locations class
     * Handles all Location based API requests
     * @package Instaphp
     * @subpackage Instagram
     * @version 1.0
     * @author randy sesser <randy@instaphp.com>
     */
    class Locations extends InstagramBase
    {

        public function __construct()
        {
            parent::__construct();
            $this->api_path = '/locations';
        }
        
        /**
         * Gets information about a particular location
         * @access public
         * @param int $location_id A location ID
         * @param string $token An access token
         * @return Response 
         */
        public function Info($location_id = null, $token = null)
        {
            if (empty($token))
                trigger_error("An access token is required in " . __METHOD__, E_USER_ERROR);
            if (empty($location_id))
                trigger_error("A location ID is required in " . __METHOD__, E_USER_ERROR);
            $this->access_token = $token;
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
        public function Recent($location_id = null, $token = null, $params = array())
        {
            if (empty($token))
                trigger_error("An access token is required in " . __METHOD__, E_USER_ERROR);
            if (empty($location_id))
                trigger_error("A location ID is required in " . __METHOD__, E_USER_ERROR);
            $this->access_token = $token;
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
        public function Search($token = null, $params = array())
        {
            if (empty($token))
                trigger_error('Access token is required in ' . __METHOD__, E_USER_ERROR);

            if (isset($params['lat'])) {
                if (!isset($params['lng']) || empty($params['lng']))
                    trigger_error('Longitude and Latitude are mutually inclusive in ' . __METHOD__, E_USER_ERROR);
            }
            if (isset($params['lng'])) {
                if (!isset($params['lat']) || empty($params['lat']))
                    trigger_error('Longitude and Latitude are mutually inclusive in ' . __METHOD__, E_USER_ERROR);
            }

            $this->access_token = $token;
            $this->AddParams($params);

            return $this->Get($this->buildUrl('search'));
        }

    }

}