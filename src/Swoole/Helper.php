<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0

namespace Prfox\Swoole;

class Helper
{
	const VERSION = \Prfox::VERSION;

	// 清空屏幕
	static public function clear()
	{
		$arr = array(27, 91, 72, 27, 91, 50, 74);
		foreach ($arr as $a) 
		{
		    //print chr($a);
		}
	}

	// 终端打印
	static public function println($value)
	{
		echo "{$value}\n";
		return;
	}

	// 计算时间差
	static public function timediff($begin_time)
	{
		$end_time = time();
		if($begin_time < $end_time){
		    $starttime = $begin_time;
		    $endtime = $end_time;
		}else{
		    $starttime = $end_time;
		    $endtime = $begin_time;
		}
		//计算天数
		$timediff = $endtime-$starttime;
		$days = intval($timediff/86400);
		//计算小时数
		$remain = $timediff%86400;
		$hours = intval($remain/3600);
		//计算分钟数
		$remain = $remain%3600;
		$mins = intval($remain/60);
		//计算秒数
		$secs = $remain%60;
		$res = $days ." days ". $hours ." hours ". $mins ." min ". $secs ." sec";
		return $res;
	}

	static public function getSize($file)
	{
		$size = is_file($file) ? filesize($file) : 0;		

		if ($size < 1024) {
			$size_str = $size . ' b';
		}elseif ($size >= 1024 && $size < 1048567) {
			$size_str = round($size/1024, 2) . ' Kb';
		}else{
			$size_str = round($size/1024/1024, 2) . ' Mb';
		}

		return $size_str;
	}

	static public function isRun($pid)
	{
		return static::killPid($pid, 0);
	}

	// 写入 PID 文件
    public static function savePid($pidFile,$pid)
    {
        $ret = file_put_contents($pidFile, $pid);
        return $ret ? true : false;
    }

	static public function getPid($file)
	{
		if (is_file($file)) {
		    $pid = file_get_contents($file);
		    if (static::isRun($pid)) {
		        return $pid;
		    }
		}
		return false;
	}

	static public function killPid($pid, $signal = null)
	{
		if (is_null($signal)) {
		    return \Swoole\Process::kill($pid);
		}
		return \Swoole\Process::kill($pid, $signal);
	}

	// 开始界面
	static public function startUI()
	{
    	$version = self::VERSION;
    	$display =<<<STRING
\033[1A\n\033[K-----------------------------\033[47;30m Prfox框架 \033[0m-----------------------------\n\033[0m
    \033[36m 使用: \033[0m
        php prfox [命令]

    \033[36m http命令: \033[0m
        \033[33m http start \033[0m      开启服务
        \033[33m http start -d \033[0m   开启服务 (守护进程模式)
        \033[33m http stop \033[0m       停止服务
        \033[33m http reload \033[0m     重新加载服务
        \033[33m http status \033[0m     查看状态
        \033[33m http log \033[0m        查看日志文件
        \033[33m -h / --help \033[0m     使用帮助

    \033[36m 其他命令: \033[0m
        \033[33m make [controller,model] \033[0m  创建类（控制器、模型）
        \033[33m clear \033[0m                    清空缓存

    \033[36m 文档: \033[0m
        \033[33m https://github.com/skyisboss/prfox \033[0m

\033[1A\n\033[K---------------------------\033[47;30m Version {$version} \033[0m---------------------------\n\033[0m

STRING;
		static::clear();
	    echo $display;
	}

	// 运行界面
	static public function runUI($is_run='')
	{
		$fox_version = static::VERSION;
		$php_version = PHP_VERSION;
		$swoole_version = swoole_version();
		$setting = \Prfox\Swoole\Console::$config;

		$loadavg = sys_getloadavg();
		foreach ($loadavg as $k=>$v) 
		{
		    $loadavg[$k] = round($v, 2);
		}
		$loadavg = implode(', ', $loadavg);

		$run_info = "\n";
    	$start_time = "";
   		$active_status   = "\033[31m ● (not running)\033[0m";
   		if ($is_run) {
   			$active_status   = "\033[1;32m ● (running)\033[0m";
   		}

   		$pid = static::getPid($setting['pid_file']);
   		if ($pid && static::isRun($pid)) {
   			$file_info = new \SplFileInfo($setting['pid_file']);
   			$time = $file_info->getMTime();
   			$run_info = date('Y-m-d H:i:s', $time) . ', '. static::timediff($time) .'';
   			$start_time = "\n\n\033[47;30m Start     time \033[0m  " . $run_info;
   			$active_status   = "\033[1;32m ● (running)\033[0m";
   		}
   		$pid_num = $pid ? '(Pid: '.$pid.')' : '';
   		$log_size = '(Size: ' .static::getSize($setting['log_file']).')';

   		// 是否热加载
   		$hot_reload = '';
   		$ret = @exec("ps -ef|grep 'prfox: master'|grep -v grep|awk -F'prfox:' '{print $2}'");  
   		// ps -ef |grep 'prfox: master'|grep -v grep|awk -F'prfox:' '{print $2}' 		
   		if (Console::$hot_reload || (false !== strpos($ret, '-h'))) {
   			$setting['max_request'] = 1;
   			$hot_reload = "\033[1;31m *\033[0m" . "\033[1;36m Hot Reload Mode\033[0m" . "\033[1;31m *\033[0m";
   		}

   		// 是否守护进程
   		$daemon = '';
   		if (Console::$config['daemonize'] || (false !== strpos($ret, '-d'))) {
   			$daemon = "\033[1;36m Daemon Mode\033[0m";
   		}
   		$active_status = $active_status . $daemon;

    	$display =<<<STRING
-----------------------------\033[47;36m  Prfox  \033[0m-----------------------------

\033[47;30m Listen  server \033[0m  {$setting['host']}:{$setting['port']} {$active_status} {$start_time} 

\033[47;30m Worker     num \033[0m  {$setting['worker_num']}

\033[47;30m Max    request \033[0m  {$setting['max_request']}{$hot_reload}

\033[47;30m Pid       file \033[0m  {$setting['pid_file']} {$pid_num}

\033[47;30m Log       file \033[0m  {$setting['log_file']} {$log_size}

\033[47;30m SYS    loadavg \033[0m  {$loadavg}

-----------------------------\033[47;36m Version \033[0m-----------------------------

\033[47;30m PHP    version \033[0m  {$php_version}

\033[47;30m Swoole version \033[0m  {$swoole_version}

\033[47;30m Prfox  version \033[0m  {$fox_version}  \033[0m  \033[33mhttps://github.com/skyisboss/prfox\033[0m
--------------------------------------------------------------------

STRING;
    	static::clear();
    	echo $display;
	}
	
}