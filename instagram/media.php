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
     * Media
     * The Media class handles all media requests to the API
     * @package Instaphp
     * @subpackage Instagram
     * @version 1.0
     * @author randy sesser <randy@instaphp.com>
     */
    class Media extends InstagramBase
    {
        public function __construct()
        {
            parent::__construct();
            $this->api_path = '/media';
        }

        /**
         * Gets information about a particular media
         * @access public
         * @param int $media_id A media ID
         * @param string $token An access token
         * @return Response 
         */
        public function Info($media_id = null, $token = null)
        {
            if (empty($media_id))
                trigger_error('Media ID is required in ' . __METHOD__, E_USER_ERROR);

            if (!empty($token))
                $this->access_token = $token;

            return $this->Get($this->buildUrl($media_id));
        }

        /**
         * Searches the API
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
        public function Comments($media_id = null, $token = null)
        {
            if (empty($media_id))
                trigger_error('Media ID is required in ' . __METHOD__, E_USER_ERROR);

            if (empty($token))
                trigger_error('Access token is required in ' . __METHOD__, E_USER_ERROR);

            $this->access_token = $token;

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
        public function AddComment($media_id = null, $comment = null, $token = null)
        {
            if (empty($media_id))
                trigger_error('Media ID is required in ' . __METHOD__, E_USER_ERROR);

            if (empty($comment))
                trigger_error('Comment is null in ' . __METHOD__, E_USER_WARNING);

            if (empty($token))
                trigger_error('Access token is required in ' . __METHOD__, E_USER_ERROR);

            $this->access_token = $token;

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
        public function DeleteComment($media_id = null, $comment_id = null, $token = null)
        {
            if (empty($media_id))
                trigger_error('Media ID is required in ' . __METHOD__, E_USER_ERROR);

            if (empty($comment_id))
                trigger_error('Comment ID is required in ' . __METHOD__, E_USER_ERROR);

            if (empty($token))
                trigger_error('Access token is required in ' . __METHOD__, E_USER_ERROR);

            $this->access_token = $token;

            return $this->Delete($this->buildUrl($media_id . '/comments/' . $comment_id));
        }

        /**
         * Gets likes for a particular media
         * @access public
         * @param int $media_id A media ID
         * @param string $token An access token
         * @return Response 
         */
        public function Likes($media_id = null, $token = null)
        {
            if (empty($token))
                trigger_error('Access token is required in ' . __METHOD__, E_USER_ERROR);

            if (empty($media_id))
                trigger_error('Media ID is required in ' . __METHOD__, E_USER_ERROR);

            $this->access_token = $token;
            return $this->Get($this->buildUrl($media_id . '/likes'));
        }

        /**
         * Likes a particular media
         * @access public
         * @param int $media_id A media ID
         * @param string $token An access token
         * @return Response 
         */
        public function Like($media_id = null, $token = null)
        {
            if (empty($token))
                trigger_error('Access token is required in ' . __METHOD__, E_USER_ERROR);

            if (empty($media_id))
                trigger_error('Media ID is required in ' . __METHOD__, E_USER_ERROR);

            $this->access_token = $token;
            return $this->Post($this->buildUrl($media_id . '/likes'));
        }

        /**
         * Unlikes a particular media
         * @access public
         * @param int $media_id A media ID
         * @param string $token An access token
         * @return Response
         */
        public function UnLike($media_id = null, $token = null)
        {
            if (empty($token))
                trigger_error('Access token is required in ' . __METHOD__, E_USER_ERROR);

            if (empty($media_id))
                trigger_error('Media ID is required in ' . __METHOD__, E_USER_ERROR);

            $this->access_token = $token;
            return $this->Delete($this->buildUrl($media_id . '/likes'));
        }

    }

}