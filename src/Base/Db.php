<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0
// 
// 数据库

namespace Prfox\Base;
use Prfox\Base\Exception;
use Prfox\Base\Db\Mysql\Medoo;

class Db
{
    // 类实例
    protected static $instance;

    // 数据库实例
    protected $database;

    // 数据表名称
    protected $table;

    // 查询字段
    protected $field = '*';

    // 查询条件
    protected $where = array();

    // 关联表查询
    protected $join = array();

    // 调式模式
    protected $debug = false;

    // 分页
    protected $page = array();

    public function __construct()
    {
    	static::$instance = $this;
    	$config = app('config')->get('mysql');    
    	$this->database = new Medoo(array(
    		'database_type' => $config['type'],
    		'server'        => $config['host'],
    		'database_name' => $config['dbname'],
    		'port'          => $config['port'],
    		'username'      => $config['username'],
    		'password'      => $config['password'],
    		'charset'       => $config['charset'],
    		'prefix'        => $config['prefix']
    	));

        // $test = $this->database->select('users',
        //     // ["[<]groups" => 'id'],
        //     //["[<]groups" => ["id"]],
        //     ["[<]groups" => ['group' => 'id'],],
        //     ['groups.id','groups.title','groups.group_type'],
        //     ['users.id' => 1]
        // );
        // echo $this->database->last();
        // dd($test);


    }

    /**
     * 设置数据表名
     * @param  string $table 表名称
     */
    static public function table(string $table)
    {
        if (!is_object(static::$instance)) {
            new static();
        }
        static::$instance->table = $table;
        return static::$instance;
    }

    public function where($where, $opt = null)
    {
        if (empty($opt)) {
            $this->where = $where;
        } else {
            $this->where = array($where=>$opt);
        }
        return $this;
    }

    public function field($field = '*')
    {
        if (!is_array($field)) {
            $field = explode(',',$field);
            $this->field = (count($field)-1) ? $field : $field[0];
        } else {
            $this->field = $field;
        }        
        return $this;
    }

    public function order($order, $opt = null)
    {
        if (!empty($opt)) {
            $opt = strtoupper($opt) == 'DESC' ? $opt : 'ASC';
            $order = array($order=>$opt);
        }
        $order = array("ORDER" => $order);
        $this->where = array_merge($order,$this->where);
        return $this;
    }

    public function limit($limit = 1, $offset = null)
    {
        if (!empty($offset)) {
            $limit = array($limit,$offset);
        }
        $limit = array("LIMIT" => $limit);
        $this->where = array_merge($limit,$this->where);
        return $this;
    }

    // join 表关联查询
    /**
     * 控制器中使用示例
     * $info = Db::table('users (u)')
            ->join(['left' => 'groups (g)'],['u.group' => 'id'])
            ->field('*')
            ->where(['u.id' => 1])
            ->findAll();
        对应原型方法
        $info = $this->database->select('users(u)',
            //["[<]groups (g)" => 'id'],
            //["[<]groups" => ["id"]],
            ["[<]groups (g)" => ['u.group' => 'id'],],
            '*',
            // ['g.id','g.title','g.group_type'],
            ['u.id' => 1]
        );
     */
    public function join($join,$opt)
    {
        // [>] == LEFT JOIN
        // [<] == RIGH JOIN
        // [<>] == FULL JOIN
        // [><] == INNER JOIN
        if (is_array($join)) {
            foreach ($join as $key => $value) {
                switch ($key) {
                    case 'left':
                        $join = '[>]'.$value;
                        break;
                    case 'right':
                        $join = '[<]'.$value;
                        break;
                    case 'full':
                        $join = '[<>]'.$value;
                        break;
                    default:
                        $join = '[><]'.$value;
                        break;
                }
            }
        }
        $this->join = [ $join => $opt];
        return $this;
    }


    /**
     * 分页
     * @param  integer $page 当前页   
     * @param  integer $list 每页显示数据量
     */
    public function pagination($pages = 1, $list = 10) 
    {
        // 获取总数
        $total = $this->database->count($this->table,'*');
        $page = new \Prfox\Base\Pagination($total,$list);
        $this->page = $page->showpage();

        $pages = ($pages - 1) * $list;
        return $this->limit($pages, $list);
    }


    public function findOne()
    {
        $query = $this->parse_Query(__FUNCTION__);
        return $this->exec($query);
    }

    public function findAll()
    {
        $query = $this->parse_Query(__FUNCTION__);
        return $this->exec($query);
    }

    // 调试模式 只输出sql语句 不执行sql操作
    public function debug()
    {
        $this->debug = true;
        return $this;
    }

    // 更新操作 成功返回受影响的行数
    public function update($data)
    {
        $query = $this->parse_Query(__FUNCTION__);
        return $this->exec($query);
    }

    // 删除操作 成功返回受影响的行数
    public function delete()
    {
        $query = $this->parse_Query(__FUNCTION__);
        return $this->exec($query);
    }

    // 集合查询
    public function __call($method, $arg) {
        $query = $this->parse_Query($method,$arg);
        return $this->exec($query);
    }
    // 执行sql语句 支持参数绑定
    public function query($sql,$paramBind = array())
    {
        $query = $this->parse_Query(__FUNCTION__ , $paramBind, $sql);
        return $this->exec($query);
    }

    /*****************************  调试模式  *****************************/

    // 获取最后一条执行的sql语句
    static public function last()
    {
        return static::$instance->database->last();
    }

    // 获得最后一个执行的错误记录.
    static public function error()
    {
        return static::$instance->database->error();
    }

    // 获取所有执行的查询记录
    static public function log()
    {
        return static::$instance->database->log();
    }

    // 获取数据库信息
    static public function info()
    {
        return static::$instance->database->info();
    }

    // 构造查询
    private function parse_Query(string $method, array $data =array(),$where ='',$field ='')
    {
        return array(
            'method' => $method,
            'data' => $data,
            'where' => empty($where) ? $this->where : $where,
            'field' => empty($field) ? $this->field : $field
        );
    }

    // 执行查询操作
    private function exec()
    {
        // 获取当前传递的参数列表
        $arg= func_get_args();
        $arg = array_shift($arg);

        // 设置操作句柄
        $database = $this->database;
        if($this->debug){
            $database = $this->database->debug();
        }

        // TODO:此处待优化
        switch ($arg['method']) {
            case 'findOne':
                $result = $database->get($this->table, $arg['field'], $arg['where']);
                break;
            case 'findAll':
                if (empty($this->join)) {
                    $result = $database->select($this->table, $arg['field'], $arg['where']);
                } else {
                    // 链表查询
                    $result = $database->select($this->table,$this->join, $this->field,$arg['where']);
                }
                // 是否启用分页
                if (!empty($this->page)) {
                   $result = ['data' => $result, 'page' => $this->page];
                }               
                break;
            case 'insert':
                $database->insert($this->table , $arg['data']);
                $result = $database->id();
                break;
            case 'update':
                $database->update($this->table, $arg['data'], $arg['where']);
                $result = $database->rowCount();
                break;
            case 'delete':
                $database->delete($this->table,$arg['where']);
                $result = $database->rowCount();
                break;
            case 'query':
                $sql = $arg['where'];
                $bind = $arg['data'];
                $result = $database->query($sql,$bind);
                break;
            // 聚合查询
            case 'count':
            case 'avg':
            case 'max':
            case 'min':
            case 'sum':
                $method = $arg['method'];
                $column = array_shift($arg['data']);
                $result = $database->$method($this->table,$column,$this->where);
                break;
            default:
                throw new Exception("{$arg['method']} 查询方法不存在");
        }
        return $result;
    }


}