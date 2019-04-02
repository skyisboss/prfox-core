<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0

namespace Prfox\Base\Db\Redis;
use Prfox\Base\Exception;

class Redis extends \Prfox\Interfaces\BaseClass
{
	// redis对象
    protected $redis;

    // 创建连接
    protected function createConnect()
    {
        $config = app('config')->get('redis.');
    	try {    		
    		$this->redis = new \Redis();
    		$this->redis->connect($config['host'], $config['port']);    		
    	} catch (Exception $e) {
    		throw new Exception("无法连接 Redis");
    	}
    	if (!empty($config['password'])) $this->redis->auth($config['password']);
    	return $this->redis;
    }

	// 连接
    protected function connect()
    {
        $this->redis = $this->createConnect();
    }

    // 关闭连接
    public function closeConnect()
    {
        // $this->redis->close();
        $this->redis = null;
    }

    // 自动连接
    protected function autoConnect()
    {
        if (empty($this->redis)) {
            $this->connect();
        }
    }

    // 执行命令
    public function __call($method, $args)
    {
        // 自动连接
        $this->autoConnect();
        // 执行命令
        return call_user_func_array(array($this->redis, $method), $args);
    }
}