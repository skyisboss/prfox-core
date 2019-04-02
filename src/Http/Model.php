<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0

namespace Prfox\Http;
use Prfox\Connect\Db;

class Model
{
	// 表名
	protected $table;
	// 主键
	protected $pk = 'id';
	// db处理器
	protected $handeler;

 	public function __construct()
 	{
 	    // 判断是否定义数据表
 	    if (empty($this->table)) {
 	    	// 如果未定义数据表，使用文件名对对应表名
 	    	$this->table = 'users';//strtolower(pathinfo(get_called_class(), PATHINFO_FILENAME));
 	    }
 	    $this->handeler = Db::table($this->table);
 	    return $this;
 	}

 	// 获取单条数据
 	public function get(int $id)
 	{
 		return $this->handeler->where([$this->pk => $id])->findOne();
 	}

 	// 获取全部数据
 	public function all($condition = null)
 	{
 		return $this->handeler->where($condition)->findAll();
 	}

 	// 新增数据
 	public function add(array $data)
 	{
 		return $this->handeler->insert($data);
 	}

 	// 删除数据
 	public function delete($condition)
 	{
 		return $this->handeler->where($condition)->delete();
 	}

 	// 更新数据
 	public function update(array $data)
 	{
 		return $this->handeler->update($data);
 	}
}