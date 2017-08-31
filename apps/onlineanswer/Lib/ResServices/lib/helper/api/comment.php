<?php

/**
 * PHP SDK For iFLYTEK
 *
 * @version         $Id$
 * @author          IFLYTEK Education Division Architecture Team
 * @copyright       © 2013, iFlyTEK CO.Ltd. All rights reserved
 * @history
 *                  shenghe | 2013-04-12 19:26:11 | created
 */

require_once(dirname(dirname(__FILE__)) .'/base.php');

/**
 * 评论接口
 * @package         Helper\API
 */
class Comment extends Base {
    /**
     * 构造函数
     * @param string $accesstoken 校验用accesstoken
     * @param array $header       http协议头
     */
    public function __construct($appkey, $accesstoken, $header) {
        parent::__construct($appkey, $accesstoken, $header);
    }

    /**
      * 获取资源的评论
      *
      * 注意：
      * 返回的json对象中，total表示评论总数，便于客户端分页
      *
      * @param  string        $resourceid                   资源ID
      * @param  string        $condition                    过滤条件,只获取符合条件的评论.目前支持以下条件:
      *                                                     productid, uid, username
      * @param  bool          $is_get_reply                 是否在获取评论同时获取评论
      * @param  int           $skip                         跳过数据条数,用于分页查询
      * @param  int           $limit                        限制返回的评论条数,不传,获取所有
      * @param  string        $password                     操作资源的密码,一般不需要
      * @return     array|string                            错误码数组信息|Json字符串(由用户进行反序列化)
      */
    public function get_comment(
      $resourceid, $condition = array(), $is_get_reply = true, $skip =null, $limit = null, $password = null
    ) {
        if (!is_string($resourceid) || empty($resourceid) || !is_array($condition) || !is_bool($is_get_reply)) {
            return array('total' => 1, 'statuscode' => 400, 'data' => 'invalid parameters');
        }

        $_params = array('resourceid' => $resourceid, 'is_get_reply' => $is_get_reply ? 'true' : 'false');

        if (is_int($skip) && is_int($limit) && $skip >= 0 && $limit >= 0) {
            $_params['skip'] = $skip;
            $_params['limit'] = $limit;
        }

        if (!is_null($password)) {
            $_params['password'] = $password;
        }

        return $this->send('get', '/resourceservice/comments/', array_merge($condition, $_params));
    }

   /**
    * 评论某资源
    * @param  string $resourceid 资源ID
    * @param  string $comment    评论内容,不能为空
    * @param  string $uid        用户ID,可以不传
    * @param  string $username   用户名称,可以不传
    * @param  string $data       应用自定义的其他取值
    * @param  string $password   资源操作密码
    * @return array|string       错误码数组信息|Json字符串(由用户进行反序列化)
    */
    public function create_comment($resourceid, $comment, $uid = null, $username = null, $data = null, $password = null) {
        if (!is_string($resourceid)|| !is_string($comment)) {
            return array('total' => 1, 'statuscode' => 400, 'data' => 'invalid parameters');
        }

        $_params = array('resourceid' => $resourceid, 'comment' => $comment);

        if (!is_null($uid)) {
            $_params['uid'] = $uid;
        }

        if (!is_null($username)) {
            $_params['username'] = $username;
        }

        if (!is_null($data)) {
            $_params['data'] = $data;
        }

        if (!is_null($password)) {
            $_params['password'] = $password;
        }

        return $this->send('put', '/resourceservice/comments/', $_params);
    }

   /**
    * 删除评论
    *
    * @param  string        $commentid                    评论ID
    * @return     array|string                            错误码数组信息|Json字符串(由用户进行反序列化)
    */
    public function delete_comment($commentid) {
        if (!is_string($commentid) || empty($commentid)) {
            return array('total' => 1, 'statuscode' => 400, 'data' => 'invalid parameters');
        }

        return $this->send('del', '/resourceservice/comments/'. $commentid .'/');
    }

   /**
    * 修改评论
    *
    * @param  string        $commentid                    评论ID
    * @param  string        $comment                      评论内容
    * @return     array|string                            错误码数组信息|Json字符串(由用户进行反序列化)
    */
    public function update_comment($commentid, $comment) {
        if (!is_string($commentid) || empty($commentid) || !is_string($comment)) {
            return array('total' => 1, 'statuscode' => 400, 'data' => 'invalid parameters');
        }

        $_params = array('comment' => $comment);

        return $this->send('post', '/resourceservice/comments/'. $commentid .'/', $_params);
    }

    /**
     * 获取评论的回复
     *
     * @param  string        $commentid                    评论ID
     * @param  string        $condition                    过滤条件,只获取符合条件的评论.目前支持以下条件:
     *                                                     productid, uid, username
     * @param  int           $skip                         页码,用于分页查询
     * @param  int           $limit                        限制返回的评论条数,不传,获取所有
     * @param  string        $password                     操作资源的密码,一般不需要
     * @return     array|string                            错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function get_comment_reply(
      $commentid, $condition = array(), $skip =null, $limit = null, $password = null
    ) {
        if (!is_string($commentid) || empty($commentid) || !is_array($condition)) {
            return array('total' => 1, 'statuscode' => 400, 'data' => 'invalid parameters');
        }

        $_params = array('commentid' => $commentid);

        if (is_int($skip) && is_int($limit) && $skip >= 0 && $limit >= 0) {
            $_params['skip'] = $skip;
            $_params['limit'] = $limit;
        }

        if (!is_null($password)) {
            $_params['password'] = $password;
        }

        return $this->send('get', '/resourceservice/comments/children/', array_merge($condition, $_params));
    }

   /**
    * 对评论进行回复
    *
    * @param  string $commentid  资源ID
    * @param  string $comment    回复内容,不能为空
    * @param  string $uid        用户ID,可以不传
    * @param  string $username   用户名称,可以不传
    * @param  string $password   资源操作密码,如果
    * @return array|string       错误码数组信息|Json字符串(由用户进行反序列化)
    */
    public function create_comment_reply($commentid, $comment, $uid = null, $username = null, $password = null) {
        if (!is_string($commentid) || empty($commentid)|| !is_string($comment)) {
            return array('total' => 1, 'statuscode' => 400, 'data' => 'invalid parameters');
        }

        $_params = array('commentid' => $commentid, 'comment' => $comment);

        if (!is_null($uid)) {
            $_params['uid'] = $uid;
        }

        if (!is_null($username)) {
            $_params['username'] = $username;
        }

        if (!is_null($password)) {
            $_params['password'] = $password;
        }

        return $this->send('put', '/resourceservice/comments/children/', $_params);
    }

   /**
    * 删除回复
    *
    * @param  string        $replyid                      回复ID
    * @return     array|string                            错误码数组信息|Json字符串(由用户进行反序列化)
    */
    public function del_comment_reply($replyid) {
        if (!is_string($replyid) || empty($replyid)) {
            return array('total' => 1, 'statuscode' => 400, 'data' => 'invalid parameters');
        }

        $_params = array('replyid' => $replyid);

        return $this->send('del', '/resourceservice/comments/children/'. $replyid .'/', $_params);
    }
}