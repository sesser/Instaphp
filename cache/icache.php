<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Instaphp\Cache
{
	/**
	 *
	 * @author randy
	 */
	interface iCache
	{
		public function Set($key, $obj, $ttl = null);

		public function Get($key);

		public function Delete($key);

		public function Check($key);
		
		public function Gc();

		public function Clear();
	}
}