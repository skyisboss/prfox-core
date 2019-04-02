<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0
// 
// 多语言

namespace Prfox\Base;

class I18N
{
    // 是否开启多语言
    protected $i18n_on = false;

	// 语言包标识
    protected $code;

    // 当前语言
    protected $lang;

    protected $default = 'zh-cn';

    // 语言包集合
    protected $langPackge = array();

    public function __construct()
    {
        $config        = app('config')->get('app.');
        $this->i18n_on = $config['i18n_start'];
        $this->code    = $config['i18n_code'];
        $this->default = $config['i18n_default']; 
        // 加载框架语言包
        $this->loadLangPackage( dirname(__DIR__).'/assets/language/*.php' );  
    }

    public function register()
    {
        // 检查配置是否开启多语言
        if (!$this->i18n_on) {
            app('cookie')->has($this->code) && app('cookie')->delete($this->code);
            return;
        }
        // 获取当前环境语言
        $this->lang = app('request')->acceptLanguage();
        
        // 设置多语言标识 只有首次访问才设置cookie 
        !app('cookie')->has($this->code) && app('cookie')->set($this->code,$this->lang,time() + 3600*24*30);
    }

    // 获取语言
    public function get($key=null , $format=[])
    {
        // 获取当前语言标识
        $code = app('cookie')->get($this->code);
        $code = !empty($code) ? $code : $this->default;


        // key为空返回全部语言
        if (empty($key)) {
            return $this->langPackge;
        }
        // 判断语言是否存在
        $lang = isset( $this->langPackge[$code][$key] ) ? $this->langPackge[$code][$key] : $key;

        // 语言是否需要替换操作 使用 sprintf 实现
        if (!empty($format) && is_array($format)) {
            if (key($format) === 0) {
                // 数字索引解析
                array_unshift($format, $lang);
                $lang = call_user_func_array('sprintf', $format);
            } else {
                // 关联索引解析
                $replace = array_keys($format);
                foreach ($replace as &$v) {
                    $v = "{:{$v}}";
                }
                $lang = str_replace($replace, $format, $lang);
            }
        }
        return $lang;
    }


    // 加载语言包
    public function loadLangPackage($loadFile)
    {
        $files = glob($loadFile);
        if (0 >= count($files)) return;
        $langs = array();
        foreach ($files as $key => $value) {
            $key = strtolower( pathinfo($value, PATHINFO_FILENAME) );
            $langs[$key] = include($value);
        }
        $this->langPackge = array_merge($this->langPackge,$langs);
    }
}