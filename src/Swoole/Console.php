<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0

namespace Prfox\Swoole;
use Prfox\Swoole\Helper;

class Console
{
	// 项目入口
	static public $rootPath;

	// swoole 配置文件
	static public $config;

	static public $hot_reload = false;

	// 命令参数
	protected $command;

	public function __construct($rootPath)
	{
		$this->checkENV();
		// 移除参数中第一个数组（当前文件名）
		array_shift($_SERVER['argv']);
		$this->command  = strtolower(join($_SERVER['argv']," "));		
		self::$config   = require($rootPath.'/config/swoole.php');
		self::$rootPath = $rootPath;
	}

	// 环境检测
	private function checkENV()
	{
		if (php_sapi_name() != 'cli') {
			die("请在命令行模式下运行\n");
		}
		if (!extension_loaded('swoole')) {
			die("无法运行，未安装Swoole\n");
		}
		if(version_compare(phpversion(),'7.1.0','<')) {
			die("php version must >= 7.1.0, current php version = " .phpversion()."\n");
		}
		if(version_compare(swoole_version(),'4.2.0','<')) {
			die("swoole version must >= 4.2.0, current swoole version =".swoole_version()."\n");
		}
		if(function_exists('apc_clear_cache')){
		    apc_clear_cache();
		}
		if(function_exists('opcache_reset')){
		    opcache_reset();
		}
	}

	// 控制台运行 解析命令执行方法
	public function run()
	{
		$command = $this->command;
		// http start'命令可能携带 -d -u参数，所以做个前置匹配，然后在 case 中解析参数
		if (false !== strpos($command, 'http start')) {
			$old_command = $command;
			$command     = 'http start';
		}		
		switch ($command) {
			case 'http start':
				$option = array(
					// 是否守护进程
					'daemon' => (false !== strpos($old_command, '-d')) ? true : false,
					// 是否热加载
					'hot_reload' => (false !== strpos($old_command, '-h')) ? true : false,
				);
				static::$hot_reload = $option['hot_reload'];
				static::$config['daemonize'] = $option['daemon'];
				return $this->httpStart($option);
				break;
			case 'http stop':
				return $this->httpStop();
				break;
			case 'http status':
				return $this->httpStatus();
				break;
			case 'http reload':
				return $this->httpReload();
				break;
			// 查看日志
			case 'http log':
				$log = file_get_contents(self::$config['log_file']);
				Helper::clear();
				return Helper::println($log);
				break;
			case 'clear':break;
			case 'make':break;

			// 其他命名都转向开始界面
			case '-h':
			case '--help':
			default:
				return Helper::startUI();		
				break;
		}
		return;
	}

	// 柔性重启机制
	protected function httpReload()
	{
		$pid = Helper::getPid( self::$config['pid_file'] );
		if ($pid) {
		    Helper::killPid($pid, SIGUSR1);
			$time = date('Y-m-d H:i:s', time());
			// return Helper::println("\033[1;36m ●  ({$time})  Prfox reload completed. \033[0m");			
			return Helper::println("\033[1;32mPrfox reload completed \033[0m");
		}
		return Helper::println("\033[33m ●  (Prfox not running)\033[0m");
	}

	// 启动服务
	protected function httpStart($option)
	{
		$pid = Helper::getPid( self::$config['pid_file'] );
		if ($pid && Helper::isRun($pid)) {
			return Helper::println("\033[1;32m ●  Prfox is running \033[0m");
		}
		
		$server = new \Prfox\Swoole\Register($option);
		$server->start();
	}

	// 停止服务
	protected function httpStop()
	{
		$pid = Helper::getPid( self::$config['pid_file'] );
		if ($pid && Helper::isRun($pid)) {
			Helper::killPid($pid);
			\Swoole\Process::wait();			
			return Helper::println("\033[1;36mPrfox stop completed \033[0m");
		}else{
			$this->execBash(['master','manager']);
		}		
		return Helper::println("\033[33m ●  (Prfox not running)\033[0m");
	}

	// shell查找pid并kill 防止垃圾代码造成进程浪费
	// $find = master or manager
	private function execBash($finds)
	{
		foreach ($finds as $value) {
			// $pid = exec("ps -ef|grep 'prfox: {$value}'|grep -v grep|awk -F' ' '{print $2}'");
			$pid = exec("pidof 'prfox: {$value}'");
			if (intval($pid)) exec("kill {$pid}");
		}
	}

	// 查看状态
	protected function httpStatus()
	{
		$pid = Helper::getPid( self::$config['pid_file'] );
		if ($pid && Helper::isRun($pid)) {
			return Helper::runUI();
		}
		return Helper::println("\033[31m ●  (Prfox not running)\033[0m");
	}
}