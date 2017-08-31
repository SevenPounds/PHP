<?php
/**
 * 带Ajax分页
 * 分页类
 */
class AjaxPage {
	public $first_row; // 起始行数
	
	public $list_rows; // 列表每页显示行数
	
	protected $total_pages; // 总页数
	
	protected $total_rows; // 总行数
	
	protected $now_page; // 当前页数
	
	protected $method = 'defalut'; // 处理情况 Ajax分页 Html分页(静态化时) 普通get方式
	
	protected $parameter = '';
	
	protected $page_name; // 分页参数的名称
	
	protected $ajax_func_name;
	
	public $plus = 3; // 分页偏移量
	
	protected $url;
	
	public $config = array('header' => '条记录', 'prev' => '上一页', 'next' => '下一页', 'first' => '第一页', 'last' => '最后一页');
	
	/**
	 * 构造函数
	 * 
	 * @param unknown_type $data        	
	 */
	public function __construct($data = array()) {
		$this->total_rows = $data ['total_rows'];
		
		$this->parameter = ! empty ( $data ['parameter'] ) ? $data ['parameter'] : '';
		$this->list_rows = ! empty ( $data ['list_rows'] ) && $data ['list_rows'] <= 100 ? $data ['list_rows'] : 15;
		$this->total_pages = ceil ( $this->total_rows / $this->list_rows );
		$this->page_name = ! empty ( $data ['page_name'] ) ? $data ['page_name'] : 'p';
		$this->ajax_func_name = ! empty ( $data ['ajax_func_name'] ) ? $data ['ajax_func_name'] : '';
		
		$this->method = ! empty ( $data ['method'] ) ? $data ['method'] : '';
		
		/* 当前页面 */
		if (! empty ( $data ['now_page'] )) {
			$this->now_page = intval ( $data ['now_page'] );
		} else {
			$this->now_page = ! empty ( $_GET [$this->page_name] ) ? intval ( $_GET [$this->page_name] ) : 1;
		}
		$this->now_page = $this->now_page <= 0 ? 1 : $this->now_page;
		
		if (! empty ( $this->total_pages ) && $this->now_page > $this->total_pages) {
			$this->now_page = $this->total_pages;
		}
		$this->first_row = $this->list_rows * ($this->now_page - 1);
	}
	
	/**
	 * 得到当前连接
	 * 
	 * @param
	 *        	$page
	 * @param
	 *        	$text
	 * @return string
	 */
	protected function _get_link($page, $text) {
		switch ($this->method) {
			case 'ajax' :
				$parameter = '';
				if ($this->parameter) {
					$parameter = ',' . $this->parameter;
				}
				return '<a onclick="' . $this->ajax_func_name . '(\'' . $page . '\'' . $parameter . ')" href="javascript:void(0)">' . $text . '</a>' . "\n";
				break;
			
			case 'html' :
				$url = str_replace ( '?', $page, $this->parameter );
				return '<a href="' . $url . '">' . $text . '</a>' . "\n";
				break;
			
			default :
				return '<a href="' . $this->_get_url ( $page ) . '">' . $text . '</a>' . "\n";
				break;
		}
	}
	
