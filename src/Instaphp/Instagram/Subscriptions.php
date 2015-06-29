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

/**
 * Subscriptions API
 * 
 * Provides simple access to Instagram's subscription API. Your subscription
 * callbacks must be publicly accessible in order for this functionality to 
 * work correctly. See {@link http://instagram.com/developer/realtime/ Real-time Photo Updates}
 * for detailed documentation
 *
 * @author Randy Sesser <randy@instaphp.com>
 * @license http://instaphp.mit-license.org MIT License 
 * @package Instaphp
 * @version 2.0-dev
 */
class Subscriptions extends Instagram
{
	/**
	 * Creates a subscription to an object. Different objects require different
	 * parameters. Please see {@link http://instagram.com/developer/realtime/#create-a-subscription Create a Subscription}
	 * for more clarification
	 * 
	 * @link http://instagram.com/developer/realtime/#create-a-subscription Create a Subscription
	 * @param string $object The object in which to subscribe
	 * @param string $callback_url The callback URL that will handle the updates
	 * @param string $verify_token A verify token to verify this subscription request
	 * @param array $params Parameters to pass
	 * @return \Instaphp\Utils\Http\Response Returns a raw HTTP {@link \Instaphp\Utils\Http\Response Response} object
	 */
	public function Create($object, $callback_url, $verify_token, array $params = [])
	{
		$defaults = [
			'client_secret' => $this->client_secret,
			'client_id'	=> $this->client_id,
			'callback_url' => $callback_url,
			'verify_token' => $verify_token,
			'object' => $object,
			'aspect' => 'media'
		];
		$params = $params + $defaults;
		return $this->Post('/subscriptions', $params);
	}
	
	/**
	 * Get a list of current subscriptions for this client
	 * @return Response
	 */
	public function ListSubscriptions()
	{
		return $this->Get('/subscriptions', ['client_id' => $this->client_id, 'client_secret' => $this->client_secret]);
	}
	
	/**
	 * Delete subscription(s).
	 * Not passing any parameters to this method will delete ALL subscriptions. 
	 * You may pass ['object' => 'tag'] to delete all 'tag' subscriptions, or
	 * you may pass ['id' => 1234] to delete a single subscription by id. See
	 * {@link http://instagram.com/developer/realtime/#delete-subscriptions Delete Subscriptions}
	 * for details.
	 * 
	 * @param array $params Parameters to pass
	 * @return Response
	 */
	public function Destroy(array $params = [])
	{
		$defaults = [
			'client_secret' => $this->client_secret,
			'client_id'	=> $this->client_id,
			'object' => '',
			'id' => ''
		];
		$params = $params + $defaults;
		if (empty($params['object']) && empty($params['id']))
			$params['object'] = 'all';
		
		return $this->Delete('/subscriptions', $params);
		
	}
	
	/**
	 * Parses a subscription POST and makes the appropriate calls to retrieve 
	 * updated media by subscription.
	 * 
	 * @param string $data The body of the update POSTed to the callback_url
	 * @param int $count (optional) number of returned objects
	 * @return array Returns an array of {@link InstaResponse} objects
	 */
	public function Recieve($data,$count = 5)
	{
		$subs = json_decode($data, TRUE);
		$responses = [];
		foreach ($subs as $sub) {
			switch ($sub['object']) {
				case 'user':
					$responses['user'][$sub['object_id']][] = $this->Get(sprintf('/users/%s/media/recent', $sub['object_id']), ["count" => $count, 'min_timestamp' => $sub['time']]);
					break;
				
				case 'tag':
					$responses['tag'][$sub['object_id']][] = $this->Get(sprintf('/tags/%s/media/recent', $sub['object_id']), ['count' => $count]);
					break;
				
				case 'location':
					$responses['location'][$sub['object_id']][] = $this->Get(sprintf('/locations/%s/media/recent', $sub['object_id']), ['count' => $count, 'min_timestamp' => $sub['time']]);
					break;
				
				case 'geography':
					$responses['geography'][$sub['object_id']][] = $this->Get(sprintf('/geographies/%s/media/recent', $sub['object_id']), ['count' => $count]);
					break;
				default: break;
			}
		}
		return $responses;
	}
}

?>
