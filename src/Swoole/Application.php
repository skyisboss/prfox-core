<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0

namespace Prfox\Swoole;
use Prfox\Base\Application AS Base;

class Application extends Base
{
	public function __construct($rootPath)
	{
		$this->runEnv   = 'swoole';
		$this->rootPath = $rootPath;
		\Prfox::registerComponents($this, array(
			'captcha'  => \Prfox\Extend\Captcha::class,
			'config'   => \Prfox\Base\Config::class,
			'request'  => \Prfox\Swoole\Request::class,
			'cookie'   => \Prfox\Swoole\Cookie::class,
			'router'   => \Prfox\Swoole\Router::class,
			'response' => \Prfox\Swoole\Response::class,
			'session' => \Prfox\Swoole\Session::class,
			'I18n'    => \Prfox\Base\I18N::class,

			'redis'    => \Prfox\Connect\Redis::class,
			'mysql'    => \Prfox\Connect\Db::class,
		));
	}

	public function initialize($request, $response)
	{
        $this->genMem  = memory_get_usage();
        $this->genTime = microtime(true);
	    \Prfox::app('request')->handler($request);
	    \Prfox::app('response')->handler($response);
	}

	
}