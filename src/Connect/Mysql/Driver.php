<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0

namespace Prfox\Connect\Mysql;
use Prfox\Connect\Connection;

class Driver extends Connection
{
    // 创建连接
    protected function createConnect()
    {
        $config = app('config')->get('mysql');
        $driver = ucfirst(strtolower($config['driver']));
        $db_con = array(
            'database_type' => $config['type'],
            'server'        => $config['host'],
            'database_name' => $config['dbname'],
            'port'          => $config['port'],
            'username'      => $config['username'],
            'password'      => $config['password'],
            'charset'       => $config['charset'],
            'prefix'        => $config['prefix']
        );
        $db = '\Prfox\Connect\Mysql\Driver\\' . $driver;
        return new $db($db_con);
    }

    public function setTable($tableName)
    {
        $this->autoConnect();
        return $this->db->setTable($tableName);
    }
}