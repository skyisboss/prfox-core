<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0

namespace Prfox\Http;

class Cookie
{
    // 前缀
    protected $prefix; 

    // 过期时间 0 为关闭浏览器就失效
    protected $expires = 0;

    // 有效的服务器路径
    protected $path = '/';

    // 有效域名/子域名
    protected $domain = '';

    // 仅通过安全的 HTTPS 连接传给客户端
    protected $secure = false;

    // 仅可通过 HTTP 协议访问
    protected $httpOnly = false;

    public function __construct()
    {
        $config        = app('config')->get('app.');
        $this->prefix  = $config['cookie_prefix'];
        $this->expires = intval($config['cookie_expires']);
    }

    protected function getCookieName($name)
    {
    	return $this->prefix . $name;
    }

    // 获取cookie
    public function get($name = null)
    {
        return app('request')->cookie($this->getCookieName($name));
    }

    // 赋值
    public function set($name, $value, $expires = '')
    {
    	$name = $this->getCookieName($name);
        $time = !empty($expires) ? $expires : ($this->expires ? time() + $this->expires : $this->expires);
        setcookie($name, $value, intval($time), $this->path, $this->domain, $this->secure, $this->httpOnly);
    }

    // 是否存在
    public function has($name)
    {
        return is_null($this->get($name)) ? false : true;
    }
    
    // 删除指定cookie
    public function delete($name)
    {
        return $this->set($name, null);
    }

    // 清空所有cookie
    public function clear()
    {
        $cookies = app('request')->cookie();
        foreach ($cookies as $name => $value) {
            $this->set($name, null);
        }
        return true;
    }
}