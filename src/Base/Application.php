<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0
// 
// 框架应用程序，Prfox框架的组件实例由容器来管理，从而达到依赖注入并解决耦合问题
// 容器类位于 Prfox\Base\Container, 由 \Prfox 托管

namespace Prfox\Base;

class Application
{
	protected $rootPath;

	protected $genTime;

	protected $genMem;

	protected $runEnv = 'fpm';

	public function __construct($rootPath)
	{
		$this->rootPath = $rootPath;
		$this->genTime  = microtime(true);
        $this->genMem   = memory_get_usage();
        \Prfox::registerComponents($this);
	}

	// 启动框架
	public function run()
	{	
		// 解析路由，成功则返回一个控制器类的命名空间路径，以及url携带的参数
		list($callback,$param) = app('router')->parse();

		// 路由调度，执行控制器类的初始化，并传入参数
		// TODO: 考虑加入中间件
		$result = app('router')->dispatch($callback,$param);

		// 接收响应控制器结果并输出到浏览器
		app('response')->addBody($result)->send();
	}

	// 获取根目录
	public function getRootPath()
	{
		return $this->rootPath;
	}

	// 设置运行环境
	public function setRunEnv($run)
	{
		return $this->runEnv = $run;
	}

	// 获取运行环境
	public function getRunEnv()
	{
		return $this->runEnv;
	}

	public function startTime()
	{
		return $this->genTime;
	}
	public function startMem()
	{
		return $this->genMem;
	}
}