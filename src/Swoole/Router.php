<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0

namespace Prfox\Swoole;
use Prfox\Http\Router as RouterBase;

class Router extends RouterBase
{
	// swoole 模式下 dirname 用不了，不知何故
	protected function dispatchInfo($filePath, $action, $args)
	{
		// 是否包含分页
		$page_code = app('config')->get('app.page_code');
		if (!isset($args[$page_code])) {
		    $page_no = app('request')->get($page_code);
		    $args[$page_code] = !empty($page_no) && intval($page_no)? (int) $page_no : 1;            
		}
		$filePath = str_replace('\\','/',$filePath);

		$data = array(
		    'callback' => $filePath.'@'.$action,
		    'namespace' => dirname($filePath),
		    'module'   => '',
		    'controller' => basename($filePath),
		    'action' => $action,
		    'params' => $args
		);
		$data['module'] = basename(dirname($data['namespace']));

		app('request')->setRouteDispatch($data);
	}
}