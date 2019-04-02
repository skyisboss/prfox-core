<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0

namespace Prfox\Swoole;
use Prfox\Http\Response as HttpResponse;

class Response extends HttpResponse
{
	public $response;
	
    public function handler($response)
    {
    	$this->response = $response;
    }

    protected function sendStatus()
    {
        $this->response->status($this->status);
    }
    protected function sendHeader()
    {
        foreach ($this->headers as $key => $value) {
            $this->response->header($key, $value);
        }
    }

    public function sendBody()
    {
        $this->response->end($this->output); 
    }

    public function getResponse()
    {
        return $this->response;
    }
}