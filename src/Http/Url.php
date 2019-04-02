<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0

namespace Prfox\Http;

// \Prfox\Http\Url::build('admin/user/index',['id'=>5,'page'=>1]);
class Url
{
	/**
	 * 生成url
	 * @param  string $url url地址
	 * @param  array $params 额外参数
	 * @param  boolean $suffix 是否生成后缀
	 * @return string 返回拼接后的url
	 */
	static public function build($url = '', $params = [], $suffix = true)
	{
		$suffix = self::getSuffix($suffix);
		$url = rtrim($url,'/');
		foreach ($params as $key => $value) {
			$url .= '/'.$key.'/'.$value;
		}
		return app('request')->root() . $url . $suffix;
	}

	// 获取 url 后缀
	static private function getSuffix($is_suffix)
	{
		$suffix = '';
		if (is_bool($is_suffix) && $is_suffix) {
			$url_suffix = app('config')->get('app.url_suffix');
			if (false !== strpos($url_suffix, 'html')) {
				$suffix = '.html';
			} else {
				$suffix = explode('|', $url_suffix);
				$suffix = '.'.$suffix[0];
			}
		}elseif (is_string($is_suffix)) {
			$suffix = $is_suffix;
		}
		return $suffix; 
	}
}