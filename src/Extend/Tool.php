<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0
// 
// 常用工具类

namespace Prfox\Extend;

class Tool
{
	// 获取随机字符串
	static public function randString($length = 8, $number = true, $ignore = [])
	{
		$strings = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
		$numbers = '0123456789';           
		if (!empty($ignore) && is_array($ignore)) {
		    $strings = str_replace($ignore, '', $strings);
		    $numbers = str_replace($ignore, '', $numbers);
		}
		$pattern = $strings . $numbers;
		$max = strlen($pattern) - 1;
		$key = '';
		for ($i = 0; $i < $length; $i++)
		{   
		    $key .= $pattern[mt_rand(0, $max)]; 
		}
		return $key;
	}

	/**
     * randmd5 产生一个随机MD5字符的一部分
     * @param   int  $length
     * @param   int  $string
     * @return  string
     */
    static public function randMD5($length = 20, $string = '') 
    {
        if (empty($string)) {
            $string = self::randString($length);
        }
        return substr(md5($string . mt_rand(111111, 999999)), 0, $length);
    }
}