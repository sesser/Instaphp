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
use GuzzleHttp\Message\Request;
/**
 * Users API
 *
 * @author Randy Sesser <randy@instaphp.com>
 * @license http://instaphp.mit-license.org MIT License
 * @package Instaphp
 * @version 2.0-dev
 */
class Users extends Instagram
{
	/**
	 * Authorize a user and set the access_token and user
	 * @param string $code
	 * @return boolean Returns true on success, false otherwise
	 */
	public function Authorize($code)
	{
        try {
            $response = $this->http->Post($this->buildPath('/oauth/access_token', false), [
                'body' => [
                    'client_id' => $this->config['client_id'],
                    'client_secret' => $this->config['client_secret'],
                    'redirect_uri' => $this->config['redirect_uri'],
                    'grant_type' => 'authorization_code',
                    'code' => $code
                    ]
            ]);
        } catch (GuzzleHttp\Exception\RequestException $re) {
            printf('%s%s', $re->getRequest(), PHP_EOL);
        }
		if ($response->getStatusCode() == 200) {
			$res = new Response($response);
			$this->SetAccessToken($res->access_token);
			$this->user = $res->user;
			return true;
		}
		return false;
	}

	/**
	 * Attempts to find a user_id by username.
	 * @param string $username The username to find
	 * @return mixed Returns a user_id for username or false if not found
	 */
	public function FindId($username)
	{
		$res = $this->Search($username, ['count' => 100]);
		if (!empty($res->data)) {
			foreach ($res->data as $user) {
				if (strtolower($user['username']) === strtolower($username))
					return $user['id'];
			}
		}
		return false;
	}

	/**
	 * Gets info about a user
	 * @param string $user_id A valid user_id
	 * @return \Instaphp\Instagram\Response
	 */
	public function Info($user_id)
	{
		return $this->Get($this->formatPath('/users/%s', $user_id));
	}

	/**
	 * Gets the currently authenticated user's feed
	 * @param array $params Parameters to pass to the API
	 * @return \Instaphp\Instagram\Response
	 * @throws \Instaphp\Exceptions\OAuthParameterException
	 */
	public function Feed(array $params = [])
	{
		if (empty($this->access_token))
			throw new \Instaphp\Exceptions\OAuthParameterException("A valid access_token is required to call this endpoint");
		return $this->Get('/users/self/feed', $params);
	}

	/**
	 * Gets the most recent media for a user.
	 * @param string $user_id A valid user_id
	 * @param array $params Parameters to pass to the API. Valid parameters are 'count', 'max_timestamp', 'min_timestamp', 'min_id', and 'max_id'
	 * @return \Instaphp\Instagram\Response
	 */
	public function Recent($user_id, array $params = [])
	{
		return $this->Get($this->formatPath('/users/%s/media/recent', $user_id), $params);
	}

	/**
	 * Gets the currently authenticated users liked media
	 * @param array $params Parameters to pass to the API. Valid parameters are 'count' and 'max_like_id'
	 * @return \Instaphp\Instagram\Response
	 * @throws \Instaphp\Exceptions\OAuthParameterException
	 */
	public function Liked(array $params = [])
	{
		if (empty($this->access_token))
			throw new \Instaphp\Exceptions\OAuthParameterException("A valid access_token is required to call this endpoint");

		return $this->Get('/users/self/media/liked', $params);
	}

	/**
	 * Search for users by username/full_name
	 * @param string $username
	 * @param array $params Parameters to pass to the API. Only supported parameter is 'count'
	 * @return \Instaphp\Instagram\Response
	 */
	public function Search($username, array $params = [])
	{
		$params['q'] = $username;
		return $this->Get('/users/search', $params);
	}

	/**
	 * Get the list of users this user follows.
	 * @param string $user_id A valid user_id
	 * @param array $params Parameters to pass to the API. Only supported parameter is 'count' (not-documented)
	 * @return \Instaphp\Instagram\Response
	 */
	public function Follows($user_id, array $params = [])
	{
		return $this->Get($this->formatPath('/users/%s/follows', $user_id), $params);
	}

