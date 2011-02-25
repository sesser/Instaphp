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
     * Users
     * The Users class handles all users request to the API
     * @package Instaphp
     * @subpackage Instagram
     * @version 1.0
     * @author randy sesser <randy@instagram.com>
     */
    class Users extends InstagramBase
    {

        public function __construct()
        {
            parent::__construct();
            $this->api_path = '/users';
        }

        /**
         * Gets the access token from an oAuth request
         * @access public
         * @param string $code The authorization code returned by the oAuth call
         * @param string $scope The scope of the oAuth request
         * @return Instaphp\Response 
         */
        public function Authenticate($code = null, $scope = null)
        {
            if (!empty($code)) {
                $this->AddParams(array(
                    'code' => $code,
                    'client_secret' => $this->config->Instagram->ClientSecret,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => $this->config->Instaphp->RedirectUri
                ));

                return $this->Post($this->config->GetOAuthTokenUri());
            }
        }

        /**
         * Gets info about a particular user
         * @access public
         * @param mixed $user_id A user ID or 'self' to get info about the currently authenticated user
         * @return Instaphp\Response 
         */
        public function Info($user_id = 'self')
        {
            return $this->Get($this->buildUrl($user_id));
        }

        /**
         * Gets a users feed
         * @access public
         * @param mixed $user_id A user ID or 'self' to get info about the currently authenticated user
         * @param string $token An access token
         * @param Array $params An associative array of key/value pairs to pass to the API
         * @return Instaphp\Response 
         */
        public function Feed($user_id = 'self', $token = null, $params = array())
        {
            if (empty($token))
                trigger_error("Token is requred to call this methog", E_USER_ERROR);

            $this->access_token = $token;

            if (!empty($params))
                $this->AddParams($params);

            return $this->Get($this->buildUrl($user_id . '/feed/'));
        }

        /**
         * Gets a user most recent media
         * @access public
         * @param mixed $user_id A user ID or 'self' to get info about the currently authenticated user
         * @param string $token An access token
         * @param Array $params An associative array of key/value pairs to pass to the API
         * @return Instaphp\Response 
         */
        public function Recent($user_id = 'self', $token = null, $params = array())
        {
            if (empty($token))
                trigger_error("Token is requred to call this methog", E_USER_ERROR);

            $this->access_token = $token;

            if (!empty($params))
                $this->AddParams($params);

            return $this->Get($this->buildUrl($user_id . '/media/recent/'));
        }

        /**
         * Search for a user by username
         * @access public
         * @param string $query A username
         * @param string $token An access token
         * @return Instaphp\Response 
         */
        public function Find($query = '', $token = null)
        {
            if (empty($token))
                trigger_error("Token is requred to call this methog", E_USER_ERROR);

            $this->access_token = $token;
            $this->AddParam('q', $query);

            return $this->Get($this->buildUrl('search'));
        }

        /**
         * Gets followers of a particular user
         * @access public
         * @param mixed $user_id A user ID or 'self' to get info about the currently authenticated user
         * @return Instaphp\Response 
         */
        public function Following($user_id = 'self')
        {
            return $this->Get($this->buildUrl($user_id . '/follows'));
        }

        /**
         * Gets a user's followers
         * @access public
         * @param mixed $user_id A user ID or 'self' to get info about the currently authenticated user
         * @param string $token An access token
         * @return Instaphp\Response 
         */
        public function Followers($user_id = 'self', $token = null)
        {
            if (empty($token))
                trigger_error('Access token is required in ' . __METHOD__, E_USER_ERROR);

            $this->access_token = $token;

            return $this->Get($this->buildUrl($user_id . '/followed-by'));
        }

        /**
         * Gets requests for follows for a particular user
         * @access public
         * @param mixed $user_id A user ID or 'self' to get info about the currently authenticated user
         * @param string $token An access token
         */
        public function Requests($user_id = 'self', $token = null)
        {
            if (empty($token))
                trigger_error('Access token is required in ' . __METHOD__, E_USER_ERROR);

            $this->access_token = $token;
        }

        /**
         * Gets the relationship of a user based on the currently authenticated user
         * @access public
         * @param mixed $user_id A user ID or 'self' to get info about the currently authenticated user
         * @param string $token An access token
         * @return Instaphp\Response 
         */
        public function Relationship($user_id = 'self', $token = null)
        {
            if (empty($token))
                trigger_error('Access token is required in ' . __METHOD__, E_USER_ERROR);

            $this->access_token = $token;

            return $this->Get($this->buildUrl($user_id . '/relationship'));
        }

        /**
         * Sets a relationship between a particular user and the currently authenticated user
         * @access public
         * @param int $user_id A user ID
         * @param string $action The action to perform. One of follow, unfollow, block, unblock, approve,  or deny
         * @param Array $token An associative array of key/value pairs to pass to the API
         * @return Instaphp\Response 
         */
        public function SetRelationship($user_id = null, $action = null, $token = null)
        {
            if (empty($token))
                trigger_error('Access token is required in ' . __METHOD__, E_USER_ERROR);

            if (null === $action)
                trigger_error('Action is required in ' . __METHOD__, E_USER_ERROR);

            if (!preg_match('/(follow|unfollow|block|unblock|approve|deny)/i', $action))
                trigger_error('Action should be one of follow/unfollow/block/unblock/approve/deny', E_USER_ERROR);

            if (null === $user_id)
                trigger_error('User ID is required in ' . __METHOD__, E_USER_ERROR);

            $this->access_token = $token;

            $this->api_params['action'] = $action;

            return $this->Post($this->buildUrl($user_id . '/relationship'));
        }

    }

}