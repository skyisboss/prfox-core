<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0

namespace Prfox\Facades;

class Facade
{
    protected static $bind=[];

    public static function bind($name, $class=null)
    {
        if (is_array($name)) {
            self::$bind=array_merge(self::$bind, $name);
        } else {
            self::$bind[$name]=$class;
        }
    }

    protected static function createFacade($class='', $args=[])
    {
        $class=$class ?: static::class;
        $facadeClass=static::getFacadeClass();
        if ($facadeClass) {
            $class=$facadeClass;
        } elseif (isset(self::$bind[$class])) {
            $class=self::$bind[$class];
        }
        return Container::getInstance()->make($class, $args);
    }

    protected static function getFacadeClass()
    {}

    // 调用实际类的方法
    public static function __callStatic($method, $params)
    {
        return call_user_func_array([static::createFacade(), $method], $params);
    }
}