	/**
	 * Get the list of users this user is followed by.
	 * @param string $user_id A valid user_id
	 * @param array $params Parameters to pass to the API. Only supported parameter is 'count' (not-documented)
	 * @return \Instaphp\Instagram\Response
	 */
	public function FollowedBy($user_id, array $params = [])
	{
		return $this->Get($this->formatPath('/users/%s/followed-by', $user_id), $params);
	}

	/**
	 * List the users who have requested this user's permission to follow
	 * @return \Instaphp\Instagram\Response
	 * @throws \Instaphp\Exceptions\OAuthParameterException
	 */
	public function Requests()
	{
		if (empty($this->access_token))
			throw new \Instaphp\Exceptions\OAuthParameterException("A valid access_token is requred to call this endpoint");

		return $this->Get('/users/self/requested-by');
	}

	/**
	 * Get information about a relationship to another user.
	 * @param string $user_id A valid user_id
	 * @return \Instaphp\Instagram\Response
	 * @throws \Instaphp\Exceptions\OAuthParameterException
	 */
	public function Relationship($user_id)
	{
		if (empty($this->access_token))
			throw new \Instaphp\Exceptions\OAuthParameterException("A valid access_token is requred to call this endpoint");

		return $this->Get($this->formatPath('/users/%s/relationship', $user_id));
	}

	/**
	 * Follow a user
	 * @param string $user_id A valid user_id
	 * @return \Instaphp\Instagram\Response
	 * @throws \Instaphp\Exceptions\OAuthParameterException
	 */
	public function Follow($user_id)
	{
		return $this->setRelationship($user_id, strtolower(__FUNCTION__));
	}

	/**
	 * Unfollow a user
	 * @param string $user_id A valid user_id
	 * @return \Instaphp\Instagram\Response
	 * @throws \Instaphp\Exceptions\OAuthParameterException
	 */
	public function Unfollow($user_id)
	{
		return $this->setRelationship($user_id, strtolower(__FUNCTION__));
	}

	/**
	 * Block a user
	 * @param string $user_id A valid user_id
	 * @return \Instaphp\Instagram\Response
	 * @throws \Instaphp\Exceptions\OAuthParameterException
	 */
	public function Block($user_id)
	{
		return $this->setRelationship($user_id, strtolower(__FUNCTION__));
	}

	/**
	 * Unblock a user
	 * @param string $user_id A valid user_id
	 * @return \Instaphp\Instagram\Response
	 * @throws \Instaphp\Exceptions\OAuthParameterException
	 */
	public function Unblock($user_id)
	{
		return $this->setRelationship($user_id, strtolower(__FUNCTION__));
	}

	/**
	 * Approve a user's request to follow
	 * @param string $user_id A valid user_id
	 * @return \Instaphp\Instagram\Response
	 * @throws \Instaphp\Exceptions\OAuthParameterException
	 */
	public function Approve($user_id)
	{
		return $this->setRelationship($user_id, strtolower(__FUNCTION__));
	}

	/**
	 * Deny a user's request to follow
	 * @param string $user_id A valid user_id
	 * @return \Instaphp\Instagram\Response
	 * @throws \Instaphp\Exceptions\OAuthParameterException
	 */
	public function Deny($user_id)
	{
		return $this->setRelationship($user_id, strtolower(__FUNCTION__));
	}

	/**
	 * Wrapper method for setting the relationship (Follow, Unfollow, Block, Unblock, Approve, Deny)
	 * @param string $user_id A valid user_id
	 * @param string $action Action to perform. One of follow, unfollow, block, unblock, approve, deny
	 * @return \Instaphp\Instagram\Response
	 * @throws \Instaphp\Exceptions\OAuthParameterException
	 */
	private function setRelationship($user_id, $action)
	{
		if (empty($this->access_token))
			throw new \Instaphp\Exceptions\OAuthParameterException("A valid access_token is requred to call this endpoint");

		return $this->Post($this->formatPath('/users/%s/relationship', $user_id), ['action' => $action]);
	}
}
