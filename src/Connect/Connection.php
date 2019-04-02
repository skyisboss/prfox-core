<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0
// 
// 数据库连接管理

namespace Prfox\Connect;

abstract class Connection
{
	protected $db;

	// 创建连接
    protected function createConnect(){}

    // 连接
    protected function connect()
    {
    	$this->db = $this->createConnect();
    }

    // 关闭连接
    protected function closeConnect()
    {
    	$this->db = null;
    }

    // 自动连接
    protected function autoConnect()
    {
    	if (empty($this->db)) {
    	    $this->connect();
    	}
    }

    // 执行命令
    public function __call($method, $args)
    {
        $this->autoConnect();
        return call_user_func_array(array($this->db, $method), $args);
    }
}