<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0

namespace Prfox\Http;

class Request
{
    public $fd;

    // GET 参数
    protected $get = [];

    // POST 参数
    protected $post = [];

    // FILES 参数
    protected $files = [];

    // COOKIE 参数
    protected $cookie = [];

    // HEADER 参数
    protected $header = [];

    // SERVER 参数
    protected $server = [];

    // 路由参数
    protected $dispatch = [];

    // Request handeler
    protected $request = [];

    public function __construct()
    {
        $header = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $header[str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($name, 5))))] = $value;
                unset($_SERVER[$name]);
            }
        }
        $this->header = $header;
        $this->get    = $_GET;
        $this->post   = $_POST;
        $this->files  = $_FILES;
        $this->cookie = $_COOKIE;
        $this->server = array_change_key_case($_SERVER, CASE_LOWER);
    }

    // 设置路由信息
    public function setRouteDispatch($info)
    {
        $this->dispatch = $info;
    }

    public function setRouteParams($params)
    {
        $this->dispatch['params'] = array_merge($this->dispatch['params'],$params);
    }


    // 提取 GET 值
    public function get($name = null)
    {
        return $this->fetch($name, $this->get);
    }

    // 提取 POST 值
    public function post($name = null)
    {
        return $this->fetch($name, $this->post);
    }

    // 提取 FILES 值
    public function files($name = null)
    {
        return $this->fetch($name, $this->files);
    }

    // 提取 ROUTE 值 TODO
    public function route($name = null)
    {
        return $this->fetch($name, $this->dispatch);
    }



    // 提取 COOKIE 值
    public function cookie($name = null)
    {
        return $this->fetch($name, $this->cookie);
    }

    // 提取 SERVER 值
    public function server($name = null)
    {
        return $this->fetch($name, $this->server);
    }

    // 提取 HEADER 值
    public function header($name = null)
    {
        return $this->fetch($name, $this->header);
    }

    // 提取数据
    protected function fetch($name, $container)
    {
        return is_null($name) ? $container : (isset($container[$name]) ? $container[$name] : null);
    }

    // 获取客户端语言
    public function acceptLanguage()
    {
        $raw =  $this->header('accept-language');        
        $pos  = strpos($raw, ',');
        $lang = $pos === false ? $raw : substr($raw, 0, $pos);
        /* Fix clientLang for ie >= 10. https://www.drupal.org/node/365615. */
        if(stripos($lang, 'hans')) $lang = 'zh-cn';
        if(stripos($lang, 'hant')) $lang = 'zh-tw';
        return strtolower($lang);
    }

    // 多种方式 兼容获取uri信息
    public function getUri()
    {
        // $uri = NULL;
        $uri = $this->server('path_info');
        $scriptName = $this->server('script_name');
        if (is_null($uri)) $uri = $this->server('request_uri');
        if (is_null($uri)) $uri = $this->server('orig_path_info');
        if (is_null($uri)) $uri = @getenv('path_info');
        if (is_null($uri)) $uri = @getenv('request_uri');
        if (is_null($uri)) $uri = @getenv('orig_path_info');
        if(false !== strpos($uri, $scriptName)) $uri = str_replace($scriptName, '', $uri);        
        $uri =  urldecode( parse_url($uri,PHP_URL_PATH) );
        return $uri;
    }


    // 是否为 GET 请求
    public function isGet()
    {
        return $this->method() == 'GET';
    }

    // 是否为 POST 请求
    public function isPost()
    {
        return $this->method() == 'POST';
    }

    // 是否为 AJAX 请求
    public function isAjax()
    {
        return (null != $this->server('HTTP_X_REQUESTED_WITH')) ? true : false;
    }

    // 是否为 PUT 请求
    public function isPut()
    {
        return $this->method() == 'PUT';
    }

    // 是否为 PATCH 请求
    public function isPatch()
    {
        return $this->method() == 'PATCH';
    }

    // 是否为 DELETE 请求
    public function isDelete()
    {
        return $this->method() == 'DELETE';
    }

    // 是否为 HEAD 请求
    public function isHead()
    {
        return $this->method() == 'HEAD';
    }

    // 是否为 OPTIONS 请求
    public function isOptions()
    {
        return $this->method() == 'OPTIONS';
    }

    // 返回请求类型
    public function method()
    {
        return $this->server('request_method');
    }

    // 返回请求的根URL
    public function root()
    {
        return $this->scheme() . '://' . $this->header('host') . '/';
    }

    // 返回请求的路径
    public function path()
    {
        return substr($this->server('path_info'), 1);
    }

    // 返回请求的URL
    public function url()
    {
        return $this->scheme() . '://' . $this->header('host') . $this->server('path_info');
    }

    // 返回请求的完整URL
    public function fullUrl()
    {
        // return $this->scheme() . '://' . $this->header('host') . $this->server('path_info') . '?' . $this->server('query_string');
        return $this->scheme() . '://' . $this->header('host') . $this->server('path_info') . $this->server('request_uri');
    }

    // 获取协议
    protected function scheme()
    {
        return $this->server('request_scheme') ?: $this->header('scheme');
    }
}