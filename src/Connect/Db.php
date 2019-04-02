<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0

namespace Prfox\Connect;

class Db
{
    protected $handler;
    
    public function __construct($tableName = null)
    {
        // fpm模式启用默认链接
        if ('fpm' == app('app')->getRunEnv()) {
            $handler = \Prfox\Connect\Mysql\Driver::class;
        } else {
            // swoole模式 如果开启协程使用协程连接，否则使用长连接
            if (app('config')->get('swoole.enable_coroutine')) {
                $handler = \Prfox\Connect\Mysql\Coroutine::class;
            } else {
                $handler = \Prfox\Connect\Mysql\Persistent::class;
            }
        }
        $this->handler = new $handler($tableName);
    }


    public function __call($method, $args)
    {
        // 执行命令
        return call_user_func_array(array($this->handler, $method), $args);
    }

    static public function __callStatic($method, $args)
    {
        // ($method == 'table' && $method = 'setTable') || ($method == 'name' && $method = 'setTable');
        ($method == 'table' || $method == 'name') && $method = 'setTable';
        // 执行命令
        return call_user_func_array(array((new static())->handler, $method), $args);
    }
}