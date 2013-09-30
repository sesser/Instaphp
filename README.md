## Instaphp V2 ##

This is version 2 of Instaphp. It's a complete rewrite from version 1 and is not backwards compatible. If you're using v1 and want to update to v2, you'll have to make a few changes. Some of the method names have changed and configuration is no longer an XML file. There are unit tests, but given the less than stellar reliability of Instagram's API, they are fairly useless.

If you're using composer, you should need to worry about dependancies. If you're not, you will have to figure out the include chain. The new version relies on [Scurl][1], a simple HTTP utility. See the [Scurl README][2] for more information.
```php
<?php
```

[1]: https://github.com/sesser/Scurl
[2]: https://github.com/sesser/Scurl/blob/master/README.md

It's not battle tested so I can't speak to it's reliability/speed/ease of use, but the unit test (generally) all pass. I will keep this in the development branch for a while until I think it's ready to move into master.

## Usage ##
<?php 

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
```