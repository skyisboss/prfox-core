<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0

namespace Prfox\Http;

class Session
{
    protected $session_drive = 'file';

    protected $session_prefix;

    protected $session_id = '';

    // 寄存session的数据
    protected $_SESSION = [];

    public function __construct()
    {
        $config = app('config')->get();
        $this->session_drive  = $config['app']['session_drive'];
        $this->session_prefix = $config['app']['session_prefix'];
        $this->session_id     = $config['app']['session_id'];

        if ('redis' == $this->session_drive) {
            $host = 'tcp://' . $config['redis']['host'] . ':' . $config['redis']['port'];
            try {
                ini_set("session.save_handler", "redis");
                ini_set("session.save_path", $host);
            } catch (\Exception $e) {
                throw new \Exception("Redis服务错误", 1);               
            }
        }
        $this->session_id = $this->getId();
    }

    public function __destruct()
    {
        $_SESSION = $this->_SESSION;
    }

    public function start()
    {
        session_name($this->session_prefix . '_sessid');
        session_start();
        $this->_SESSION = $_SESSION;
    }

    public function set($key, $val)
    {
        $key = $this->getname($key);
        $this->_SESSION[$key] = $val;
    }

    public function get($key)
    {
        $key = $this->getname($key);
        if(isset($this->_SESSION[$key])){
            return $this->_SESSION[$key];
        }
        return null;
    }

    public function delete($key)
    {
        $key = $this->getname($key);
        if (!empty( $this->_SESSION[$key] )) {
            unset($this->_SESSION[$key]);
        }
    }

    public function getId()
    {
        return session_id();
    }

    private function getName($name)
    {
        return $this->session_prefix . $name;
    }
}