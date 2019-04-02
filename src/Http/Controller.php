<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0

namespace Prfox\Http;
use Prfox\Base\Exception;

class Controller
{
	// 默认布局
    protected $layout = 'main';

    private $assignData = array();

	public function __construct()
	{
		$this->initialize();
		// 注册多语言服务		
		app('I18n')->register();
	}

	// 控制器初始化方法
	protected function initialize(){}

	//重定向跳转
	protected function redirect($uri)
	{
		app('response')->addStatus(302);
		app('response')->addHeader('Location', $uri);
		return;
	}
	
	//输出成功信息
	protected function seccess($data, $status = 200)
	{
	}
	
	//输出错误信息
	protected function error($data, $status = 404)
	{
		app('response')->addStatus($status);
	}
	
	//输出json信息
	protected function json(array $data,bool $unicode = false)
	{
		app('response')->addHeader('Content-Type', 'application/json');
		return $unicode 
			? json_encode($data,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) 
			: json_encode($data);
	}

	//将变量赋值给模板
	protected function assign(string $name,string $value = null)
	{
		$this->assignData[$name]=$value;
	}

	// 渲染视图 
	protected function display(string $template = '', array $assigns = array())
	{
		if (!empty($assigns)) {
			foreach ($assigns as $key => $value) {
				$this->assign($key,$value);
			}
		}

		/*****************************  获取模板文件  *****************************/
		// 调度信息
		$dispatch = app('request')->route();
		// 模块文件夹路径
		$module_dir = dirname( $dispatch['namespace'] );
		// 视图文件夹路径
		$view_dir   = $module_dir . DIRECTORY_SEPARATOR .app('config')->get('app.view_path');		
		
		// 如果未指定模板文件,根据默认组合
		if (empty($template)) {
			$template = $dispatch['module'] .DIRECTORY_SEPARATOR .$dispatch['action'];
		} else {
			$template = preg_replace('/\:|@|\/+/', DIRECTORY_SEPARATOR, $template);
			if (strpos($template, DIRECTORY_SEPARATOR) !== false) {
				$template = explode(DIRECTORY_SEPARATOR,$template);
				$template = $template[0] . DIRECTORY_SEPARATOR .$template[1];
			} else {
				$template = $dispatch['module'] . DIRECTORY_SEPARATOR .$template;
			}
		}

		// 完整的视图文件路径
		$template = app('app')->getRootPath() .
					$view_dir .
					DIRECTORY_SEPARATOR .
					$template .
					'.php';

		if( !is_file($template) ) throw new Exception('视图文件不存在');
		extract($this->assignData);
		ob_start();
        require $template;
        $output = ob_get_clean() . debug();
        app('response')->addHeader('Content-Type', 'text/html; charset=utf-8');
        return $output;
        
	}

	// 来个魔法函数 方便在控制器中获取容器服务 提升开发体验
	// 据说魔法函数对性能有很大影响
	// 然而php7中经过严格测试，使用魔法函数的效率只慢 2-5微秒差距，是微秒 μm
	public function __call($method, $args)
	{
		return \Prfox::app($method);
	}
}