<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0
// 
// 错误和异常

namespace Prfox\Base;

class Exception extends \Exception
{

	// 构造
    
    public function __construct($message = "", $code = 0, $previous = null)
    {
        app('response')->addHeader('Content-Type', 'text/html; charset=utf-8');
        parent::__construct($message);
        // TODO 日志记录
    }


	// 注册异常处理
	static public function register()
	{
    	if ( app('config')->get('app.app_debug') ) {
    		// 调试模式打开全部错误提示
			ini_set("display_errors", "On");
			error_reporting(E_ALL);  
		    $whoops = new \Whoops\Run;
		    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
		    $whoops->register();
    	} else {
    		ini_set("display_errors", "Off");
    		error_reporting(0);
    		//set_exception_handler( array(static::class, "handeler") );
    		//set_error_handler([static::class, "handeler"]);    	
    		register_shutdown_function( array(static::class, "handler") );
    	} 
	}

	static private function page_404()
	{
		ob_start();
		include __DIR__ .'/../assets/template/error.php';
		return ob_get_clean();
	}

	static public function handler($error = null)
	{
		$output = '<pre>' .$error. '</pre>';
		!app('config')->get('app.app_debug') && $output = self::page_404();
		app('response')->addStatus(404);
		app('response')->addBody($output);
		app('response')->send();
		return;
	}
}