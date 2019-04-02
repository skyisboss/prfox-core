<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0
// 
// 缓存

namespace Prfox\Base;
use Prfox\Base\Exception;

class Cache
{
    static protected $driver;

    static protected function drivers()
    {
        $driver = app('config')->get('app.cache_driver');
        $driverClass = "\\Prfox\\Base\\Cache\\" . ucfirst(strtolower($driver)); 
        
        if (!class_exists($driverClass)) throw new Exception('缓存配置不正确');
        if (!is_object(static::$driver)) static::$driver = new $driverClass();
        return static::$driver;
    }

    static public function __callStatic($method,$args)
    {
        return call_user_func_array(array(static::drivers(), $method), $args);
    }
}
