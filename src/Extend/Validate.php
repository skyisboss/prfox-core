<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0
// 
// 验证器

namespace Prfox\Extend;
use Prfox\Base\Exception;

class Validate
{
	// 验证规则
	protected $rule = [];
	// 提示信息
	protected $message = [];
	// 验证场景
	protected $project = [];
 	// 错误信息
	protected $error = [];

	// 验证规则
	public function rule($name)
	{
		$this->rule = array_merge($this->rule, $name);
		return $this;
	}

	// 验证回显信息
	public function message($name)
	{
		$this->message = array_merge($this->message, $name);
		return $this;
	}

	// 验证场景
	public function project($name)
	{
		$this->project = array_merge($this->project, $name);
		return $this;
	}

	public function check($data)
	{
		$rules=$this->rule;
		foreach ($rules as $key => $item) {
			// 解析验证规则
			$rule = $this->parseRule($item);
			// 是否需要必填字段 required
			if (in_array('required',$rule)) {
				$value = isset($data[$key]) ? $data[$key] : null;
				$checkRequired = $this->requiredValidator($value);
				if (!$checkRequired) {
					return $this->ValidateFail($key,'required');
				}
				// 验证成功移除该规则
				$pos = array_keys($rule,'required');
				unset($rule[array_shift($pos)]);
			}
			// 验证其他规则
			$result = $this->Validate($key, $rule, $data[$key]);	
			if (false === $result) {
				break;
			}				
		}
		
		return $result;	
	}

