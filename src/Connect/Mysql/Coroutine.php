<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0

namespace Prfox\Connect\Mysql;
use Prfox\Swoole\Pool;

class Coroutine extends Persistent
{
	public $pool;

	public function __construct()
	{
		$this->pool = new Pool;
		// 连接池状态管理
		app()->instance('dbPool', $this->pool);
		// 开启协程
		static $trigger = false;
		if (!$trigger) {
		    \Swoole\Runtime::enableCoroutine(true);
		    $trigger = true;
		}
	}

	public function __destruct()
	{
        // 关闭连接
        $this->closeConnect();
	}

	// 连接
	protected function connect()
	{
	    $this->db = $this->pool->getConnection(function () {
            return parent::createConnect();
        });
	}

	// 关闭连接
	public function closeConnect()
	{
	    $this->pool->releaseConnection($this->db, function () {
	        parent::closeConnect();
	    });
	}

	// 重新连接
	protected function reconnect()
	{
	    $this->pool->destroyConnection(function () {
	        parent::closeConnect();
	    });
	    $this->connect();
	}
}