	/**
	 * 得到工作室汇聚页当前连接
	 *
	 * @param
	 *        	$page
	 * @param
	 *        	$text
	 * @return string
	 */
	protected function _get_studio_link($type, $page, $text) {
		switch ($type) {
			case 'first_page' :
				if ($this->now_page > 5) {
					$page=1;
					$text="第一页";
				}
				$parameter = '';
				if ($this->parameter) {
					$parameter = ',' . $this->parameter;
				}
				return '<li class="normal3" onclick="'.$this->ajax_func_name . '(\'' . $page . '\'' . $parameter . ')" href="javascript:void(0)"  style="cursor:pointer">'.'<a>' . $text . '</a>' . '</li>'."\n";
				break;		
			case 'up_page' :
				if ($this->now_page != 1) {
					$page=$this->now_page - 1;
					$text="上一页";
				}
				$parameter = '';
				if ($this->parameter) {
					$parameter = ',' . $this->parameter;
				}
				return '<li class="normal" onclick="'.$this->ajax_func_name . '(\'' . $page . '\'' . $parameter . ')" href="javascript:void(0)"  style="cursor:pointer">'.'<a>' . $text . '</a>' . '</li>'."\n";
				break;
			case 'down_page' :
				if ($this->now_page < $this->total_pages) {
					$page=$this->now_page + 1;
					$text="下一页";
				}
				$parameter = '';
				if ($this->parameter) {
					$parameter = ',' . $this->parameter;
				}
				return '<li class="normal" onclick="'.$this->ajax_func_name . '(\'' . $page . '\'' . $parameter . ')" href="javascript:void(0)"  style="cursor:pointer">'.'<a>' . $text . '</a>' . '</li>'."\n";
				break;
			case 'last_page' :
				if ($this->now_page < $this->total_pages - 5) {
					$page=$this->total_pages;
					$text="最后一页";
				}
				$parameter = '';
				if ($this->parameter) {
					$parameter = ',' . $this->parameter;
				}
				return '<li class="normal3" onclick="'.$this->ajax_func_name . '(\'' . $page . '\'' . $parameter . ')" href="javascript:void(0)"  style="cursor:pointer">'.'<a>' . $text . '</a>' . '</li>'."\n";
				break;
			default:
				$parameter = '';
				if ($this->parameter) {
					$parameter = ',' . $this->parameter;
				}
				return '<li class="normal2" onclick="'.$this->ajax_func_name . '(\'' . $page . '\'' . $parameter . ')" href="javascript:void(0)"  style="cursor:pointer">'.'<a>' . $text . '</a>' . '</li>'."\n";
				break;
				
		}
	}
	
	/**
	 * 设置当前页面链接
	 */
	protected function _set_url() {
		$url = $_SERVER ['REQUEST_URI'] . (strpos ( $_SERVER ['REQUEST_URI'], '?' ) ? '' : "?") . $this->parameter;
		$parse = parse_url ( $url );
		if (isset ( $parse ['query'] )) {
			parse_str ( $parse ['query'], $params );
			unset ( $params [$this->page_name] );
			$url = $parse ['path'] . '?' . http_build_query ( $params );
		}
		if (! empty ( $params )) {
			$url .= '&';
		}
		$this->url = $url;
	}
	
	/**
	 * 得到$page的url
	 * 
	 * @param $page 页面        	
	 * @return string
	 */
	protected function _get_url($page) {
		if ($this->url === NULL) {
			$this->_set_url ();
		}
		// $lable = strpos('&', $this->url) === FALSE ? '' : '&';
		return $this->url . $this->page_name . '=' . $page;
	}
	
	/**
	 * 得到第一页
	 * 
	 * @return string
	 */
	public function first_page($name = '第一页') {
		if ($this->now_page > 5) {
			return $this->_get_link ( '1', $name);
		}
		return '';
	}
	
	/**
	 * 最后一页
	 * 
	 * @param
	 *        	$name
	 * @return string
	 */
	public function last_page($name = '最后一页') {
		if ($this->now_page < $this->total_pages - 5) {
			return $this->_get_link ( $this->total_pages, $name);
		}
		return '';
	}
	
	/**
	 * 上一页
	 * 
	 * @return string
	 */
	public function up_page($name = '上一页') {
		if ($this->now_page != 1) {
			return $this->_get_link ( $this->now_page - 1, $name);
		}
		return '';
	}
	
	/**
	 * 下一页
	 * 
	 * @return string
	 */
	public function down_page($name = '下一页') {
		if ($this->now_page < $this->total_pages) {
			return $this->_get_link ( $this->now_page + 1, $name);
		}
		return '';
	}

