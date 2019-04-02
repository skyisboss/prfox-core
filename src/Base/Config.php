<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0
// 
// 配置

namespace Prfox\Base;

class Config
{
    protected $config = array();

	protected $env = array();

	public function __construct()
	{
        // 加载env配置
    	$env = app('app')->getRootPath() . DIRECTORY_SEPARATOR . '.env';
        if (!is_file($env)) {
            throw new Exception('load env fail', 500);
        }
        $env = parse_ini_file($env, true);

        // 加载config配置
        $config = array();
        $files = glob(app('app')->getRootPath() . "/config/*.php");            
        foreach ($files as $key => $value) {
        	$key = strtolower( pathinfo($value, PATHINFO_FILENAME) );
        	$value = include $value;
        	if (isset($env[$key]) && is_array($value)) {
        		$this->config[$key] = $env[$key] + $value;
        	}
        }

        // 设置默认时区
        date_default_timezone_set($this->config['app']['default_timezone']);
	}

    public function getEnv($key)
    {
        if (empty($this->env)) {
            $env = app('app')->getRootPath() . DIRECTORY_SEPARATOR . '.env';
            if (!is_file($env)) {
                throw new Exception('load env fail', 500);
            }
            $this->env = parse_ini_file($env, true);
        }
        return isset($this->env[$key]) ? $this->env[$key] : $this->env;      
    }

    // 获取配置
	public function get(string $name='', $default = '')
	{
		if(empty($name)) {
            return $this->config;
        }
        $array = explode('.', $name);
        $current   = $this->config;        
        foreach ($array as $key) {
        	$finded = false;
            if (isset($current[$key])) {
            	$current = $current[$key];
            	$finded = true;
            }
        }
        if (!empty($default) && !$finded) {
        	return $default;
        }
        return $current;
	}

    // 添加配置
	public function set($name, $value = null)
	{
		if (is_string($name)) {
			if (false === strpos($name, '.')) {
                $name = 'app.' . $name;
            }
            $name = explode('.', $name, 3);
            if (2 === count($name)) {
                $this->config[strtolower($name[0])][$name[1]] = $value;
            } else {
                $this->config[strtolower($name[0])][$name[1]][$name[2]] = $value;
            }
            return $value;
		}
		if (is_array($name)) {
			$this->config = array_merge($this->config, $name);
		}
		return $this->config;
	}
}