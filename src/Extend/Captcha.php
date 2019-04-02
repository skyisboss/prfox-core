<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0
// 
// 验证码

namespace Prfox\Extend;

class Captcha
{
	//验证码图片宽度，单位px
	private $width = 100;

	//验证码图片高度，单位px
    private $height = 40;

    // 验证码长度(个数) 
    private $length = 4; 

    // 验证码内容
    private $code;

    //验证码过期时间，单位秒
    private $expire = 60; 
	
	//是否有干扰元素
    private $disturb = true; 

    // 验证码种子
    public $token = "123456789abcdefhijkmnprstuvwxyzABCDEFGHIJKMNPQRSTUVWXYZ";

    // 字体大小
    public $fontSize = 16;

    // 字体
    public $fontFile = __DIR__ . '/../assets/font/fette.ttf';

    private $canvas;

    private $error;

    public function __construct($config = [])
    {
    	if (!empty($config) && is_array($config)) {
    		$this->width  = $this->config['width'];
    		$this->height = $this->config['height'];
    		$this->length  = $this->config['length'];
    		$this->disturb = $this->config['disturb'];
    		$this->expire  = $this->config['expire'];
    	}
    }

    public function check($code) 
    {
    	$verifyCode = app('session')->get('verify_code'); 
    	$status = true; 

    	if (empty($code)) {
    		$this->error = '请输入验证码';
    		return false;
    	}

    	if(!$verifyCode){
    		$this->error = '验证码过期';
    		$status = false;
    	}
    	if(strtolower($code) != strtolower($verifyCode)){
    		$this->error = '验证码不正确';
    		$status = false;
    	}
    	app('session')->delete('verify_code');
    	return $status;
    }

    public function getError()
    {
    	return $this->error;
    }


    public function show()
    {
        app('response')->addHeader('Content-Type', 'image/png');
        $this->createImg();
        if($this->disturb){
            $this->setDisturb();
        }
        $this->setCode();
        $this->output();
    }

    // 创建图片
    private function createImg()
    {
        $this->canvas = imagecreatetruecolor($this->width, $this->height);
        $background = imagecolorallocate($this->canvas, 255, 255, 255); //243, 251, 254
        imagefill($this->canvas, 0, 0, $background);
    }

    // 设置干扰元素
    private function setDisturb()
    {
        // 加入弧线
        for ($i = 0; $i <= 5; $i++) {
            $color = imagecolorallocate($this->canvas, mt_rand(1, 150), mt_rand(1, 150), mt_rand(1, 150));
            imagearc($this->canvas, rand(0, $this->width), rand(0, $this->height), rand(30, 300), rand(20, 200), 50, 30, $color);
        }
    }

    // 设置验证码
    private function setCode()
    {
        $angleRand = [-30, 30];
        $yRand = [5, 20];
        $xSpacing = 1.2;        

        for ($i = 0; $i < $this->length; $i++) {

            $word = $this->token{rand(0, strlen($this->token) - 1)};
            $this->code .= $word;

            $fontColor  = imagecolorallocate($this->canvas, mt_rand(30, 150), mt_rand(31, 150), mt_rand(31, 150));
            
            imagettftext(
            	$this->canvas, 
            	$this->fontSize, 
            	mt_rand($angleRand[0], $angleRand[1]), 
            	$this->fontSize * ($xSpacing * $i) + $xSpacing + 5, 
            	$this->fontSize + mt_rand($yRand[0], $yRand[1]), 
            	$fontColor, 
            	$this->fontFile, 
            	$word
            );
        } 
        //app('session')->set('verify_code',$this->code,$this->expire);   
    }

    // 输出图片
    private function output()
    {
        ob_start();
        imagepng($this->canvas);
        // 释放资源
        imagedestroy($this->canvas);

        if ('fpm' == app('app')->getRunEnv()) {
            echo ob_get_clean();
        } else {
            app('response')->addBody(ob_get_clean());
        }
    }
}