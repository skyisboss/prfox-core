<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0

namespace Prfox\Http;

class Response
{
    // 输出内容
	protected $output;

    // 状态码
    protected $status = 200;

    // HTTP 响应头
    protected $headers = [];

    // 输出响应
	public function send()
	{
		$this->sendStatus();
		$this->sendHeader();
		$this->sendBody();		
	}


	protected function sendStatus()
    {
        header("HTTP/1.1 {$this->status}");
    }
	protected function sendHeader()
    {
        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}");
        }
	}

	protected function sendBody()
	{
        echo $this->output;
		exit;
	}

    // 设置响应内容
	public function addBody($data)
	{
		!empty($data) && $this->output = $data;
        return $this;
	}

    // 设置Header信息
    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    // 设置状态码
    public function addStatus($status)
    {
        $this->status = $status;
    }
}