<?php 

namespace Instaphp\Cache
{
	use Instaphp;
	require_once('icache.php');

	abstract class Cache implements iCache
	{
		private static $_instance;

		private function __construct($engine)
		{
			
		}

		public static function Instance($engine = "File")
		{
			if (null == static::$_instance) {
				$method = new \ReflectionMethod("Instaphp\\Cache\\{$engine}", "Instance");
				static::$_instance = $method->invoke(null, null);
			}
			return static::$_instance;
		}
	}
}