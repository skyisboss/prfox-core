<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0
// 
// 连接池

namespace Prfox\Swoole;
use Swoole\Coroutine\Channel;

class Pool
{
    // 最小连接数
    public $min = 5;

    // 最大连接数
    public $max = 50;

    // 队列
    protected $_queue;

    // 活跃连接数
    protected $count = 0;

    // 60秒内无请求 将逐渐释放连接
    protected $free_time = 60;

    // 连接池组
    public $connection;

    public function __construct()
    {
    	$this->_queue = new Channel($this->min);
    }

    // 获取连接池的统计信息
    public function getStats()
    {
        return [
            'current_count' => $this->getCurrentCount(),
            'queue_count'   => $this->getQueueCount(),
            'active_count'  => $this->getActiveCount(),
        ];
    }

    // 获取队列中的连接数
    public function getQueueCount()
    {
        $count = $this->_queue->stats()['queue_num'];
        return $count < 0 ? 0 : $count;
    }

    // 获取活跃的连接数
    public function getActiveCount()
    {
        return $this->count;
    }

    // 获取当前总连接数
    public function getCurrentCount()
    {
        return $this->getQueueCount() + $this->getActiveCount();
    }

    // 活跃连接数自增
    protected function activeCountIncrement()
    {
        return ++$this->count;
    }

    // 活跃连接数自减
    protected function activeCountDecrement()
    {
        return --$this->count;
    }

    // 放入连接
    protected function push($connection)
    {
        $this->activeCountDecrement();
        if ($this->getQueueCount() < $this->min) {
            return $this->_queue->push($connection);
        }
        return false;
    }

    // 弹出连接
    protected function pop()
    {
        while (true) {
            $connection = $this->_queue->pop();
            $this->activeCountIncrement();
            return $connection;
        }
    }

    // 获取连接
    public function getConnection($closure)
    {
        // 队列有连接，从队列取
        if ($this->getQueueCount() > 0) {
            return $this->pop();
        }
        // 达到最大连接数，从队列取
        if ($this->getCurrentCount() >= $this->max) {
            return $this->pop();
        }
        // 活跃连接数自增
        $this->activeCountIncrement();
        // 执行创建连接的匿名函数并返回
        return $closure();
    }

    // 释放连接
    public function releaseConnection($connection, $closure)
    {
        // 放入连接
        $this->push($connection);
        // 执行销毁连接的匿名函数
        $closure();
    }

    // 销毁连接
    public function destroyConnection($closure)
    {
        // 执行销毁连接的匿名函数
        $closure();
        // 活跃连接数自减
        $this->activeCountDecrement();
    }
}