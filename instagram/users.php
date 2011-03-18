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
     * Users
     * The Users class handles all users request to the API
     * @package Instaphp
     * @version 1.0
     * @author randy sesser <randy@instagram.com>
     */
    class Users extends InstagramBase
    {

        public function __construct($token = null)
        {
            parent::__construct($token);
            $this->api_path = '/users';
        }

        /**
         * Gets the access token from an oAuth request
         * @access public
         * @param string $code The authorization code returned by the oAuth call
         * @param string $scope The scope of the oAuth request
         * @return Response 
         */
        public function Authenticate($code, $scope = null)
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
         * @return Response 
         */
        public function Info($user_id = 'self')
        {
            return $this->Get($this->buildUrl($user_id));
        }

        /**
         * Gets a users feed
         * @access public
         * @param string $token An access token
         * @param Array $params An associative array of key/value pairs to pass to the API
         * @return Response 
         */
        public function Feed(Array $params = array())
        {
            if (!empty($params))
                $this->AddParams($params);

            return $this->Get($this->buildUrl('self/feed/'));
        }

        /**
         * Gets a user most recent media
         * @access public
         * @param mixed $user_id A user ID or 'self' to get info about the currently authenticated user
         * @param string $token An access token
         * @param Array $params An associative array of key/value pairs to pass to the API
         * @return Response 
         */
        public function Recent($user_id, Array $params = array())
        {
            if (!empty($params))
                $this->AddParams($params);

            return $this->Get($this->buildUrl($user_id . '/media/recent/'));
        }

        /**
         * Search for a user by username
         * @access public
         * @param string $query A username
         * @param string $token An access token
         * @return Response 
         */
        public function Find($query = '')
        {
            $this->AddParam('q', $query);
            return $this->Get($this->buildUrl('search'));
        }

        /**
         * Gets followers of a particular user
         * @access public
         * @param mixed $user_id A user ID or 'self' to get info about the currently authenticated user
         * @param Array $params Additional params to pass to the API
         * @return Response 
         */
        public function Following($user_id, Array $params = array())
        {
            if (!empty($params))
                $this->AddParams($params);
            
            return $this->Get($this->buildUrl($user_id . '/follows'));
        }

        /**
         * Gets a user's followers
         * @access public
         * @param mixed $user_id A user ID or 'self' to get info about the currently authenticated user
         * @param Array $params Additional params to pass to the API
         * @return Response 
         */
        public function Followers($user_id, Array $params = array())
        {
            if (!empty($params))
                $this->AddParams($params);

            return $this->Get($this->buildUrl($user_id . '/followed-by'));
        }

        /**
         * Gets requests for follows for a particular user
         * @access public
         * @param mixed $user_id A user ID or 'self' to get info about the currently authenticated user
         * @param string $token An access token
         * @return Response
         */
        public function Requests($user_id)
        {
	
        }

        /**
         * Gets the relationship of a user based on the currently authenticated user
         * @access public
         * @param mixed $user_id A user ID or 'self' to get info about the currently authenticated user
         * @param string $token An access token
         * @return Response 
         */
        public function Relationship($user_id)
        {
            return $this->Get($this->buildUrl($user_id . '/relationship'));
        }

        /**
         * Sets a relationship between a particular user and the currently authenticated user
         * @access public
         * @param int $user_id A user ID
         * @param string $action The action to perform. One of follow, unfollow, block, unblock, approve,  or deny
         * @param Array $token An associative array of key/value pairs to pass to the API
         * @return Response 
         */
        protected function SetRelationship($user_id, $action)
        {
            $this->AddParam('action', $action);
            return $this->Post($this->buildUrl($user_id . '/relationship'));
        }
        
        /**
         * Follow a user...
         * @access public
         * @param int $user_id A user ID
         * @param string $token An access token
         * @return Response
         */
        public function Follow($user_id)
        {
            return $this->SetRelationship($user_id, 'follow');
        }
        
        /**
         * Unfollow a user...
         * @access public
         * @param int $user_id A user ID
         * @param string $token An access token
         * @return Response
         */
        public function Unfollow($user_id)
        {
            return $this->SetRelationship($user_id, 'unfollow');
        }
        
        /**
         * Block a user...
         * @access public
         * @param int $user_id A user ID
         * @param string $token An access token
         * @return Response
         */
        public function Block($user_id)
        {
            return $this->SetRelationship($user_id, 'block');
        }
        
        /**
         * Unblock a user...
         * @access public
         * @param int $user_id A user ID
         * @param string $token An access token
         * @return Response
         */
        public function Unblock($user_id)
        {
            $this->SetRelationship($user_id, 'unblock');
        }
        
        /**
         * Approve a user request...
         * @access public
         * @param int $user_id A user ID
         * @param string $token An access token
         * @return Response
         */
        public function Approve($user_id)
        {
            $this->SetRelationship($user_id, 'approve');
        }
        
        /**
         * Deny a user request...
         * @access public
         * @param int $user_id A user ID
         * @param string $token An access token
         * @return Response
         */
        public function Deny($user_id)
        {
            $this->SetRelationship($user_id, 'deny');
        }
    }

}