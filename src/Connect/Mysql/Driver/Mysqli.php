<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0

namespace Prfox\Connect\Mysql\Driver;

class Mysqli
{
	protected $db;
	// 数据表名称
	protected $_table = 'users';

	// 查询字段
	protected $_field = '*';

	// 查询条件
	protected $_where = array();

	// 关联表查询
	protected $_join = array();

	// 调式模式
	protected $_debug = false;

	protected $_limit = null;

	// 分页
	protected $_page = array();

	public function __construct($config)
	{
		$config = [
			'host' => $config['server'],
			'username' => $config['username'],
			'password' => $config['password'],
			'db' => $config['database_name'],
			'port' => $config['port'],
			'prefix' => $config['prefix'],
			'charset' => $config['charset']
		];
		$this->db = new MysqliDb($config);
		return $this;
	}

	public function setTable($tableName)
    {
        $this->_table = $tableName;
        return $this;
    }

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
	    $this->db->orderBy($order, $opt);
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
        $this->_limit = $limit;
        return $this;
    }

    /**
     * 分页
     * @param  integer $page_size 每页显示行数
     * @return mixed
     */
    public function page($page_size = 10) 
    {
    	$this->db->pageLimit = $page_size;
        $router = app('request')->route('params');
        $page_now = $router[app('config')->get('app.page_code')];
        
        // $page_now = isset($_GET['page']) ? intval($_GET['page']) : 1;
    	$info = $this->db->paginate($this->_table, $page_now, $this->_field);
    	// 执行分页函数
    	if ($info) {    		
    		$pageClass = new \Prfox\Base\Page($this->db->totalCount,$page_size);
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
    	$this->db->where($this->_where);
    	return $this->db->delete($this->_table);
    }

    /**
     * 更新数据
     * @param  array $data 数据源
     * @return init 返回影响行数
     */
    public function update($data)
    {
    	return $this->db->update($this->_table,$data);
    }

    /**
     * 新增数据
     * @param  array $data 数据源
     * @return int 返回新增数据的id
     */
    public function insert($data)
    {
    	return $this->db->insert($this->_table,$data);
    }

    /**
     * 执行sql语句
     * @param  string $sql 执行的sql语句
     * @param  array $map 参数绑定
     * @return mixed 返回查询结果
     */
    public function query($sql,$map = array())
    {
    	return $this->rawQuery($sql,$map);
    }

    /**
     * 查询单条数据
     * @return mixed
     */
	public function findOne()
    {
    	return $this->db->getOne($this->_table, $this->_field);
    }

    /**
     * 查询多条数据
     * @return mixed
     */
    public function findAll()
    {
        return $this->db->get($this->_table, $this->_limit ? $this->_limit : null, $this->_field);
    }

    /**
     * 自动事务提交
     * @param  \Closure $closure 执行的匿名函数
     * @return void
     */
    public function transaction(\Closure $closure)
    {
    	$this->beginTransaction();
    	try {
    	    $closure();
    	    // 提交事务
    	    $this->commit();
    	} catch (\Throwable $e) {
    	    // 回滚事务
    	    $this->rollBack();
    	    throw $e;
    	}
    }

    // 开启事务
    public function beginTransaction()
    {
    	$this->db->startTransaction();
    }

    // 提交事务
    public function commit()
    {
        $this->db->commit();
    }

    // 回滚事务
    public function rollBack()
    {
        $this->db->rollback();
    }

    // 数据返回类型 
    // 默认是数组，开启此项则返回对象
    public function toObject()
    {
    	$this->db = $this->db->objectBuilder();
    	return $this;
    }

    /*****************************  调试信息  *****************************/
    public function debug()
	{
		$this->db = $this->db->debug();
		return $this;
	}

    public function info()
    {
    	$mysql = $this->db->mysqli();
    	return [
			'server' => $mysql->stat,
			'driver' => 'mysql',
			'client' => $mysql->client_info,
			'version' => $mysql->server_info,
			'connection' => $mysql->host_info,
			'dns' => null
    	];
    }

    public function last()
    {
    	return $this->db->getLastQuery();
    }

    public function log()
    {
    	return $this->db->setTrace (true);
    }

    public function error()
    {
    	return $this->db->getLastError();
    }
}