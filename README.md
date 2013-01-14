## Instaphp v1.0

This software is licensed under The MIT License. Please see the file named 'LICENSE' for futher details.

### About
Instaphp is a small(ish) PHP library to access [Instagram's][0] [API][1]. It's
main goal was to be easy to use, lightweight and have as few dependencies as
possible. It's currently only compatible with PHP 5 >= 5.3 but there are
very few 5.3 features being used and it's relatively trivial to convert it
to versions < 5.3.

Instaphp is currently being used to power [instaview.me](http://instaview.me).

### Quickstart Guide
To get an idea of how the library works, it's best to understand the various
[endpoints][2] provided by the API itself. The Instagram API currently has eight
"endpoints" in which to retrieve data from their system:

*    Users
*    Relationships
*    Media
*    Comments
*    Likes
*    Tags
*    Locations
*    Geographies (This is really a subscription based endpoint and not available in Instaphp, yet)

In actuality, these eight endpoints can be summarized into four major endpoints:

*    Users
*    Media
*    Tags
*    Locations

Relationships are really an attribute of Users. Comments and Likes are both
attributes of Media. Geographies are also an attribute of Media however, that
particular endpoint is only available for subscriptions created for a particular
location. Currently, Instaphp does not have a mechanism that supports subscriptions.

These four main "endpoints" are the basis for this library and allows a good
separation for the various data you can pull from Instagram.

#### Example: Getting the current Popular photos

```php
<?php
//-- Include our library
include_once 'instaphp/instaphp.php';

//-- Get an instance of the Instaphp object
$api = Instaphp\Instaphp::Instance();

//-- Get the response for Popular media
$response = $api->Media->Popular();

//-- Check if an error was returned from the API
if (empty($response->error))
	foreach ($response->data as $item)
		printf('<img src="%s" width="%d" height="%d" alt="%s">', $item->images->thumbnail->url, $item->images->thumbnail->width, $item->images->thumbnail->height, empty($item->caption->text) ? 'Untitled':$item->caption->text);
```

#### Example: Authentication

Instagram uses oAuth to authenticate its users. That means you follow a link to
their site, login to their system, and grant an application access on your behalf.
It's a fairly common scheme for handling authentication without passing sensitive
data (e.g. your username and password) across (possibly) unsecure lines (e.g. non-https).

For Instaphp, you must have an API Key, API Secret and callback URL in order for
oAuth to work. The basic flow looks like this:

1.    User clicks a link to "Login"
2.    User lands on Instagram's site and enters username/password
3.    Upon successful login, user is asked if they want to grant application access to their photos
4.    If user allows access, Instagram redirects user to callback URL of application with a code
5.    Application calls API with code to validate authentication
6.    Application is then given an access token to "sign" the calls to the API

Here's how it looks:

```php
<?php 
	//-- The oAuth URL can be found in the Config object
	$oAuthUrl = Instaphp\Config::Instance()->GetOAuthUri(); ?>

	<!-- Here's a link -->
	<a href="<?php echo $oAuthUrl ?>">Login</a>
	
	<?php
	//-- To authenticate, simply grab the code in your callback url
	$code = $_GET['code'];
	if (!empty($code)) {
		//-- Create an Instaphp instance
		$api = Instaphp\Instaphp::Instance();

		//-- Authenticate
		$response = $api->Users->Authenticate($code);

		//-- If no errors, grab the access_token (and cookie it, if desired)
		if (empty($response->error)) {
			$token = $response->auth->access_token;
			setcookie('instaphp', $token, strtotime('30 days'));
			//-- once you have a token, update the Instaphp instance so it passes the token for future calls
			$api = Instaphp\Instaphp::Instance($token);
		}
	}
```

[0]: http://instagr.am/
[1]: http://instagram.com/developer/
[2]: http://instagram.com/developer/endpoints/
