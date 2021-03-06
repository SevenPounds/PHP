<?php
/**
 * VoteAddWidget
 * 添加投票Widget，可以放在任意位置
 * @uses Widget
 * @package
 * @version $id$
 * @copyright 2001-2013 SamPeng
 * @author SamPeng <penglingjun@zhishisoft.com>
 * @license PHP Version 5.2 {@link www.sampeng.org}
 */
class VoteAddWidget extends Widget
{
	static $rand = 0;
	public function render($data)
	{
		self::$rand ++;
		$data['rand'] = self::$rand;
		$data['time'] = getdate();
		$data['count'] = 2;
		
		// 当前登录用户相关的名师工作室
		$loginName = $this->user['login'];
		$MSGroups = D("MSGroup","msgroup")->getMsGroupByLoginName($loginName);
		$data['MSGroups'] = $MSGroups;
		
		$tpl = $data['tpl']?$data['tpl']:'VoteAdd';
		$data['exit'] = isset($data['exit'])?$data['exit']:true;
		$content = $this->renderFile(dirname(__FILE__) . '/'.$tpl.'.html',$data);
		return $content;
	}
}
