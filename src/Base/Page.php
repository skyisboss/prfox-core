<?php
// @package    Prfox PHP-Framework
// @author     pixelFox skyisboss@gmail.com
// @link       https://github.com/skyisboss/Prfox
// @copyright  Copyright (c) 2015-2018 
// @license    http://www.apache.org/licenses/LICENSE-2.0

namespace Prfox\Base;

class Page
{
	// 总记录数
	public $data_total;

	// 每页显示行数
	public $page_size;	

	// 当前页码
    public $page_now = 1;

    // 总页数
    public $page_total;

    // 分页参数 标识码
	public $page_code;

	public $url;

	public $request_mode = 'path_info';


	public function __construct($dataTotal, $pageSize)
	{
		$this->data_total = $dataTotal;
		$this->page_size  = $pageSize;
		$this->page_code = app('config')->get('app.page_code');

		/* 计算分页信息 */ 
		$this->page_total = ceil($dataTotal / $pageSize);
		$this->page_now   = $this->now();
	}

	// 分页显示定制 
    public function config($key)
     {
     	$config  = array(         
     	    'start'  => '首页', 
     	    'prev'   => '上页', 
     	    'next'   => '下页', 
     	    'end'    => '尾页', 
     	    'total'  => '<span class="rows"> 当前 %NOW% 页，共 %COUNT% 页</span>', 
     	    'layout' => '%START% %PREV% %LINK_PAGE% %NEXT% %END% %TOTAL%', 
     	);
     	return $config[$key];
     } 

	//当前页
    public function now()
    {
    	$this->url['parse_url'] = parse_url( app('request')->fullUrl() );

        if ( $page_now = app('request')->get($this->page_code) ) { 
        	$this->request_mode = 'request_query';
        	return  (int) $page_now;
        } else {
        	$route = app('request')->route();
        	$this->url['path_info'] = strtolower($route['module']).
							        	'/'.strtolower($route['controller']).
							        	'/'.$route['action'];
        	return (int) $route['params'][$this->page_code];
        }
        return $this->page_now;
    }

	// 首页
	public function start()
	{
        $page = 1;
        $text = $this->config('start');
        if ($page != $this->page_now) {
        	return '<a class="first" href="'.$this->url($page).'">' . $text . '</a>';
        }        
        return '<a class="first not-allowed" href="javascript:;">' . $text . '</a>'; 
	}

	// 尾页
	public function end()
	{
        $page = $this->page_total;
        $text = $this->config('end');
        if ($page != $this->page_now) {
        	return '<a class="end" href="'.$this->url($page).'">' . $text . '</a>';
        }        
        return '<a class="end not-allowed" href="javascript:;">' . $text . '</a>'; 
	}

	// 上页
	public function prev()
	{
        $page = $this->page_now - 1;
        $text = $this->config('prev'); 
        if ($page > 0) {
        	return '<a class="prev" href="'.$this->url($page).'">' . $text . '</a>'; 
        }
        return '<a class="prev not-allowed" href="javascript:;">' . $text . '</a>'; 
	}

	// 下页
	public function next()
	{
        $page = $this->page_now + 1;        
        $text = $this->config('next');
        if ($page <= $this->page_total) {
        	return '<a class="next" href="'.$this->url($page).'">' . $text . '</a>'; 
        }
        return '<a class="next not-allowed" href="javascript:;">' . $text . '</a>'; 
	}

	public function url($page)
	{
		$parseUrl = $this->url['parse_url'];
		if ( $this->request_mode == 'request_query' ) { 	 // GET 传参访问	
			$query = array();
			parse_str($parseUrl['query'],$query);			
			$query[$this->page_code] = $page;
			$query = '?' .http_build_query($query);
			$buildUrl = $parseUrl['scheme'].'://'.$parseUrl['host'].$parseUrl['path'].$query;	

		} else { // path_info 传参访问

			$buildUrl = \Prfox\Http\Url::build($this->url['path_info'],[$this->page_code => $page]);
			isset($parseUrl['query']) && $buildUrl = $buildUrl .'?'. $parseUrl['query'];
		}
		return $buildUrl;
	}

	// 数字列表
	public function number($length)
    {
        $per = floor($length / 2);
        $min = $this->page_now - $per;

        if ($length % 2) {
            $max = $this->page_now + ceil($length / 2) - 1;
        } else {
            $max = $this->page_now + $per - 1;
        }

        if ($max > $this->page_total) {
            $min -= $max - $this->page_total;
        }

        if ($min < 1) {
            $max += 1 - $min;
        }

        $max > $this->page_total && $max = $this->page_total;
        $min < 1 && $min = 1;
        $link_page = '';

        foreach ( range($min, $max) as $page) {       	
        	if ($page == $this->page_now) {
        		$link_page .= '<span class="current">' . $page . '</span>'; 
        	} else {
        		$link_page .= '<a class="num" href="' . $this->url($page) . '">' . $page . '</a>';
        	}
        }
        return $link_page;

    }

	public function show($length = 5)
	{
		// 没有数据时不显示
		if(0 == $this->page_total) return '';
		
		//替换分页内容 
        $str = str_replace( 
            array(
            	'%TOTAL%', '%NOW_PAGE%', '%PREV%', '%NEXT%', '%START%', '%LINK_PAGE%', '%END%', '%COUNT%', '%TOTAL_ROW%', '%TOTAL%', '%NOW%'
            ), 
            array(
            	$this->config('total'), $this->page_now, $this->prev(), $this->next(), $this->start(), $this->number($length), $this->end(), $this->page_total, $this->data_total, $this->page_size, $this->page_now
            ), 
            $this->config('layout')
        ); 
		return $this->style() .'<div class="page">'.$str.'</div>';
	}

	public function style()
	{
		return 
		'
		<style>
		.b-page { 
		  background: #fff; 
		  box-shadow: 0px 1px 2px 0px #E2E2E2; 
		} 
		.page { 
		  width: 100%; 
		  padding: 30px 15px; 
		  background: #FFF; 
		  text-align: center; 
		  overflow: hidden; 
		} 
		.page .first, 
		.page .prev, 
		.page .current, 
		.page .num, 
		.page .current, 
		.page .next, 
		.page .end { 
		  padding: 8px 16px; 
		  margin: 0px 5px; 
		  display: inline-block; 
		  color: #008CBA; 
		  border: 1px solid #F2F2F2; 
		  border-radius: 5px; 
		} 
		.page .first:hover, 
		.page .prev:hover, 
		.page .current:hover, 
		.page .num:hover, 
		.page .current:hover, 
		.page .next:hover, 
		.page .end:hover { 
		  text-decoration: none; 
		  background: #F8F5F5; 
		} 
		.page .current { 
		  background-color: #008CBA; 
		  color: #FFF; 
		  border-radius: 5px; 
		  border: 1px solid #008CBA; 
		} 
		.page .current:hover { 
		  text-decoration: none; 
		  background: #008CBA; 
		} 
		.page .not-allowed { 
		  cursor: not-allowed; 
		  text-decoration: none; 
		  background: #F8F5F5;
		}
		</style>
		';
	}
}