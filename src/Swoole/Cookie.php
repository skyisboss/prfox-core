<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0

namespace Prfox\Swoole;
use Prfox\Http\Cookie as HttpCookie;

class Cookie extends HttpCookie
{
	public function set($name, $value, $expires = '') 
	{
		$name = $this->getCookieName($name);
        $time = !empty($expires) ? $expires : ($this->expires ? time() + $this->expires : $this->expires); 
        $response = app('response')->getResponse();
        $response->cookie(
        	$name, $value, $time, $this->path, $this->domain, $this->secure, $this->httpOnly
        );
	}
}