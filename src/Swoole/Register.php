<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0
// 
// Swoole http服务注册

namespace Prfox\Swoole;

class Register
{
	protected $server;

	protected $config;

	public function __construct($option)
	{			
		$this->config = Console::$config;		
		$this->config['daemonize'] = $option['daemon'];
		// 热加载应用层代码，把 max_request 设为 1，
		// 每次请求后该work都被销毁，新请求来时系统又自动创建，从而达到修改代码立即生效的热加载效果
		// 注意：只对应用层的代码有效，并且要求以下两点
		// 1、关闭 PHP OPcache
		// 2、生产环境禁止使用，否则性能严重下降
		$option['hot_reload'] && $this->config['max_request'] = 1;

		// 日志分割
		$this->logSplit();
	}

	// 日志分割
	// filesize 获取结果是字节
	// 1兆字节(mb)= 1048576 字节(b)
	protected function logSplit()
	{
		$file = $this->config['log_file'];
		$path = dirname($file); // 获取日志目录
		$size = is_file($file) ? filesize($file) : 0; // 获取日志大小
		// 日志分割阈值	默认 10 MB		
		$logsplit = 1048576 * (intval($this->config['logsplit']) ? intval($this->config['logsplit']) : 10);	
		if ( $size >= $logsplit ) {
			// 储存备份日志的目录
			$dir = $path . DIRECTORY_SEPARATOR . 'logsplit';
			// 备份日志的名称
			$new = $dir . DIRECTORY_SEPARATOR . date('Y-m-d His') . '.log';
			if (!is_dir($dir)) {
				mkdir($dir, 0777, true);
			}
			rename($file,$new);	
		}
	}

	public function start()
	{		
		$this->init();
		$this->onStart();
		$this->onManagerStart();
		$this->onWorkerStart();
		$this->onRequest();
		$this->run();
	}

	protected function init()
	{				
		$this->server = new \Swoole\Http\Server($this->config['host'], $this->config['port']);
		$this->server->set($this->config);
	}

	
    
    // 服务器启动事件
    // 创建 master 进程
	protected function onStart()
	{
		$this->server->on('Start', function ($server) {
			$opt = '';
			if (Console::$hot_reload) $opt .= ' -h';
			if ($this->config['daemonize']) $opt .= ' -d';
			// 进程命名，方面后续获取id
			@cli_set_process_title("prfox: master" . $opt);
			// 把master进程id写入文件
			// linux系统下获取一个进程id方法很多
			// ps -ef|grep 'prfox: master'|grep -v grep|awk -F' ' '{print $2}'
			// pidof 'prfox: master'
			\Prfox\Swoole\Helper::savePid($this->config['pid_file'], getmypid());
		});
	}

    // 管理进程启动事件
    // 创建 manager 进程
	protected function onManagerStart()
	{
        $this->server->on('ManagerStart', function ($server) {            
            @cli_set_process_title("prfox: manager");
        });
	}

    // 进程启动事件
    // 创建 worker 进程
	protected function onWorkerStart()
	{
		$this->server->on('WorkerStart', function ($server, $workerId) {
		    if ($workerId < $server->setting['worker_num']) {
		    	@cli_set_process_title("prfox: worker #{$workerId}");
		    } else {
		    	@cli_set_process_title("prfox: task #{$workerId}");
		    }
		    // 实例化App
			new \Prfox\Swoole\Application(Console::$rootPath);
		});
	}

    // 请求响应事件
    // 主要逻辑处理
	protected function onRequest()
	{
		$this->server->on('request', function ($request, $response) 
		{
			try {
				\Prfox::app('app')->initialize($request, $response);
				\Prfox::app('app')->run();
			} catch (\Throwable $e) {
				\Prfox\Base\Exception::handler($e);
			}
		});
	}

	// 运行服务
	protected function run()
	{
		\Prfox\Swoole\Helper::runUI(true);
		echo 'Start time: ' . date('Y-m-d H:i:s',time()) . "\n";
		$this->server->start();
	}
}