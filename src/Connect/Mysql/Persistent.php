<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0

namespace Prfox\Connect\Mysql;

class Persistent extends Driver
{
	// 重新连接
	protected function reconnect()
	{
	    $this->closeConnect();
	    $this->connect();
	}

	// 执行命令
	public function __call($name, $arguments)
	{
	    try {
	        // 执行父类命令
	        return parent::__call($name, $arguments);
	    } catch (\Throwable $e) {
	        if (self::isDisconnectException($e)) {
	            // 断开连接异常处理
	            $this->reconnect();
	            // 重新执行命令
	            return $this->__call($name, $arguments);
	        } else {
	            // 抛出其他异常
	            throw $e;
	        }
	    }
	}

    // 判断是否为断开连接异常
    protected static function isDisconnectException(\Throwable $e)
    {
        $disconnectMessages = [
            'server has gone away',
            'no connection to the server',
            'Lost connection',
            'is dead or not enabled',
            'Error while sending',
            'decryption failed or bad record mac',
            'server closed the connection unexpectedly',
            'SSL connection has been closed unexpectedly',
            'Error writing data to the connection',
            'Resource deadlock avoided',
            'failed with errno',
        ];
        $errorMessage       = $e->getMessage();
        foreach ($disconnectMessages as $message) {
            if (false !== stripos($errorMessage, $message)) {
                return true;
            }
        }
        return false;
    }
}