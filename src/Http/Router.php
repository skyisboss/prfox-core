<?php
// @package    Pxfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Pxfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0

namespace Prfox\Http;
use Prfox\Base\Exception;

class Router
{
    protected $halts = false;
    protected $routes = array();
    protected $methods = array();
    protected $callbacks = array();
    protected $maps = array();
    protected $patterns = array(
        ':any' => '[^/]+',
        ':num' => '[0-9]+',
        ':all' => '.*'
    );
    protected $error_callback;

    // 添加路由
    public function __call($method, $params) {
        $uri = strpos($params[0], '/') === 0 ? $params[0] : '/' . $params[0];
        array_push($this->maps, null);
        array_push($this->routes, $uri);
        array_push($this->methods, strtoupper($method));
        array_push($this->callbacks, $params[1]);
    }

    // 调度信息
    protected function dispatchInfo($filePath, $action, $args)
    {
        // 是否包含分页
        $page_code = app('config')->get('app.page_code');
        if (!isset($args[$page_code])) {
            $page_no = app('request')->get($page_code);
            $args[$page_code] = !empty($page_no) && intval($page_no)? (int) $page_no : 1;            
        }

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

    // 定义错误回显
    public static function error($callback) {
        self::$error_callback = $callback;
    }
    public static function haltOnMatch($flag = true) {
        self::$halts = $flag;
    }

    // 1、路由解析
    public function parse()
    {
        $url_suffix = app('config')->get('app.url_suffix');

        $uri = preg_replace('/\.('.$url_suffix.')$/i', '', app('request')->getUri());

        return $ret = $this->parseRoute($uri);
    }

    // 执行操作
    public function dispatch($callback,$params = array())
    {
        if (!is_object($callback)) {
            list($controller, $action) = explode('@', $callback); 
            
            if (!class_exists($controller)) {               
                throw new Exception('控制器不存在');
            }            
            if (!method_exists($controller, $action)) {
                throw new Exception('操作方法不存在');
            }
            // 更新路由信息  
            $this->dispatchInfo($controller, $action, $params);

            $callback = array(new $controller(),$action);
        }
        return call_user_func_array($callback , $params);
    }

    // 解析路由
    private function parseRoute($uri)
    {
        if (!empty($this->routes)) {
            $httpMethod = app('request')->method();
            $searches = array_keys($this->patterns);
            $replaces = array_values($this->patterns);
            $found_route = false;
            $this->routes = preg_replace('/\/+/', '/', $this->routes);            
            // 直接命中 
            if (in_array($uri, $this->routes)) {
                $route_pos = array_keys($this->routes, $uri);                
                foreach ($route_pos as $route) {
                    if ($this->foundRoute($route,$httpMethod)) {
                        $found_route = true;
                        return array($this->callbacks[$route],array());
                    }
                }
            } 
            // 正则命中
            else {
                $pos = 0;            
                foreach ($this->routes as $route) {
                    if (strpos($route, ':') !== false) {
                        $route = str_replace($searches, $replaces, $route);
                    }
                    if (preg_match('#^' . $route . '$#', $uri, $params)) {
                        if ($this->foundRoute($pos,$httpMethod)) {
                            $found_route = true;
                            array_shift($params);
                            return array($this->callbacks[$pos],$params);
                        }
                    }
                    $pos++;
                }
            }
        }

        return $this->parseDefaultRoute($uri);
    }

    // 判断路由是否存在
    private function foundRoute($route, $httpMethod)
    {
        if ($this->methods[$route] == $httpMethod || $this->methods[$route] == 'ANY' || (!empty($this->maps[$route]) && in_array($httpMethod, $this->maps[$route])) ) 
        {
            return true;
        }
        return false;
    }

    // 解析默认路由
    private function parseDefaultRoute($uri)
    {
        // 设置默认目标
        $config = app('config')->get('app.');
        $module     = $config['default_module'];
        $controller = $config['default_controller'];
        $action     = $config['default_action'];
        $params     = array();

        if ($uri != '/') {
            $uri        = explode('/',ltrim($uri,'/'));
            $count      = count($uri);
            switch ($count) {
                case 3:
                    // 全部匹配
                    list($module,$controller,$action) = $uri;
                    break;
                case 2:
                    // 匹配模块控制器，方法默认
                    list($controller,$action) = $uri;
                    break;
                case 1:
                    // 匹配模块，控制器方法默认
                    list($controller) = $uri;
                    break;
                default:
                    list($module,$controller,$action) = $uri;
                    // 多余部分作为参数
                    for ($i=3; $i < $count+3; $i += 2) { 
                        if (isset($uri[$i+1])) {
                            $params[$uri[$i]] = $uri[$i+1];
                        }
                    }
            }
        }

        $module = ucfirst($module);
        $controller = ucfirst($controller);
        // 组合命名空间 多模块部署
        $callback = "\App\\Http\\{$module}\\{$config['ctrl_path']}\\{$controller}@{$action}"; 

        // 单模块部署
        //$callback = "\\{$config['app_path']}\\{$config['ctrl_path']}\\{$controller}@{$action}"; 

        // 判断是否定义过目标地址url
        if (in_array(ltrim($callback,'\\'), $this->callbacks)) {
            throw new Exception("该地址已定义路由，请从路由地址访问");            
        }
        return array( $callback,$params );
    }
}


