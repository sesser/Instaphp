## Instaphp V2 ##

This is version 2 of Instaphp. It's a complete rewrite from version 1 and is not backwards compatible. If you're using v1 and want to update to v2, you'll have to make a few changes. Some of the method names have changed and configuration is no longer an XML file. There are unit tests, but given the less than stellar reliability of Instagram's API, they are fairly useless.

If you're using composer, you shouldn't need to worry about dependencies. If you're not, you will have to figure out the include chain. The new version relies on [GuzzleHttp][3] and [MonoLog][4].

[1]: https://github.com/sesser/Scurl
[2]: https://github.com/sesser/Scurl/blob/master/README.md
[3]: http://docs.guzzlephp.org/en/latest/
[4]: https://github.com/Seldaek/monolog

It's not battle tested, so I can't speak to it's reliability/speed/ease of use, but the unit test (generally) all pass. I will keep this in the development branch for a while until I think it's ready to move into master.

## Usage ##

Here's a basic example showing how to get 10 popular posts...

``` php
<?php
	
	$api = new Instaphp\Instaphp([
		'client_id' => 'your client id',
		'client_secret' => 'your client secret',
		'redirect_uri' => 'http://somehost.foo/callback.php',
		'scope' => 'comments+likes'
	]);

	$popular = $api->Media->Popular(['count' => 10]);

	if (empty($popular->error)) {
		foreach ($popular->data as $item) {
			printf('<img src="%s">', $item['images']['low_resolution']['url']);
		}
	}
?>
```
### Configuration ###

Configuration is now a simple `array` of key/value pairs. The absolute minimum required setting is `client_id`, but if you plan to allow users to login via OAuth, you'll need `client_secret` & `redirect_uri`. All the other settings are optional and/or have sensible defaults.

Key|Default Value|Description
:--|:-----------:|:----------------
access_token|Empty|This is the access token for an authorized user. You obtain this from API via OAuth
redirect_uri|Empty|The redirect URI you defined when setting up your Instagram client
client_ip|Empty|The IP address of the client. This is used to sign POST & DELETE requests. It's not required, but without the signing, users are more limited in how many likes/comments they can post in a given hour
scope|comments+relationships+likes|The scope of your client's capability
log_enabled|FALSE|Enable logging
log_level|DEBUG|Log level. See [Monolog Logger](https://github.com/Seldaek/monolog#log-levels)
log_path|./instaphp.log|Where the log file lives
http_useragent|Instaphp/2.0; cURL/{curl_version}; (+http://instaphp.com)|The user-agent string sent with all requests
http_timeout|6|Timeout for requests to the API.
http_connect_timeout|2|Timeout for http connect
debug|FALSE|Debug mode?
event.before|Empty|Callback called prior to sending the request to the API. Method takes a single parameter [BeforeEvent](http://docs.guzzlephp.org/en/latest/events.html#before)
event.after|Empty|Callback called after a response is received from the API. Method takes a single parameter of [CompleteEvent](http://docs.guzzlephp.org/en/latest/events.html#complete)
event.error|Empty|Callback called when an error response is received from the API. Method takes a single parameter of [ErrorEvent](http://docs.guzzlephp.org/en/latest/events.html#error).
