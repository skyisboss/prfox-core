<?php
 // 公共函数

if (! function_exists('app')) {
    function app($make = '')
    {
        return \Prfox::app($make);
    }
}

if (! function_exists('env')) {
    function env($key = '')
    {
        //return \Prfox\Base\Config::getEnv($key);
        return app('config')->getEnv($key);
    }
}

if (! function_exists('pp')) {
    function pp($val,$is_exit = false)
    {
        ob_start();
        var_dump($val);
        $val = ob_get_clean();
        $val = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $val);
        $val = preg_replace('/\[*\:\"(.*)\] \=\>/m', '] =>', $val);
        //$val = preg_replace('/\[*\:\"(.*)\"\:/m', ':', $val);          
        if ('fpm' == app('app')->getRunEnv()) {
             echo '<pre>'.$val.'</pre>';
             if ($is_exit) exit();
        } else {
            app('response')->addHeader('Content-Type', 'text/html; charset=utf-8');
            $response = app('response')->getResponse();
            $response->write('<pre>'.$val.'</pre>');
        }
    }
}
if (! function_exists('dd')) {
    function dd($var,$is_exit = true)
    {
        pp($var,$is_exit);
    }
}



// 多维数组变为一维数组
function arr_foreach($array,$return=[]){
    array_walk_recursive($array,function($value)use(&$return){$return[]=$value;});
    return $return;
}

function debug()
{
	if (false == app('config')->get('app.app_debug')) return;

	$time = round((microtime(true) - app('app')->startTime())*1000, 2);
	$memo = round((memory_get_usage() - app('app')->startMem()) / 1024, 2);
	$req = number_format(1 / number_format((microtime(true) - app('app')->startTime()), 6), 2) . 'req/s';
	$router = app('request')->route();

	$info  = "模块：{$router['module']} 控制器：{$router['controller']} 方法：{$router['action']}\\n";
	$info .= "运行时间：{$time} 毫秒, 占用内存：{$memo}k 吞吐率：{$req}\\n";
	return '<script>console.log("'.$info.'");</script>';
}