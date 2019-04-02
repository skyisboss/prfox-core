<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0

namespace Prfox\Base\Cache;
use Prfox\Base\Redis;

class Redis
{
	private $driver;
	public function __construct()
	{
		$this->driver = new Redis();
	}
	public function __destruct()
	{
		$this->driver->closeConnect();
	}

	public function get($name){
		$cacheData = $this->driver->get($name);
		if(empty($cacheData)){return null;}
		return unserialize($cacheData);
	}
	public function set($name, $val, $expire = 0){
		if ($expire) {
			$this->driver->setex($name, $expire, serialize($val));
		} else {
			$this->driver->set($name, serialize($val));
		}
	}
	public function remove($name){
		$this->driver->delete($name);
	}
	public function clear(){
		$this->driver->flushAll();
	}
}

