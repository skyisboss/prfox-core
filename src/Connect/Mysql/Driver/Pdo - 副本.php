<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0

namespace Prfox\Connect\Mysql\Driver;

class Pdo extends Medoo
{
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
	public function field($field = '*')
	{
	    if (!is_array($field)) {
	        $field = explode(',',$field);
	        $this->_field = (count($field)-1) ? $field : $field[0];
	    } else {
	        $this->_field = $field;
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
     * @param  integer $page 当前页   
     * @param  integer $list 每页显示数据量
     * @return mixed
     */
    public function page($pages = 1, $list = 10) 
    {
        // 获取总数
        // $total = $this->count($this->_table,'*');
        // $pageClass = new \Prfox\Base\Pagination($total,$list);
        // $this->_page = $pageClass->showpage();

        $pages = ($pages - 1) * $list;
        $this->limit($pages, $list);
        return $this->findAll();
    }

    /**
     * 删除数据
     * @return int 返回影响行数
     */
    public function delete()
    {
    	$this->deleteData($this->_table,$this->_where);
    	return $this->rowCount();
    }

    /**
     * 更新数据
     * @param  array $data 数据源
     * @return init 返回影响行数
     */
    public function update($data)
    {
    	$this->updateData($this->_table,$data,$this->_where);
    	return $this->rowCount();
    }

    /**
     * 新增数据
     * @param  array $data 数据源
     * @return int 返回新增数据的id
     */
    public function insert($data)
    {
    	$this->insertData($this->_table,$data);
    	return $this->id();
    }

    /**
     * 执行sql语句
     * @param  string $sql 执行的sql语句
     * @param  array $map 参数绑定
     * @return mixed 返回查询结果
     */
    public function query($sql,$map = array())
    {
    	return $this->querySql($sql,$map);
    }

    /**
     * 查询单条数据
     * @return mixed
     */
	public function findOne()
    {
        return $this->get($this->_table, $this->_field, $this->_where);
    }

    /**
     * 查询多条数据
     * @return mixed
     */
    public function findAll()
    {
        return $this->select($this->_table, $this->_field, $this->_where);
    }

    /**
     * 自动事务提交
     * @param  \Closure $closure 执行的匿名函数
     * @return void
     */
    public function transaction(\Closure $closure)
    {
    	$this->action($closure);
    }

    // 开启事务
    public function beginTransaction()
    {
    	$this->pdo->beginTransaction();
    }

    // 提交事务
    public function commit()
    {
        $this->pdo->commit();
    }

    // 回滚事务
    public function rollBack()
    {
        $this->pdo->rollBack();
    }
}