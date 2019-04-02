<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0

namespace Prfox\Swoole;
use Prfox\Extend\Tool;
use Prfox\Connect\Redis;
/**
 * session_name  控制台 Name 栏的值
 * session_id    控制台 Value 栏的值
 * 在fpm模式下 session_name为 PHPSESSID 
 * session_id 是php自动生成的随机字符串 一般不用用户设置
 *
 * swoole模式下的session实现
 * 1、使用redis储存会话内容
 * 2、使用随机数生成session_id
 * 3、把session_id当作key 储存在redis value包含session内容和过期时间
 * 
 */
class Session
{
	// session 前缀
	protected $prefix;
	// 名称 PHPSESSID
	protected $session_name;
	// session ID
	protected $session_id;

	// 过期时间
    protected $expires = 0;

	// swoole驱动
	protected $driver;

	// 寄存session的数据
	protected $_SESSION = [];

	public function __construct()
	{
		$this->prefix = app('config')->get('app.session_prefix');		
		$this->expires = intval(ini_get('session.gc_maxlifetime'));
		$this->session_name  = '_sessid';
		$this->driver = new Redis();

		$session_id = app('cookie')->get($this->session_name);
		empty($session_id) && $session_id = Tool::randMD5(32);
		$this->session_id = $session_id;
	}

	// 加载session数据
	protected function loadSession()
	{
		$data =  $this->driver->get($this->session_id);
		if ($data) {
			$this->_SESSION = unserialize($data);
		}
	}

	public function set($key, $value)
	{
		if (empty($this->_SESSION)) {
			$this->loadSession();
		}	    
	    app('cookie')->set($this->session_name,$this->session_id,time() + $this->expires);
	    $this->_SESSION[$key] = $value;
	}

	public function get($key)
	{
		if (empty($this->_SESSION)) {
			$this->loadSession();
		}
	    if(isset($this->_SESSION[$key])){
	        return $this->_SESSION[$key];
	    }
	    return null;
	}

	public function delete($key)
    {
    	if (empty($this->_SESSION)) {
			$this->loadSession();
		}
        if (!empty( $this->_SESSION[$key] )) {
            unset($this->_SESSION[$key]);
        }
    }

    // 使用析构函数保存数据
	public function __destruct()
	{
		if($this->session_id) {
            $this->driver->set($this->session_id,serialize($this->_SESSION), $this->expires);
        }
		$this->driver->closeConnect();
	}


	// 获取拼接后的session名称
	protected function getName($name)
    {
        return $this->prefix . $name;
    }
}