<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Instaphp\Cache
{
	use Instaphp;
	use Instaphp\Config;
	require_once('icache.php');
	/**
	 * Description of file
	 *
	 * @author randy
	 */
	class File implements iCache
	{
		private static $_instance;
		private $_config;
		private $_defaultTtl;
		private $_path;
		
		private final function __construct()
		{
			$this->_config = Config::Instance();
			$cache = $this->_config->xpath("//Cache[@Engine='File']");
			
			if (empty($cache) || count($cache) == 0 || !$cache[0]["Enabled"])
				return false;
			$this->_defaultTtl = strtotime("+2 minutes");
			$this->_path = dirname(dirname(__FILE__)) . "/tmp/cache/";
			
			if (!is_dir($this->_path))
				mkdir($this->_path, 0755, true);
			
			$this->Gc();
		}
		
		public static function Instance()
		{
			if (null == static::$_instance)
				static::$_instance = new self();
			return static::$_instance;
		}
		
		public function Set($key, $value, $ttl = NULL)
		{
			$ttl = (empty($ttl)) ? $this->_defaultTtl : strtotime($ttl);
			
			if (!is_string($value))
				$value = serialize($value);
			
			$value = $ttl . PHP_EOL . $value;
			$this->_save($key, $value);
		}
		
		public function Get($key)
		{
			if (null !== ($content = $this->_read($key)))
				return $content;
			return false;
				
		}

		public function Delete($key)
		{
			if ($this->Check($key))
				@unlink($this->_path . $key);
		}

		public function Check($key)
		{
			return file_exists($this->_path . $key);
		}

		public function Clear()
		{
			$dir = dir($this->_path);
			while (false !== ($file = $dir->read())) {
				if ($file != "." && $file != "..")
					$this->Delete($file);
			}
		}
		
		public function Gc()
		{
			$min = (int)date("i");
			$now = time();
			if ($min % 2 == 0) {
				$dir = dir($this->_path);
				while (false !== ($file = $dir->read())) {
					if ($file != "." && $file != "..") {
						$lines = file($this->_path . $file);
						$ttl = array_shift($lines);
						if (!empty($ttl) && $now >= (int)$ttl)
							$this->Delete($file);
					}
				}
			}
		}
		
		private function _read($key)
		{
			$passes = 3;
			$now = time();
			
			if (!$this->Check($key))
				return null;
			$lines = null;
			try
			{
				for ($i = $passes; $i >= 0; $i--) {
					if (false !== ($lines = file($this->_path . $key)))
						break;
					sleep(1);
				}
			}
			catch (Exception $ex) {}
			
			if (null === $lines)
				return null;
			
			$ttl = (int)array_shift($lines);
			
			$content = "";
			if (count($lines) > 0) {
				if ($now >= $ttl) {
					$this->Delete($key);
					return null;
				}
				$content = implode(PHP_EOL, $lines);
			}
			
			return @unserialize($content);
		}
		
		private function _save($key, $content)
		{
			return file_put_contents($this->_path . $key, $content, LOCK_EX|FILE_TEXT);
		}
	}
}
