<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0

namespace Prfox\Connect\Redis;
use Prfox\Base\Exception;
use Prfox\Connect\Connection;

class Driver extends Connection
{
    // 创建连接
    protected function createConnect()
    {
        $redis = new \Redis();
        $config = app('config')->get('redis.');
    	try {    
    		$redis->connect($config['host'], $config['port']);    		
    	} catch (Exception $e) {
    		throw new Exception("redis connection failed");
    	}
    	if (!empty($config['password'])) $redis->auth($config['password']);
    	return $redis;
    }
}