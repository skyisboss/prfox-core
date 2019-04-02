<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0
// 
// 文件上传

namespace Prfox\Extend;

class File
{
	protected $name;
	protected $type;
	protected $tmpName;
	protected $error;
	protected $size;

	// 错误信息
	protected $_error;

	// 文件验证
	protected $validate;

	static public $instance;

	private function __construct($file)
	{
		$this->name    = $file['name'];
		$this->type    = $file['type'];
		$this->tmpName = $file['tmp_name'];
		$this->error   = $file['error'];
		$this->size    = $file['size'];
		$this->ext     = pathinfo($file['name'])['extension'];
		self::$instance = $this;
	}

	// $info = \Prfox\Base\File::upload($file)->check(['size'=>10])->save('/public/uploads/');
	// if (!$info) echo '上传成功，文件地址：' . $info;
	static public function upload($file)
	{
		return new self($file);
	}
	// 获取错误信息
	static public function getError()
	{
		return self::$instance->_error;
	}

	// 文件验证
	public function check($check = array())
	{
		$this->validate = $check;
		return $this;
	}

	/**
     * 保存文件方法
     * @param  string           $path    保存路径
     * @param  string|bool      $savename    保存的文件名 默认自动生成
     * @param  boolean          $replace 同名文件是否覆盖
     */
	public function save($path, $savename = true, $replace = true)
	{
		// 检测是否上传文件
		if (!is_uploaded_file($this->tmpName)) {
			$this->_error = '请上传合法文件';
			return false;
		}

		//文件验证
		if (!$this->validate()) {
			return false;
		}

		//创建文件夹
		$opath = $path;
		$path = str_replace('/', DIRECTORY_SEPARATOR, $path);
		$path = app('app')->getRootPath() . DIRECTORY_SEPARATOR . trim($path,DIRECTORY_SEPARATOR) .DIRECTORY_SEPARATOR;
		if (!is_dir($path) && !mkdir($path, 0755, true)) {
			$this->_error = '文件夹创建失败';
            return false;
        }
        // 保存名称
        $savename = $this->getSaveName($savename,$replace);

        //保存文件
        if(!move_uploaded_file($this->tmpName, $path . $savename)){
			$this->_error = '文件上传失败';
            return false;
        }
        self::$instance = null;		
        return $opath . $savename;
	}

	// 文件验证方法
	private function validate()
	{
		$check = $this->validate;

		if (isset($check['size']) && $this->size > intval($check['size']) ) {
			$this->_error = '文件大小超过';
			return false;
		}

		if( isset($check['ext']) ){
			$exeName = strtolower( $this->ext );
			$checkExt = strtolower($check['ext']);

			if(!is_integer(strpos($checkExt, $exeName))){
				$this->_error = '文件扩展名错误';
				return false;
			}
		}

		if ( isset($check['type']) ) {
			$checkType = $check['type'];
			if(!is_integer(strpos($checkType,$this->type))){
				$this->_error = '文件类型错误';
				return false;
			}
		}
		return true;
	}

	// 获取保存的文件名
	private function getSaveName($savename,$replace)
	{
		if (!$savename) {
			$name = $this->name;
		} else {			
			$name = md5( date('Ymd') . $this->name ) .'.'. $this->ext;
		}
		return $name;
	}
}