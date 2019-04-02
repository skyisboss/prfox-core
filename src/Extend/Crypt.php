<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0
// 
// 对称加解密

namespace Prfox\Extend;

class Crypt
{

    static public function encode($string)
    {
        $encrypt = openssl_encrypt(serialize($string), static::method(), static::token(),0, static::iv());

        return $encrypt;
    }

    static public function decode($encrypt)
    {
        $decrypt = openssl_decrypt($encrypt, static::method(), static::token(), 0, static::iv());

        return unserialize($decrypt);
    }

    // 加密盐
    static private function token()
    {
        return app('config')->get('app.openssl_token');
    }

    // 加密方法
    static private function method()
    {
        return app('config')->get('app.openssl_method');
    }

    // 偏移量
    static private function iv()
    {
        return substr(md5(static::token()),5,16);
    }

}