	private function Validate($field, $rules, $data)
	{
		// $data = [
		// 	'name'  => 'admin',
		// 	'email' => 'thinkphp@qq.com',
		// 	'age'   => 30
		// ];
		// $fiel = name,email,age
		// $data = admin
		// 
		// dd($rules);
		// array(3) {
		//   ["min"] => string(1) "5"
		//   ["max"] => string(2) "10"
		//   ["filter"] => array(2) {
		//     [0] => string(4) "trim"
		//     [1] => string(10) "strip_tags"
		//   }
		// }
		

		foreach ($rules as $key => $value) {
			switch ($key) {
				case 'max':
					// 如果是数组判断长度					
					if (is_array($data)) {
						$leng = count($data);
					} else{
						// 如果是数字判断字面大小
						// 如果是字符串判断长度
						$leng = is_numeric($data) ? intval($data) : intval( mb_strlen((string) $data) );
					}
					// $leng 当前数据长度, $value规定的数据长度					
					if ($leng > intval($value)) {
					    return $this->ValidateFail($field,$key);
					}
					break;
				case 'min':
					// 如果是数组判断长度					
					if (is_array($data)) {
						$leng = count($data);
					} else{
						// 如果是数字判断字面大小
						// 如果是字符串判断长度
						$leng = is_numeric($data) ? intval($data) : intval( mb_strlen((string) $data) );
					}
					// $leng 当前数据长度, $value规定的数据长度					
					if ($leng < intval($value)) {
					    return $this->ValidateFail($field,$key);
					}
					break;
				case 'integer': // 整形
					if (!filter_var($data, FILTER_VALIDATE_INT)) {
						return $this->ValidateFail($field,$key,$data);
					}
					break;	
				case 'number': // 数组
					if (!is_numeric($data)) {
						return $this->ValidateFail($field,$key,$data);
					}
					break;
				case 'email':
					if (!filter_var($data, FILTER_VALIDATE_EMAIL)) {
						return $this->ValidateFail($field,$key,$data);
					}
					break;
				case 'ip':
					if (!filter_var($data, FILTER_VALIDATE_IP,[FILTER_FLAG_IPV4,FILTER_FLAG_IPV6])) {
						return $this->ValidateFail($field,$key,$data);
					}
					break;	
				case 'url':
					if (!filter_var($data, FILTER_VALIDATE_URL)) {
						return $this->ValidateFail($field,$key,$data);
					}
					break;
				case 'json':
					json_decode($data);
					if (!(json_last_error() === JSON_ERROR_NONE)) {
						return $this->ValidateFail($field,$key,$data);
					}
					break;
				case 'array':
					if (!is_array($data)) {
						return $this->ValidateFail($field,$key,$data);
					}
					break;
				case 'in':
					if (!in_array($data,$value)) {
						return $this->ValidateFail($field,$key,$data);
					}
					break;
				case 'notIn':
					if (in_array($data,$value)) {
						return $this->ValidateFail($field,$key,$data);
					}
					break;
				case 'alpha': //只字母
					if (!preg_match('/^[A-Za-z]+$/',$data)) {
						return $this->ValidateFail($field,$key,$data);
					}
					break;
				case 'alphaNum': //只字母和数字
					if (!preg_match('/^[A-Za-z0-9]+$/',$data)) {
						return $this->ValidateFail($field,$key,$data);
					}
					break;	
				case 'chs': //只中文
					if (!preg_match('/^[\x{4e00}-\x{9fa5}]+$/u',$data)) {
						return $this->ValidateFail($field,$key,$data);
					}
					break;	
				case 'chsAlpha': //只中文和字母
					if (!preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z]+$/u',$data)) {
						return $this->ValidateFail($field,$key,$data);
					}
					break;	
				case 'chsAlphaNum': //只中文和字母和数字
					if (!preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u',$data)) {
						return $this->ValidateFail($field,$key,$data);
					}
					break;
				case 'regex': //只中文和字母和数字
					if (0 !== strpos($value, '/') && !preg_match('/\/[imsU]{0,4}$/', $value)) {
					    // 不是正则表达式则两端补上/
					    $value='/^'.$value.'$/';
					}
					if (!preg_match($value, (string) $data)) {
						return $this->ValidateFail($field,$key,$data);
					}
					break;
				// TODO:: 数据过滤
				case 'filter': 
					
					break;
				default:
					throw new Exception("{$key} 验证规则不存在");
					break;
			}
		}
	}


	/**
	 * 验证失败 
	 * @param string $key 数据中的键 eg:name
	 * @param string $rule 规则名称 eg:required
	 * 一起就是 name.required
	 */
	private function ValidateFail($key='', $rule='', $data='')
	{
		if ( isset($this->message[$key .'.'. $rule]) ) {
			$message = $this->message[$key .'.'. $rule];

		} elseif( isset($this->message[$key])){
			$message = $this->message[$key];

		} else {
			$message = "未定义错误消息 key:{$key} rule:{$rule}";
		}
		$this->error = ['now-data'=>$data, 'fail-key'=>$key, 'use-rule'=>$rule, 'msg'=>$message];
		return false;
	}

	// 验证不能为空
	public function requiredValidator($attribute, $trim = true) 
	{
	    if(is_array($attribute)){
	        $res = empty($attribute)?false:true;
	    }elseif(is_string($attribute)){
	        $attribute = $trim?trim($attribute):$attribute;
	        $res = ($attribute==='')?false:true;
	    }else{
	        $res = empty($attribute)?false:true;
	    }
	    return $res;
	}

	/**
	 * 解析验证规则
	 * @param  string|array $rule 验证规则
	 * @return array 返回数组
	 */
	private function parseRule($rule)
	{
		$ruleArray = [];
		if (is_array($rule)) {
			return $rule;
		}
		// 'name'  => 'require|max(25)|in(1,2,5)' 拆分成数组
		$rule = explode('|', trim($rule,'|'));
		// 循环查找已拆分数组里其他验证条件
		foreach ($rule as $key => $item) {
			$item = trim($item);
			// 正则匹配 max(25)
			// array(3) {
			//   [0] => string(7) "max(22)"
			//   [1] => string(3) "max"
			//   [2] => string(2) "22"
			// }
			preg_match_all('/(\w+)\\((.*)\\)/',$item,$itemArray);	
			$itemArray = arr_foreach($itemArray);
			array_shift( $itemArray );				
			if (empty($itemArray)) {
				$ruleArray[$item] = $item;
			} else {	
				$ruleArray[$itemArray[0]] = (false !== strpos($itemArray[1], ',')) 
											? explode(',',$itemArray[1]) 
											: $itemArray[1];
			}
		}
		return $ruleArray;
	}

	// 获取错误消息
	public function getMessage()
	{
		return $this->error;
	}
}
