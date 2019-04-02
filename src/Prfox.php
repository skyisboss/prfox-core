<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0
// 
// 组件管理
use Prfox\Base\Container AS IOC;
use Prfox\Base\Exception;

class Prfox
{
	const VERSION = '1.0.5';
	protected static $container;

	// 绑定组件
	static public function registerComponents($app,$component = [])
	{
		$default = array(		
			'config'   => \Prfox\Base\Config::class,			
			'I18n'     => \Prfox\Base\I18N::class,
			'request'  => \Prfox\Http\Request::class,
			'cookie'   => \Prfox\Http\Cookie::class,
			'session'  => \Prfox\Http\Session::class,
			'router'   => \Prfox\Http\Router::class,
			'response' => \Prfox\Http\Response::class,			
			'captcha'  => \Prfox\Extend\Captcha::class,

			'redis'    => \Prfox\Db\Redis::class,
			'mysql'    => \Prfox\Db\Mysql::class,
		);		
		static::$container = IOC::getInstance();
		static::$container->instance('app', $app);		
		// if ('fpm' == $app->getRunEnv()) {
		if (empty($component)) {
			// 绑定服务（延迟加载）
			static::$container->bind($default);
			static::$container->session->start();
			// 注册错误处理
			Exception::register();
			return;
		}
		
		foreach ($component as $name => $class) {
			static::$container->instance($name, new $class);	
		}
	}

	// 获取容器服务
	static public function app($name = '')
    {
        return empty($name) ? static::$container : static::$container->make($name);
    }
}