	public function show() {
		if ($this->total_rows < 1 ||$this->total_pages<=1) {
			return '';
		}
		$plus = $this->plus;
		if ($plus + $this->now_page > $this->total_pages) {
			$begin = $this->total_pages - $plus * 2;
		} else {
			$begin = $this->now_page - $plus;
		}
		
		$begin = ($begin >= 1) ? $begin : 1;
		$return = '';
		$return .= $this->first_page ();
		$return .= $this->up_page ();
		for($i = $begin; $i <= $begin + $plus * 2; $i ++) {
			if ($i > $this->total_pages) {
				break;
			}
			if ($i == $this->now_page) {
				$return .= "<a class='current'>$i</a>\n";
			} else {
				$return .= $this->_get_link ( $i, $i ) . "\n";
			}
		}
		$return .= $this->down_page ();
		$return .= $this->last_page ();
		return $return;
	}

		

	/**
	 * showStudio 
	 * 
	 * 生成新版教师工作室汇聚页使用翻页链接
	 */
	public function showStudioPager() {
		if ($this->total_rows < 1 || $this->total_pages <= 1) {
			return '';
		}

		$plus = $this->plus;
		if ($plus + $this->now_page > $this->total_pages) {
			$begin = $this->total_pages - $plus * 2;
		} else {
			$begin = $this->now_page - $plus;
		}
		
		$begin = ($begin >= 1) ? $begin : 1;

		$return = '<div class="online_page2"><ul>';

		if ($this->now_page > 5) {
			$return .= $this->_get_studio_link('first_page');
		}

		if ($this->now_page != 1) {
			$return .= $this->_get_studio_link('up_page');
		}

		for ($i = $begin; $i <= $begin + $plus * 2; $i++) {
			if ($i > $this->total_pages) {
				break;
			}

			if ($i == $this->now_page) {
				$return .= '<li class="present2">' . $i . '</li>';
			} else {
				$return .= $this->_get_studio_link('', $i, $i);
			}
		}

		if ($this->now_page < $this->total_pages) {
			$return .= $this->_get_studio_link('down_page');
		}

		if ($this->now_page < $this->total_pages - 5) {
			$return .= $this->_get_studio_link('last_page');
		}
		$return .= '</ul></div>';

		return $return;
	}

	/**
	 * showApp
	 * 
	 * 生成新版教研应用列表页面使用翻页链接
	 */
	public function showAppPager() {
		if ($this->total_rows < 1 || $this->total_pages <= 1) {
			return '';
		}

		$plus = $this->plus;
		if ($plus + $this->now_page > $this->total_pages) {
			$begin = $this->total_pages - $plus * 2;
		} else {
			$begin = $this->now_page - $plus;
		}
		
		$begin = ($begin >= 1) ? $begin : 1;

		$return = '<div class="online_page"><ul>';

		if ($this->now_page > 5) {
			$return .=  $this->_get_studio_link('first_page');
		}

		if ($this->now_page != 1) {
			$return .=  $this->_get_studio_link('up_page');
		}
        $allLenghtb = $begin + $plus * 2;
		for ($i = $begin; $i <= $allLenghtb; $i++) {
			if ($i > $this->total_pages) {
				break;
			}

			if ($i == $this->now_page) {
				$return .= '<li class="present2">' . $i . '</li>';
			} else {
				$return .= $this->_get_studio_link('', $i, $i);
			}
		}

		if ($this->now_page < $this->total_pages) {
			$return .= $this->_get_studio_link('down_page');
		}

		if ($this->now_page < $this->total_pages - 5) {
			$return .= $this->_get_studio_link('last_page');
		}
		$return .= '</ul></div>';

		return $return;
	}

	/**
	 * 简单分页，只实现"上一页下一页"
	 * @author yxxing
	 */
	public function simpleShow() {
		if ($this->total_rows < 1 ||$this->total_pages<=1) {
			return '';
		}
		$plus = $this->plus;
		if ($plus + $this->now_page > $this->total_pages) {
			$begin = $this->total_pages - $plus * 2;
		} else {
			$begin = $this->now_page - $plus;
		}
	
		$begin = ($begin >= 1) ? $begin : 1;
		$return = '';
		$return .= $this->up_page ("<");
		$return .= $this->down_page (">");
		return $return;
	}
	
	public function setConfig($name,$value) {
		if(isset($this->config[$name])) {
			$this->config[$name]    =   $value;
		}
	}
}
?>
