<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0

namespace Prfox\Swoole;
use Prfox\Http\Request as HttpRequest;

class Request extends HttpRequest
{
    public function __destruct()
    {
        $this->fd     = null;
        $this->get    = null;
        $this->post   = null;
        $this->files  = null;
        $this->cookie = null;
        $this->request = null;
    }

    public function handler($request)
    {
        // 烦人的 ico 图标请求
        if ($request->server['path_info'] == '/favicon.ico') return ''; 
        $this->request = $request;       
        $this->header = $request->header;
        $this->get    = $request->get;
        $this->post   = $request->post;
        $this->files  = $request->files;
        $this->cookie = $request->cookie;
        $this->server = $request->server;
        $this->fd     = $request->fd;
    }
}