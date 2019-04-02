<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0

namespace Prfox\Connect\Mysql\Driver;

class Pdo
{
    protected $db;
    protected $buildObject = false;
	// 数据表名称
	protected $_table;

	// 查询字段
	protected $_field = '*';

	// 查询条件
	protected $_where = array();

	// 关联表查询
	protected $_join = array();

	// 调式模式
	protected $_debug = false;

	// 分页
	protected $_page = array();

    public function __construct($config)
    {
        $this->db = new Medoo($config);
        return $this;
    }

	/**
	 * 设置表名
	 * @param  string $tableName 数据表名称
	 * @return void
	 */
    public function setTable($tableName)
    {
        $this->_table = $tableName;
        return $this;
    }

	/**
	 * 条件查询
	 * @param  string $where 条件
	 * @param  string $opt 额外参数
	 * @return void
	 */
	public function where($where, $opt = null)
	{
	    if (empty($opt)) {
	        $this->_where = $where;
	    } else {
	        $this->_where = array($where=>$opt);
	    }
	    return $this;
	}

	/**
	 * 字段查询
	 * @param  string $field 字段
	 * @return void
	 */
	public function field($field = null)
	{
        if ($field != $this->_field && !empty($field)) {
            if (false !== strpos($field, ',')) {
                $this->_field = explode(',',$field);
            } else {
                $this->_field = array($field);
            }
        }
	    return $this;
	}

	/**
	 * 排序查询
	 * @param  string $order 排序字段
	 * @param  string $opt 额外参数
	 * @return void
	 */
	public function order($order, $opt = null)
	{
	    if (!empty($opt)) {
	        $opt = strtoupper($opt) == 'DESC' ? $opt : 'ASC';
	        $order = array($order=>$opt);
	    }
	    $order = array("ORDER" => $order);
	    $this->_where = array_merge($order,$this->_where);
	    return $this;
	}

	/**
	 * 限定数据
	 * @param  integer $limit 起始限定
	 * @param  integer $offset 偏移量
	 * @return void
	 */
	public function limit($limit = 1, $offset = null)
    {
        if (!empty($offset)) {
            $limit = array($limit,$offset);
        }
        $limit = array("LIMIT" => $limit);
        $this->_where = array_merge($limit,$this->_where);
        return $this;
    }

    /**
     * 分页 
     * @param  integer $page_size 每页显示行数
     * @return mixed
     */
    public function page($page_size = 10) 
    {
        $router = app('request')->route('params');
        $page_now = $router[app('config')->get('app.page_code')];

        $page_now = ($page_now - 1) * $page_size;
        $this->limit($page_now, $page_size);       
        $info = $this->findAll();
        if ($info) {
            unset($this->_where['LIMIT']);            
            $page_count = $this->db->count($this->_table,'*',$this->_where);
            $pageClass = new \Prfox\Base\Page($page_count,$page_size);
            $html = $pageClass->show();
        } else {
            $info = null;
            $html = null;
        }
        return ['data' => $info, 'page' => $html];

    }

    /**
     * 删除数据
     * @return int 返回影响行数
     */
    public function delete()
    {
    	$this->db->delete($this->_table,$this->_where);
    	return $this->db->rowCount();
    }

    /**
     * 更新数据
     * @param  array $data 数据源
     * @return init 返回影响行数
     */
    public function update($data)
    {
    	$this->db->update($this->_table,$data,$this->_where);
    	return $this->db->rowCount();
    }

    /**
     * 新增数据
     * @param  array $data 数据源
     * @return int 返回新增数据的id
     */
    public function insert($data)
    {
    	$this->db->insert($this->_table,$data);
    	return $this->db->id();
    }

    /**
     * 执行sql语句
     * @param  string $sql 执行的sql语句
     * @param  array $map 参数绑定
     * @return mixed 返回查询结果
     */
    public function query($sql,$map = array())
    {
    	return $this->db->query($sql,$map);
    }

    /**
     * 查询单条数据
     * @return mixed
     */
	public function findOne()
    {
        return $this->db->get($this->_table, $this->_field, $this->_where);
    }

    /**
     * 查询多条数据
     * @return mixed
     */
    public function findAll()
    {
        $result = $this->db->select($this->_table, $this->_field, $this->_where);
        return $this->buildReturn($result);
    }

    /**
     * 自动事务提交
     * @param  \Closure $closure 执行的匿名函数
     * @return void
     */
    public function transaction(\Closure $closure)
    {
    	$this->db->action($closure);
    }

    // 开启事务
    public function beginTransaction()
    {
    	$this->db->pdo->beginTransaction();
    }

    // 提交事务
    public function commit()
    {
        $this->db->pdo->commit();
    }

    // 回滚事务
    public function rollBack()
    {
        $this->db->pdo->rollBack();
    }

    // 数据返回类型 
    // 默认是数组，开启此项则返回对象
    public function toObject()
    {
        // $this->db = $this->db->objectBuilder();
        $this->buildObject = true;
        return $this;
    }

    // 构建返回数据
    private function buildReturn($row)
    {
        if (!$this->buildObject) return $row;
        $result = new \stdClass ();
        $results = array();
        foreach ($row as $key => $val) {
            if (is_array($val)) {
                $result->$key = new \stdClass ();
                foreach ($val as $k => $v) {
                    $result->$key->$k = $v;
                    // $result->$key->$k = is_numeric($v) ? (int) $v : $v;
                }
            } else {
                $result->$key = $val;
                // $result->$key = is_numeric($val) ? (int) $val : $val;
            }
            $results[] = end($result);
            // array_push($results, end($result));
        }
        return $results ;
    }

    public function __call($method, $args)
    {
        return call_user_func_array(array($this->db, $method), $args);
    }
}