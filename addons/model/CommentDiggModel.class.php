<?php
/**
 * 评论赞模型
 * @author ylzhao
 */
class CommentDiggModel extends Model {
	var $tableName = 'comment_digg';
	protected $fields = array (
			0 => 'id',
			1 => 'uid',
			2 => 'comment_id',
			3 => 'ctime',
			'_pk' => 'id'
	);	
	
	public function addDigg($comment_id, $mid) {
		$data ['comment_id'] = $comment_id;
		$data ['uid'] = $mid;
		$data['uid'] = !$data['uid'] ? $GLOBALS['ts']['mid'] : $data['uid'];
		if ( !$data['uid'] ){
			$this->error = '未登录不能赞';
			return false;
		}
		$isExit = $this->where ( $data )->getField ( 'id' );
		if ($isExit) {
			$this->error = '你已经赞过';
			return false;
		}
		$data ['ctime'] = time ();
		$res = $this->add ( $data );
		if($res){
			$comment = model ( 'Source' )->getSourceInfo ( 'comment', $comment_id );
			$result = $this->execute("UPDATE `ts_comment` SET digg_count=digg_count+1  WHERE comment_id=".$comment_id);
			model('comment')->cleanCache($comment_id);
				
			// 增加通知  自己赞自己不发消息
			if($comment['comment_user_info']['uid'] != $mid){
				$author = model ( 'User' )->getUserInfo ( $mid );
				$config['user'] = '<a href="'.$author ['space_url'].'" >'.$author ['uname'].'</a>';
				$config ['content'] = t($comment ['source_content']);
				$config ['content'] = str_replace('◆','',$config ['content']);
				$config ['content'] = mStr($config ['content'], 34);
				$config ['sourceurl'] = $comment['source_url'];
				model ( 'Notify' )->sendNotify ( $comment['comment_user_info']['uid'], 'blog_comment_digg', $config );
			}
		}
		return $res;
	}

	/**
	 * 删除赞
	 * @param int $comment_id
	 * @param int $mid
	 * @return boolean
	 */
	public function delDigg ($comment_id, $mid) {
		$data['comment_id'] = $comment_id;
		$data['uid'] = $mid;
		$data['uid'] = !$data['uid'] ? $GLOBALS['ts']['mid'] : $data['uid'];
		if ( !$data['uid'] ){
			$this->error = '未登录不能取消赞';
			return false;
		}
		$isExit = $this->where($data)->getField('id');
		if (!$isExit) {
			$this->error = '取消赞失败，您可以已取消过赞信息';
			return false;
		}

		$res = $this->where($data)->delete();

		if ($res) {
			$comment = model('Source')->getSourceInfo('comment', $comment_id);
			//$result = model('Feed')->where('comment_id='.$comment_id)->setDec('digg_count');
            $result = $this->execute("UPDATE `ts_comment` SET digg_count=digg_count-1  WHERE comment_id=".$comment_id);
			model('Feed')->cleanCache($comment_id);
            model('comment')->cleanCache($comment_id);
		}

		return $res;
	}

	public function checkIsDigg($comment_ids, $uid) {
		if (! is_array ( $comment_ids ))
			$comment_ids = array (
					$comment_ids 
			);
		
		$comment_ids = array_filter($comment_ids);
		$map ['comment_id'] = array (
				'in',
				$comment_ids 
		);
		$map ['uid'] = $uid;
		$list = $this->where ( $map )->field ( 'comment_id' )->findAll ();
		foreach ( $list as $v ) {
			$res [$v ['comment_id']] = 1;
		}
		
		return $res;
	}

	public function getLastError () {
		return $this->error;
	}
}